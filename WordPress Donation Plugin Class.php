<?php

namespace SMWC_Donations;

class SMWC_Donations_Plugin {
    
    // Single Instance
    private static $instance = null;
    
    // Database instance
    private $db;
    
    // CiviCRM instance
    private $civicrm;

    // Stripe Key
    private $stripe_key;

    // Get Instance
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Constructor
    private function __construct() {
        $this->init();
    }
    
    // Initialize Plugin
    private function init() {

        // Create instances
        $this->db = new Database();
        $this->civicrm = new CiviCRM($this->db);

        // Load Stripe
        include_once(plugin_dir_path(__FILE__) . '../stripe/init.php');
        
        // Initialize Stripe
        $this->init_stripe();
        
        // Initialize database
        $this->init_database();
        
        // Setup hooks
        $this->setup_hooks();

    }
    
    // Initialize Stripe
    private function init_stripe() {
        if (defined('STRIPE_SECRET_KEY')) {
            $this->stripe_key = STRIPE_SECRET_KEY;
            \Stripe\Stripe::setApiKey($this->stripe_key);
        } else {
            error_log('SMWC Donations: Stripe API key not configured');
            $redirect_url = add_query_arg('failed', '1', $this->get_redirect_base() );
            wp_redirect($redirect_url);
            exit;
        }
    }
    
    // Initialize Database
    private function init_database() {
        $this->db = $this->db->initialize(['civicrm']);
    }
    
    // Setup WordPress Hooks
    private function setup_hooks() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_filter('template_include', [$this, 'load_donate_template']);
        add_filter('theme_page_templates', [$this, 'add_template_to_dropdown']);
        add_action('admin_post_nopriv_smwc_make_payment', [$this, 'process_payment']);
        add_action('admin_post_smwc_make_payment', [$this, 'process_payment']);
        add_action('admin_post_nopriv_smwc_sign_agreement', [$this, 'process_agreement']);
        add_action('admin_post_smwc_sign_agreement', [$this, 'process_agreement']);
        add_action('admin_post_nopriv_smwc_check_status', [$this, 'check_membership_status']);
        add_action('admin_post_smwc_check_status', [$this, 'check_membership_status']);
    }
    
    // Enqueue Scripts and Styles
    public function enqueue_scripts() {
        wp_enqueue_script(
            'smwc_donations_js', 
            plugin_dir_url(__FILE__) . '../scripts/donations.js', 
            ['jquery'], 
            '1.12'
        );
        wp_enqueue_style(
            'smwc_donations_css', 
            plugin_dir_url(__FILE__) . '../scripts/donations.css', 
            [], 
            '1.12'
        );
    }
    
    // Load Donate Template
    public function load_donate_template($template) {
        if (get_page_template_slug() === 'template.php') {
            $template = plugin_dir_path(__FILE__) . '../template.php';
        }
        return $template;
    }
    
    // Add template to WordPress dropdown
    public function add_template_to_dropdown($templates) {
        $templates['template.php'] = 'Donations Page';
        return $templates;
    }
    
    // Process Payment
    public function process_payment() {
        
        // Verify nonce
        if (!isset($_REQUEST['smwc_donations_nonce']) || !wp_verify_nonce($_REQUEST['smwc_donations_nonce'], 'smwc_donations_payment')) {
            wp_die('Security check failed: Invalid nonce');
        }
        
        // Get the referring page URL for redirect
        $redirect_base = $this->get_redirect_base();
        
        try {

            // Process the payment
            $result = $this->handle_payment_processing();
            
            if ($result['success']) {

                $this->send_confirmation_email($result['amount']);

                // Redirect back to the form page with success message
                $redirect_url = add_query_arg('complete', '1', $redirect_base);
                wp_redirect($redirect_url);
                exit;
            } else {
                $redirect_url = add_query_arg('failed', '1', $redirect_base);
                wp_redirect($redirect_url);
                exit;
            }
            
        } catch (Exception $e) {
            error_log('SMWC Donations payment error: ' . $e->getMessage());
            $redirect_url = add_query_arg('failed', '1', $redirect_base);
            wp_redirect($redirect_url);
            exit;
        }
    }
    
    // Get Redirect Base URL
    public function get_redirect_base($fallback_url = null) {
        $referer = wp_get_referer();
        $redirect_base = $referer ? $referer : ($fallback_url ? $fallback_url : home_url());
        return $redirect_base;
    }
    
    // Redirect with success or fail message
    public function redirect_with_status($status, $fallback_url = null, $status_value = '1') {
        $redirect_base = $this->get_redirect_base($fallback_url);
        $redirect_url = add_query_arg($status, $status_value, $redirect_base);
        wp_redirect($redirect_url);
        exit;
    }
    
    // Handle Payment Processing
    private function handle_payment_processing() {

        // Get and validate amount
        $amount = $this->get_validated_amount();

        // Process one-time payment
        if ($this->get_sanitized_request('interval') == 'once') {
            $charge = $this->process_one_time_payment($amount);

        // Process recurring payment
        } else {
            $charge = $this->process_recurring_payment($amount);
        }
        
        if ($charge->status == 'succeeded' || $charge->status == 'active') {

            // Add or update contact in CiviCRM
            $contact = $this->create_or_update_contact($charge, $amount);
            
            // Add contribution to CiviCRM
            $this->add_contribution_to_civi($contact);
            
            return ['success' => true, 'amount' => $amount];

        }
        
        return ['success' => false];

    }
    
    // Get Validated Amount
    private function get_validated_amount() {

        $amount = $this->get_sanitized_request('amount');
        
        // Remove currency symbols and commas
        $amount = floatval(str_replace(['$', ','], '', $amount));
        
        if ($amount <= 0) {
            throw new Exception('Invalid donation amount');
        }
        
        return $amount;
    }
    
    // Process One-Time Payment
    private function process_one_time_payment($amount) {

        $description = $this->get_payment_description();
        $token = $this->get_sanitized_request('token');
        
        if (empty($token)) {
            throw new Exception('Payment token is required');
        }
        
        try {
            return \Stripe\Charge::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'description' => $description,
                'source' => $token,
            ]);
            
        } catch (\Stripe\Exception\StripeException $e) {
            error_log('Payment processing error: ' . $e->getMessage());
            throw new Exception('Payment processing error');
        }

    }
    
    // Process Recurring Payment
    private function process_recurring_payment($amount) {

        $description = $this->get_payment_description();
        $token = $this->get_sanitized_request('token');
        $name = $this->get_sanitized_request('name');
        $email = $this->get_sanitized_request('email');
        $interval = $this->get_sanitized_request('interval');
        
        if (empty($token) || empty($name) || empty($email)) {
            throw new Exception('Required payment information is missing');
        }
        
        try {
            // Create customer
            $customer = \Stripe\Customer::create([
                'name' => $name,
                'email' => $email,
                'source' => $token
            ]);
            

            
            // Create product
            $product = \Stripe\Product::create([
                'name' => $description,
            ]);
            
            // Create price
            $price = \Stripe\Price::create([
                'product' => $product->id,
                'nickname' => $description,
                'unit_amount' => $amount * 100,
                'currency' => 'usd',
                'recurring' => ['interval' => $interval],
            ]);
            
            // Create subscription
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [['price' => $price->id]],
            ]);
            

            
            return $subscription;
            
        } catch (\Stripe\Exception\StripeException $e) {
            error_log('Recurring payment error: ' . $e->getMessage());
            throw new Exception('Payment processing error');
        }

    }
    
    // Get Payment Description
    private function get_payment_description() {
        $type = $this->get_sanitized_request('type');
        return $type == 'donation' ? 'Donation' : 'Membership Dues';
    }
    
    // Create or Update Contact
    private function create_or_update_contact($charge, $amount) {
        return $this->prepare_contact_data($charge, $amount);
    }
    
    // Prepare Contact Data
    private function prepare_contact_data($charge, $amount) {

        $name = $this->get_sanitized_request('name');
        $contact = [
            'last_name' => $this->extract_last_name($name),
            'first_name' => $this->extract_first_name($name),
            'email' => $this->get_sanitized_request('email'),
            'phone' => $this->get_sanitized_request('phone'),
            'get-involved' => $this->get_sanitized_request('get-involved'),
            'interval' => $this->get_sanitized_request('interval'),
            'type' => $this->get_sanitized_request('type'),
            'charge_id' => $charge->id,
            'amount' => $amount,
            'app_amount' => $amount,
            'app_fee' => $charge->application_fee ?? 0,
            'campaign' => $this->get_sanitized_request('campaign'),
            'memory' => $this->get_sanitized_request('memory'),
            'orientation' => $this->get_sanitized_request('orientation'),
            'notes' => $this->get_sanitized_request('notes'),
        ];
        
        return $contact;

    }
    
    // Extract Last Name
    private function extract_last_name($name) {
        if (strpos($name, ' ') === false)
            return '';
        return preg_replace('#.*\s([\w-]*)$#', '$1', $name);
    }
    
    // Extract First Name
    private function extract_first_name($name) {
        $last_name = $this->extract_last_name($name);
        return trim(preg_replace('#' . preg_quote($last_name, '#') . '#', '', $name));
    }
    
    // Add Contribution To Civi
    private function add_contribution_to_civi($contact) {
        $this->process_civi_contribution($contact);
    }
    
    // Process CiviCRM Contribution
    private function process_civi_contribution($contact) {
        error_log('SMWC Donations: Processing CiviCRM contribution for ' . $contact['email']);
    }
    
    // Format Contact Name
    private function format_contact_name($contact) {
        $name = $contact['first_name'] ?? '';
        if (!empty($contact['last_name']))
            $name .= ' ' . $contact['last_name'];
        return trim($name);
    }
    
    // Send Confirmation Email
    private function send_confirmation_email($amount) {
        $email_to = get_bloginfo('admin_email');
        $body = $this->build_email_body($amount);
        $subject = $this->build_email_subject($amount);
        
        // Set proper headers for HTML emails
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>'
        );
        
        // Log email attempt
        error_log('SMWC Donations: Attempting to send email to ' . $email_to);
        error_log('SMWC Donations: Email subject: ' . $subject);
        error_log('SMWC Donations: Email body length: ' . strlen($body));
        
        // Send main email and log result
        $sent = wp_mail($email_to, $subject, $body, $headers);
        if (!$sent) {
            error_log('SMWC Donations: Failed to send confirmation email to ' . $email_to);
        } else {
            error_log('SMWC Donations: Confirmation email sent successfully to ' . $email_to);
        }
        
        $this->send_additional_emails($email_to, $body, $headers);
    }
    
    // Build Email Body
    private function build_email_body($amount) {

        $body = 'Name: ' . $this->get_sanitized_request('name') . '<br>';
        $body .= 'Email: ' . $this->get_sanitized_request('email') . '<br>';
        $body .= 'Phone: ' . $this->get_sanitized_request('phone') . '<br>';
        
        $notes = $this->get_sanitized_request('notes');
        if ($notes) {
            $body .= 'Notes: ' . $notes . '<br><br>';
        }
        
        $body .= 'Amount: $' . $amount;
        
        if ($this->get_sanitized_request('get-involved')) {
            $body .= '<br><br>Interested in getting involved.';
        }
        
        if ($this->get_sanitized_request('orientation')) {
            $body .= '<br>Interested in a new member orientation.';
        }
        
        return $body;
    }
    
    // Build Email Subject
    private function build_email_subject($amount) {
        $type = $this->get_sanitized_request('type');
        $name = $this->get_sanitized_request('name');
        
        if ($type == 'membership') {
            return $name . ' Paid Their Member Dues';
        }
        return $name . ' Made A Donation of $' . $amount;
    }
    
    // Send Additional Emails
    private function send_additional_emails($email_to, $body, $headers = array()) {
        $name = $this->get_sanitized_request('name');
        
        if ($this->get_sanitized_request('get-involved')) {
            $subject = $name . ' is interested in getting involved';
            $sent = wp_mail($email_to, $subject, $body, $headers);
            if (!$sent) {
                error_log('SMWC Donations: Failed to send get-involved email');
            }
        }
        
        if ($this->get_sanitized_request('orientation')) {
            $subject = $name . ' is interested in a new member orientation';
            $sent = wp_mail($email_to, $subject, $body, $headers);
            if (!$sent) {
                error_log('SMWC Donations: Failed to send orientation email');
            }
        }
    }
    
    // Get sanitized request data
    private function get_sanitized_request($key, $default = '') {
        if (!isset($_REQUEST[$key])) {
            return $default;
        }
        
        $value = $_REQUEST[$key];
        
        // Sanitize based on expected data type
        switch ($key) {
            case 'email':
                return sanitize_email($value);
            case 'amount':
            case 'phone':
                return sanitize_text_field($value);
            case 'name':
            case 'notes':
            case 'campaign':
            case 'memory':
                return sanitize_text_field($value);
            case 'type':
            case 'interval':
                return sanitize_text_field($value);
            case 'get-involved':
            case 'orientation':
                return !empty($value);
            case 'token':
                return sanitize_text_field($value);
            default:
                return sanitize_text_field($value);
        }
    }
    
    // Process Member Agreement
    public function process_agreement() {
        $this->civicrm->process_agreement();
    }
    
    // Check Membership Status
    public function check_membership_status() {
        $this->civicrm->check_membership_status();
    }
    

}