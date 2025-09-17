<!DOCTYPE html>

<html <?php language_attributes(); ?>>

	<head>

		<meta charset="<?php bloginfo("charset"); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" >

		<link rel="apple-touch-icon" sizes="180x180" href="/wp-content/uploads/fbrfg/apple-touch-icon.png?v=7">
		<link rel="icon" type="image/png" sizes="32x32" href="/wp-content/uploads/fbrfg/favicon-32x32.png?v=7">
		<link rel="icon" type="image/png" sizes="16x16" href="/wp-content/uploads/fbrfg/favicon-16x16.png?v=7">
		<link rel="icon" href="/wp-content/uploads/fbrfg/favicon.ico?v=5">

		<?php wp_head(); ?>

	</head>

	<body <?php body_class(); ?>>

		<a href="#hyp-page" class="hyp-skip-link"><?= esc_html__('Skip to main content', 'hyp-theme-25' ); ?></a>

		<?php wp_body_open(); ?>

		<header class="hyp-header">
			<div class="hyp-wrap hyp-wrap-wide">

				<div class="hyp-logo">
					<a href="/" aria-label="<?= esc_attr__('Return to Homepage','hyp-theme-25'); ?>">
						<img src="<?= get_template_directory_uri() ?>/assets/hyp-access-logo.png" alt="Hyp+Access Logo" class="site-header" width="300px">
					</a>
				</div>

				<div class="hyp-nav">

					<nav aria-label="<?= esc_attr__('Sub Navigation Menu','hyp-theme-25'); ?>">
						<ul class="hyp-nav-top">

							<?php wp_nav_menu([ 'theme_location' => 'secondary', 'container' => false,
									'items_wrap' => '%3$s', 'echo' => true ]); ?>

							<?php $permalink = get_permalink(); ?>

							<?php if (is_user_logged_in()): ?>
								<li><a href="/dashboard" class="hyp-top-link login <?= strpos($permalink, 'dashboard') !== false ? 'current' : '' ?>"><?= esc_html__('Dashboard', 'hyp-theme-25' ); ?></a></li>
							<?php else: ?>
								<li><a href="/login" class="hyp-top-link login <?= strpos($permalink, 'login') !== false ? 'current' : '' ?>"><?= esc_html__('Login', 'hyp-theme-25' ); ?></a></li>
							<?php endif; ?>

							<li class="hyp-top-icon">
								<a href="https://www.instagram.com/hyp_access/" aria-label="<?= esc_attr__('Hyp+Access Instagram','hyp-theme-25'); ?>" target="_blank">
									<svg class="svg-icon" aria-hidden="true" role="img" focusable="false" width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd" d="M11.9962 0.0078125C8.73824 0.0078125 8.32971 0.021622 7.05019 0.080003C5.77333 0.138241 4.90129 0.341051 4.13824 0.637622C3.34938 0.944146 2.68038 1.35434 2.01343 2.02124C1.34652 2.68819 0.936333 3.35719 0.629809 4.14605C0.333238 4.9091 0.130429 5.78115 0.0721905 7.058C0.0138095 8.33753 0 8.74605 0 12.0041C0 15.262 0.0138095 15.6705 0.0721905 16.9501C0.130429 18.2269 0.333238 19.099 0.629809 19.862C0.936333 20.6509 1.34652 21.3199 2.01343 21.9868C2.68038 22.6537 3.34938 23.0639 4.13824 23.3705C4.90129 23.667 5.77333 23.8698 7.05019 23.9281C8.32971 23.9864 8.73824 24.0002 11.9962 24.0002C15.2542 24.0002 15.6627 23.9864 16.9422 23.9281C18.2191 23.8698 19.0911 23.667 19.8542 23.3705C20.643 23.0639 21.312 22.6537 21.979 21.9868C22.6459 21.3199 23.0561 20.6509 23.3627 19.862C23.6592 19.099 23.862 18.2269 23.9202 16.9501C23.9786 15.6705 23.9924 15.262 23.9924 12.0041C23.9924 8.74605 23.9786 8.33753 23.9202 7.058C23.862 5.78115 23.6592 4.9091 23.3627 4.14605C23.0561 3.35719 22.6459 2.68819 21.979 2.02124C21.312 1.35434 20.643 0.944146 19.8542 0.637622C19.0911 0.341051 18.2191 0.138241 16.9422 0.080003C15.6627 0.021622 15.2542 0.0078125 11.9962 0.0078125ZM7.99748 12.0041C7.99748 14.2125 9.78776 16.0028 11.9962 16.0028C14.2047 16.0028 15.995 14.2125 15.995 12.0041C15.995 9.79557 14.2047 8.00529 11.9962 8.00529C9.78776 8.00529 7.99748 9.79557 7.99748 12.0041ZM5.836 12.0041C5.836 8.60181 8.594 5.84381 11.9962 5.84381C15.3984 5.84381 18.1564 8.60181 18.1564 12.0041C18.1564 15.4062 15.3984 18.1642 11.9962 18.1642C8.594 18.1642 5.836 15.4062 5.836 12.0041ZM18.3998 7.03996C19.1949 7.03996 19.8394 6.39548 19.8394 5.60043C19.8394 4.80538 19.1949 4.16086 18.3998 4.16086C17.6048 4.16086 16.9603 4.80538 16.9603 5.60043C16.9603 6.39548 17.6048 7.03996 18.3998 7.03996Z"/>
									</svg>

								</a>
							</li>

							<li class="hyp-top-icon">
								<a href="https://www.tiktok.com/@hyp_access" aria-label="<?= esc_attr__('Hyp+Access TikTok','hyp-theme-25'); ?>" target="_blank">
									<svg class="svg-icon" aria-hidden="true" role="img" focusable="false" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
										<path d="M19.589 6.686a4.793 4.793 0 0 1-3.77-4.245V2h-3.445v13.672a2.896 2.896 0 0 1-5.201 1.743l-.002-.001.002.001a2.895 2.895 0 0 1 3.183-4.51v-3.5a6.329 6.329 0 0 0-5.394 10.692 6.33 6.33 0 0 0 10.857-4.424V8.687a8.182 8.182 0 0 0 4.773 1.526V6.79a4.831 4.831 0 0 1-1.003-.104z"/>
									</svg>
								</a>
							</li>

							<li class="hyp-top-icon"><a href="https://www.facebook.com/HypAccess" aria-label="<?= esc_attr__('Hyp+Access Facebook','hyp-theme-25'); ?>" target="_blank"><svg class="svg-icon" aria-hidden="true" role="img" focusable="false" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 2C6.5 2 2 6.5 2 12c0 5 3.7 9.1 8.4 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7C18.3 21.1 22 17 22 12c0-5.5-4.5-10-10-10z"></path></svg></a></li>

							<li class="hyp-top-icon"><a href="https://www.youtube.com/@hyp_access" aria-label="<?= esc_attr__('Hyp+Access YouTube','hyp-theme-25'); ?>" target="_blank"><svg class="svg-icon" aria-hidden="true" role="img" focusable="false" width="28" height="28" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path d="M21.8,8.001c0,0-0.195-1.378-0.795-1.985c-0.76-0.797-1.613-0.801-2.004-0.847c-2.799-0.202-6.997-0.202-6.997-0.202 h-0.009c0,0-4.198,0-6.997,0.202C4.608,5.216,3.756,5.22,2.995,6.016C2.395,6.623,2.2,8.001,2.2,8.001S2,9.62,2,11.238v1.517 c0,1.618,0.2,3.237,0.2,3.237s0.195,1.378,0.795,1.985c0.761,0.797,1.76,0.771,2.205,0.855c1.6,0.153,6.8,0.201,6.8,0.201 s4.203-0.006,7.001-0.209c0.391-0.047,1.243-0.051,2.004-0.847c0.6-0.607,0.795-1.985,0.795-1.985s0.2-1.618,0.2-3.237v-1.517 C22,9.62,21.8,8.001,21.8,8.001z M9.935,14.594l-0.001-5.62l5.404,2.82L9.935,14.594z"></path></svg></a></li>

						</ul>
					</nav>

					<nav aria-label="<?= esc_attr__('Main Navigation Menu','hyp-theme-25'); ?>">
						<ul class="hyp-nav-main">
							<?php if ( has_nav_menu("primary") ) {
					            wp_nav_menu([ "items_wrap" => '%3$s',  "theme_location" => "primary" ]);
							} ?>
						</ul>
					</nav>

				</div>

				<div class="hyp-mobile-nav">

					<button class="hyp-mobile-control" aria-label="<?= esc_attr__('Open Navigation Menu','hyp-theme-25'); ?>" aria-controls="hyp-mobile-nav" aria-expanded="false">
						<svg class="hyp-mobile-close" aria-hidden="true" focusable="false" width="50px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g id="Menu / Close_MD">
								<path id="Vector" d="M18 18L12 12M12 12L6 6M12 12L18 6M12 12L6 18" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</g>
						</svg>
						<svg class="hyp-mobile-open" aria-hidden="true" focusable="false" width="50px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M5 6.5H19V8H5V6.5Z" fill="#1F2328"/>
							<path d="M5 16.5H19V18H5V16.5Z" fill="#1F2328"/>
							<path d="M5 11.5H19V13H5V11.5Z" fill="#1F2328"/>
						</svg>
					</button>

					<nav class="hyp-mobile-menu" id="hyp-mobile-nav">

						<ul class="hyp-mobile-social">

							<li class="hyp-top-icon">
								<a href="https://www.instagram.com/hyp_access/" aria-label="<?= esc_attr__('Hyp+Access Instagram','hyp-theme-25'); ?>" target="_blank">
									<svg class="svg-icon" aria-hidden="true" role="img" focusable="false" width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd" d="M11.9962 0.0078125C8.73824 0.0078125 8.32971 0.021622 7.05019 0.080003C5.77333 0.138241 4.90129 0.341051 4.13824 0.637622C3.34938 0.944146 2.68038 1.35434 2.01343 2.02124C1.34652 2.68819 0.936333 3.35719 0.629809 4.14605C0.333238 4.9091 0.130429 5.78115 0.0721905 7.058C0.0138095 8.33753 0 8.74605 0 12.0041C0 15.262 0.0138095 15.6705 0.0721905 16.9501C0.130429 18.2269 0.333238 19.099 0.629809 19.862C0.936333 20.6509 1.34652 21.3199 2.01343 21.9868C2.68038 22.6537 3.34938 23.0639 4.13824 23.3705C4.90129 23.667 5.77333 23.8698 7.05019 23.9281C8.32971 23.9864 8.73824 24.0002 11.9962 24.0002C15.2542 24.0002 15.6627 23.9864 16.9422 23.9281C18.2191 23.8698 19.0911 23.667 19.8542 23.3705C20.643 23.0639 21.312 22.6537 21.979 21.9868C22.6459 21.3199 23.0561 20.6509 23.3627 19.862C23.6592 19.099 23.862 18.2269 23.9202 16.9501C23.9786 15.6705 23.9924 15.262 23.9924 12.0041C23.9924 8.74605 23.9786 8.33753 23.9202 7.058C23.862 5.78115 23.6592 4.9091 23.3627 4.14605C23.0561 3.35719 22.6459 2.68819 21.979 2.02124C21.312 1.35434 20.643 0.944146 19.8542 0.637622C19.0911 0.341051 18.2191 0.138241 16.9422 0.080003C15.6627 0.021622 15.2542 0.0078125 11.9962 0.0078125ZM7.99748 12.0041C7.99748 14.2125 9.78776 16.0028 11.9962 16.0028C14.2047 16.0028 15.995 14.2125 15.995 12.0041C15.995 9.79557 14.2047 8.00529 11.9962 8.00529C9.78776 8.00529 7.99748 9.79557 7.99748 12.0041ZM5.836 12.0041C5.836 8.60181 8.594 5.84381 11.9962 5.84381C15.3984 5.84381 18.1564 8.60181 18.1564 12.0041C18.1564 15.4062 15.3984 18.1642 11.9962 18.1642C8.594 18.1642 5.836 15.4062 5.836 12.0041ZM18.3998 7.03996C19.1949 7.03996 19.8394 6.39548 19.8394 5.60043C19.8394 4.80538 19.1949 4.16086 18.3998 4.16086C17.6048 4.16086 16.9603 4.80538 16.9603 5.60043C16.9603 6.39548 17.6048 7.03996 18.3998 7.03996Z"/>
									</svg>
								</a>
							</li>

							<li class="hyp-top-icon">
								<a href="https://www.tiktok.com/@hyp_access" aria-label="<?= esc_attr__('Hyp+Access TikTok','hyp-theme-25'); ?>" target="_blank">
									<svg class="svg-icon" aria-hidden="true" role="img" focusable="false" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
										<path d="M19.589 6.686a4.793 4.793 0 0 1-3.77-4.245V2h-3.445v13.672a2.896 2.896 0 0 1-5.201 1.743l-.002-.001.002.001a2.895 2.895 0 0 1 3.183-4.51v-3.5a6.329 6.329 0 0 0-5.394 10.692 6.33 6.33 0 0 0 10.857-4.424V8.687a8.182 8.182 0 0 0 4.773 1.526V6.79a4.831 4.831 0 0 1-1.003-.104z"/>
									</svg>
								</a>
							</li>

							<li class="hyp-top-icon"><a href="https://www.facebook.com/HypAccess" aria-label="<?= esc_attr__('Hyp+Access Facebook','hyp-theme-25'); ?>" target="_blank"><svg class="svg-icon" aria-hidden="true" role="img" focusable="false" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 2C6.5 2 2 6.5 2 12c0 5 3.7 9.1 8.4 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7C18.3 21.1 22 17 22 12c0-5.5-4.5-10-10-10z"></path></svg></a></li>

							<li class="hyp-top-icon"><a href="https://www.youtube.com/@hyp_access" aria-label="<?= esc_attr__('Hyp+Access YouTube','hyp-theme-25'); ?>" target="_blank"><svg class="svg-icon" aria-hidden="true" role="img" focusable="false" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path d="M21.8,8.001c0,0-0.195-1.378-0.795-1.985c-0.76-0.797-1.613-0.801-2.004-0.847c-2.799-0.202-6.997-0.202-6.997-0.202 h-0.009c0,0-4.198,0-6.997,0.202C4.608,5.216,3.756,5.22,2.995,6.016C2.395,6.623,2.2,8.001,2.2,8.001S2,9.62,2,11.238v1.517 c0,1.618,0.2,3.237,0.2,3.237s0.195,1.378,0.795,1.985c0.761,0.797,1.76,0.771,2.205,0.855c1.6,0.153,6.8,0.201,6.8,0.201 s4.203-0.006,7.001-0.209c0.391-0.047,1.243-0.051,2.004-0.847c0.6-0.607,0.795-1.985,0.795-1.985s0.2-1.618,0.2-3.237v-1.517 C22,9.62,21.8,8.001,21.8,8.001z M9.935,14.594l-0.001-5.62l5.404,2.82L9.935,14.594z"></path></svg></a></li>

						</ul>

						<?php wp_nav_menu(["theme_location" => "primary", "container" => false,
							"menu_class" => "hyp-mobile-main", "items_wrap" => '<ul class="%2$s">%3$s</ul>', ]); ?>

						<ul class="hyp-mobile-top">

							<?php wp_nav_menu([ 'theme_location' => 'secondary', 'container' => false,
									'items_wrap' => '%3$s', 'echo' => true ]); ?>

							<?php if (is_user_logged_in()): ?>
								<li><a href="/dashboard" class="hyp-top-link login"><?= esc_html__('Dashboard', 'hyp-theme-25' ); ?></a></li>
							<?php else: ?>
								<li><a href="/login" class="hyp-top-link login"><?= esc_html__('Login', 'hyp-theme-25' ); ?></a></li>
							<?php endif; ?>

						</ul>

					</nav>

				</div>

			</div>

		</header>
