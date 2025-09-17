<?php

namespace App\Payment;

use Mail;
use App\Mail\TransactionEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Payment {

	// Platform Fee
	private $fee = .01;

	// Stripes API URLs
	private $url = 'https://api.stripe.com/v1/';
	private $auth = 'https://dashboard.stripe.com/oauth/authorize?response_type=code&scope=read_write&client_id=';

	private function secret_key() {
		return config('services.stripe.secret_key');
	}

	public function public_key() {
		return config('services.stripe.public_key');
	}

	public function connect_url() {
		return $this->auth . config('services.stripe.connect_account');
	}

	public function unsubscribe() {

		$user = Auth::user();
		$account = config('services.stripe.account_id');

		if ( isset($user->stripe_sub_id) ) {
			$method = 'subscriptions/' . $user->stripe_sub_id;
			$subscription = $this->request( $account, $method, array(), 'DELETE' );
			$user->sub_status = 0;
			$user->save();
		}

		return isset($subscription) ? $subscription : false;

	}

	public function subscribe() {

		$user = Auth::user();
		$account = config('services.stripe.account_id');
		$payment = (object) array();


		// Create Plan
		$plan = $this->request( $account, 'plans', array(
			"amount" => 7900,
			"interval" => 'month',
			"id" => date( 'YmdHis',strtotime('now')),
			"name" => $user->name,
			"statement_descriptor" => 'Subscription Payment',
			"currency" => "usd"
		));

		// Create Customer
		if ( isset( $plan->id ) ) {
			$customer = $this->request( $account, 'customers', array(
				"name" => $user->name,
				"email" => $user->email,
				"phone" => $user->phone,
				"source" => request('token'),
			));
		}

		// Subscribe Customer To Plan
		if ( isset( $customer->id ) ) {
			$subscription = $this->request( $account, 'subscriptions', array(
					"customer" => $customer->id,
					"plan" => $plan->id,
				)
			);
		}


		// Set Charge Data
		if ( isset($subscription->id) ) {
			$payment->complete = $payment->charge = true;
			$payment->subscription = $subscription->id;
			$user->sub_status = 1;
			$user->stripe_sub_id = $subscription->id;
			$user->save();

		// Or Set Error
		} else {
			$payment->complete = false;
			$payment->charge = !isset($plan->id) ? $plan :
				( !isset($customer->id) ? $customer :
				( !isset($subscription->id) ? $subscription : false ));
		}

		return $payment;

	}

	public function new() {

		$payment = (object) [
			'data' => (object) request()->input('form'),
			'page' => \App\Page::find( request()->input('page.id') ),
			'user' => \App\User::find( request()->input('page.user_id') ),
			'charge' => (object) array(),
			'team' => request('team'),
			'individual' => request('individual'),
			'token' => request('token'),
			'valid' => true,
			'error' => false,
			'complete' => false,
		];

		if ( $payment->page->type == 'rsvp' ) {
			$payment = \App\Guest::new( $payment );

		} else if ( $payment->data->total == 0 ) {
			$payment = \App\Transaction::response( $payment );

		} else {

			if ( $payment->page->type == 'event' )
				$payment = \App\Ticket::validate( $payment );

			else if ( $payment->page->type == 'store' )
				$payment = \App\Product::validate( $payment );

			else if ( $payment->page->type == 'custom' )
				$payment = \App\Field::validate( $payment );

			// Get Users Stripe Account ID
			$account = $payment->user->stripe_id;

			if ( $payment->valid && $account ) {

				// Recurring Payment
				if ( boolval($payment->data->interval) ) {

					$name = preg_replace('/[^A-Za-z0-9\-]/', '', $payment->user->name );

					// Create Plan
					$plan = $this->request( $account, 'plans', array(
						"amount" => $payment->data->grand_total * 100,
						"interval" => $payment->data->interval,
						"id" => $payment->page->id . date( 'YmdHis',strtotime('now')),
						"name" => $payment->page->name,
						"statement_descriptor" => substr( $name, 0, 22 ),
						"currency" => "usd"
					));

					$customer_name = $payment->data->customer['first'] . ' ' . $payment->data->customer['last'];

					// Create Customer
					if ( isset( $plan->id ) ) {
						$customer = $this->request( $account, 'customers', array(
							"name" => $customer_name,
							"email" => $payment->data->customer['email'],
							"source" => $payment->token,
							"metadata" => $payment->data->customer,
						));
					}

					// Subscribe Customer To Plan
					if ( isset( $customer->id ) ) {
						$subscription = $this->request( $account, 'subscriptions', array(
								"customer" => $customer->id,
								"plan" => $plan->id,
								"application_fee_percent" => 1,
							)
						);
					}

					// Get Last Charge Detail
					if ( isset( $subscription->id ) ) {
						$charges = $this->request( $account, 'charges?', array(
								"customer" => $customer->id,
								"limit" => 1,
								"expand[]" => "data.balance_transaction",
							), 'GET'
						);
					}

					// Set Charge Data
					if ( isset($charges->data[0]->id) ) {
						$payment->charge = $charges->data[0];
						$payment->subscription = $subscription->id;

					// Or Set Error
					} else {
						$payment->charge = !isset($plan->id) ? $plan :
							( !isset($customer->id) ? $customer :
							( !isset($subscription->id) ? $subscription : $charges ));
					}

				}

				// Single Payment
				else {

					$name = preg_replace('/[^A-Za-z0-9\-]/', '', $payment->user->name );

					$payment->charge = $this->request( $account, 'charges', array(
						"source" => $payment->token,
						"amount" => $payment->data->grand_total * 100,
						"application_fee" => $payment->data->app_fee * 100,
						"description" => $payment->page->name,
						"statement_descriptor" => substr( $name, 0, 22 ),
						"currency" => "usd",
						"expand[]" => "balance_transaction",
					));

				}

				if ( isset($payment->charge->id) ) {

					$payment->complete = true;

					// Create or Update Customer
					$payment = \App\Customer::new($payment);

					// Create Transaction
					$payment = \App\Transaction::new($payment);

					// Set Deductible
					$payment = \App\Ticket::deductible($payment);

					if( $payment->page->type == 'fundraiser' || $payment->page->type == 'donation' )
						\App\Page::new_donation($payment);

					else if( $payment->page->type == 'event' )
						\App\Ticket::new_order($payment);

					else if( $payment->page->type == 'store' )
						\App\Product::new_order($payment);

					else if( $payment->page->type == 'custom' )
						\App\Field::new_order($payment);

					if ( isset($payment->user->details->alerts) )
						Mail::to($payment->user->email)
							->send( new TransactionEmail($payment) );


				}

				else {
					$payment->error = $payment->charge->error->message;
					report_exception( $payment->error );
				}

			} else {
				report_exception('Failed to validate ' . $payment->page->type . ' items' );
				if ( !$payment->error )
					$payment->error = 'There was a problem with your payment total';

			}

		}

		if ( isset($payment->user->integrations->mailchimp_key) )
			\App\Mailchimp::add($payment);

		if ( isset($payment->user->integrations->salesforce_auth) )
			\App\Salesforce::add($payment);

		return $payment;

	}

	public function refund( $transaction, $amount ) {

		$amount = str_replace( '$', '', str_replace( ',', '', $amount ));

		// Make Refund
		$refund = $this->request( Auth::user()->stripe_id, 'refunds', array(
			"charge" => $transaction->stripe_id,
			"amount" => floatval( $amount ) * 100,
			"refund_application_fee" => 'true',
			"expand[]" => "balance_transaction",
		));

		if ( !isset($refund->error) ) {

			$refund_transaction = $transaction->replicate();
			$refund_transaction->status = 'refund';
			$refund_transaction->gross = $refund->balance_transaction->amount * .01;
			$refund_transaction->net = $refund->balance_transaction->net * .01;
			$refund_transaction->fee = $refund->balance_transaction->fee *.01;
			$refund_transaction->save();

			$page = \App\Page::find($transaction->page_id);
			$page->decrement( 'raised', floatval($transaction->total) );

			if ( $transaction->fundraiser_id ) {
				\App\Fundraiser::where(['user_id' => $transaction->fundraiser_id,
					'page_id' => $transaction->page_id ])->first()->decrement(
						'raised', floatval($transaction->total) );
			}

			if ( $transaction->team_id ) {
				$team = $transaction->team;
				$team->update([ 'raised' => floatval($transaction->team->total) - $transaction->total ]);
			}

		}

		return $refund;

	}

	public function cancel( $transaction ) {
		$account = Auth::user()->stripe_id;
		$method = 'subscriptions/' . $transaction->stripe_sub_id;
		$subscription = $this->request( Auth::user()->stripe_id, $method, array(), 'DELETE' );
		$transactions = \App\Transaction::where( 'stripe_sub_id', $transaction->stripe_sub_id )->get();
		foreach ( $transactions as $trans )
			$trans->update(['sub_status' => 'cancelled']);
		$transaction->update([ 'sub_status' => 'cancelled' ]);
		return $subscription;
	}

	public function hook() {

		// Get Event/Hook Data
		$event = request('data');
		$type = request('type');

		// Subscriptions
		if ( $type == 'invoice.payment_succeeded' || $type == 'invoice.payment_failed' ) {
			$action = 'invoices/' . $event['object']['id'];
			$subscription_id = $event['object']['lines']['data'][0]['id'];
			$transaction = \App\Transaction::where( 'stripe_sub_id', $subscription_id )
				->orderBy('id', 'DESC')->first();
			$user = !isset($transaction->id) ? \App\User::where( 'stripe_sub_id', $subscription_id )->first() : false;

		// Single Charges
		} else if ( $type == 'charge.succeeded' || $type == 'charge.failed' ) {
			$action = 'charges/' . $event['object']['id'];
			$transaction = \App\Transaction::where( 'stripe_id', $event['object']['id'] )
				->orderBy('id', 'DESC')->first();
		}

		// If Transaction Exists
		if ( isset($action) && isset($transaction->id) ) {

			// Get Object From Stripe
			$stripe_account = $transaction->user->stripe_id;
			$object = $this::request($stripe_account,$action,false,'GET');

			// Update/Create Transactions
			$type == 'invoice.payment_succeeded' ?
				\App\Transaction::subscription( $object, $transaction, $type ):
				\App\Transaction::status( $object, $transaction, $type );

		} else if ( isset($action) && isset($user->id) ) {
			\App\Invoice::store( $user, $event, $type );
		}

		return response('Thanks!', 200);

	}

	public function request( $account, $method, $data, $action = 'POST' ) {

		$request = curl_init();
		curl_setopt($request, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer ' . $this->secret_key(), 'Stripe-Account: ' . $account ) );
		curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

		if ( $action == 'POST' ) {
			curl_setopt($request, CURLOPT_URL, $this->url . $method );
			curl_setopt($request, CURLOPT_POST, true);
			curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query( $data ));

		} else if ( $action == 'GET' && $data ) {
			curl_setopt($request, CURLOPT_URL, $this->url . $method . http_build_query( $data ) );

		} else if ( $action == 'GET' ) {
			curl_setopt($request, CURLOPT_URL, $this->url . $method );

		} else if ( $action == 'DELETE' ) {
			curl_setopt($request, CURLOPT_URL, $this->url . $method );
			curl_setopt($request, CURLOPT_CUSTOMREQUEST, "DELETE");
		}

		$response = curl_exec( $request );
		curl_close( $request );
		return json_decode( $response );

	}

	public function connect() {

		$user = Auth::id();

		if ( $user && isset($_GET['code']) ) {

			$data = array(
				"code" => $_GET['code'],
				"grant_type" => 'authorization_code',
			);

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $this->secret_key() ) );
			curl_setopt($ch, CURLOPT_URL, 'https://connect.stripe.com/oauth/token' );
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$output = curl_exec($ch);
			curl_close($ch);
			$response = json_decode($output);

		}

		if ( $user && !isset($response->error) ) {

			\App\User::find($user)->update( [
				'stripe_id' => $response->stripe_user_id,
				'stripe_status' => 'active',
			]);

			return true;

		} else {

			return false;

		}

	}

}
