<?php

// Get Setting
function hyp_setting( $name = false ) {
	$settings = get_option('hyp_theme_options');
	return !empty($settings[$name]) ? $settings[$name] : false;
}

// Register the settings page under "Settings"
add_action('admin_menu', 'hyp_add_settings_subpage');
function hyp_add_settings_subpage() {
    add_options_page(
        __( 'Hyp Theme Settings', 'hyp-theme-25' ),
        __( 'Theme', 'hyp-theme-25' ),
        'manage_options',
        'hyp-theme-settings',
        'hyp_render_settings'
    );
}

// Register the settings, sections, and fields
add_action('admin_init', 'hyp_register_settings');
function hyp_register_settings() {

    register_setting('hyp_settings_group', 'hyp_theme_options');

    add_settings_section(
        'hyp_cookie_section',
        __( 'Cookie Consent Banner', 'hyp-theme-25' ),
        'hyp_cookie_section_text',
        'hyp-theme-settings'
    );

    add_settings_field('cookie_enable', __('Cookie Banner', 'hyp-theme-25' ), 'hyp_render_cookie_check', 'hyp-theme-settings', 'hyp_cookie_section');
    add_settings_field('cookie_text', __('Consent Agreement', 'hyp-theme-25' ), 'hyp_render_cookie_text', 'hyp-theme-settings', 'hyp_cookie_section');

	add_settings_section(
        'hyp_banner_section',
        __( 'Footer Banner', 'hyp-theme-25' ),
        'hyp_banner_section_text',
        'hyp-theme-settings'
    );

	add_settings_field('banner_enable', __('Enable Banner', 'hyp-theme-25' ), 'hyp_render_banner_check', 'hyp-theme-settings', 'hyp_banner_section');
	add_settings_field('banner_title', __('Banner Title', 'hyp-theme-25' ), 'hyp_render_banner_title', 'hyp-theme-settings', 'hyp_banner_section');
	add_settings_field('banner_text', __('Banner Text', 'hyp-theme-25' ), 'hyp_render_banner_text', 'hyp-theme-settings', 'hyp_banner_section');
	add_settings_field('banner_button_text', __('Button Text', 'hyp-theme-25' ), 'hyp_render_banner_button_text', 'hyp-theme-settings', 'hyp_banner_section');
	add_settings_field('banner_button_link', __('Button URL', 'hyp-theme-25' ), 'hyp_render_banner_button_url', 'hyp-theme-settings', 'hyp_banner_section');

    add_settings_section(
        'hyp_disclaimer_section',
        __( 'Disclaimers', 'hyp-theme-25' ),
        'hyp_disclaimer_section_text',
        'hyp-theme-settings'
    );

    add_settings_field('disclaimer_blog', __('Blog Disclaimer', 'hyp-theme-25' ), 'hyp_render_disclaimer_blog', 'hyp-theme-settings', 'hyp_disclaimer_section');
    add_settings_field('disclaimer_video', __('Video Disclaimer', 'hyp-theme-25' ), 'hyp_render_disclaimer_video', 'hyp-theme-settings', 'hyp_disclaimer_section');
    add_settings_field('disclaimer_footer', __('Footer Disclaimer', 'hyp-theme-25' ), 'hyp_render_disclaimer_footer', 'hyp-theme-settings', 'hyp_disclaimer_section');

    add_settings_section(
        'hyp_extensions_section',
        __( 'Modules', 'hyp-theme-25' ),
        'hyp_extensions_section_text',
        'hyp-theme-settings'
    );

    add_settings_field('accessibility_enable', __('Accessibility Tool', 'hyp-theme-25' ), 'hyp_render_accessibility_check', 'hyp-theme-settings', 'hyp_extensions_section');
    add_settings_field('excerpts_enable', __('Advanced Excerpts', 'hyp-theme-25' ), 'hyp_render_excerpts_check', 'hyp-theme-settings', 'hyp_extensions_section');
    add_settings_field('font_sizes_enable', __('TinyMCE Font Sizes', 'hyp-theme-25' ), 'hyp_render_tinymce_check', 'hyp-theme-settings', 'hyp_extensions_section');
	add_settings_field('newsletter_button', __('Newsletter Button', 'hyp-theme-25' ), 'hyp_render_newsletter_check', 'hyp-theme-settings', 'hyp_extensions_section');

    add_settings_section(
        'hyp_misc_section',
        __( 'Misc', 'hyp-theme-25' ),
        'hyp_misc_section_text',
        'hyp-theme-settings'
    );

    add_settings_field('news_title', __('News Page Title', 'hyp-theme-25' ), 'hyp_render_news_title', 'hyp-theme-settings', 'hyp_misc_section');
	add_settings_field('givebutter_code', __('Givebutter Button Code', 'hyp-theme-25' ), 'hyp_render_givebutter_code', 'hyp-theme-settings', 'hyp_misc_section');

}

function hyp_extensions_section_text() {
    echo '<p>' . esc_html__('These extensions are built into the theme to expand functionality.', 'hyp-theme-25' ) . '</p>';
}

function hyp_cookie_section_text() {
    echo '<p>' . esc_html__('This is a basic GDPR/CCPA consent banner that loads at the bottom of the site.', 'hyp-theme-25' ) . '</p>';
}

function hyp_banner_section_text() {
    echo '<p>' . esc_html__('This is the notification banner at the bottom of the site in the footer.', 'hyp-theme-25' ) . '</p>';
}

function hyp_disclaimer_section_text() {
    echo '<p>' . esc_html__('Legal discalimers that are show at various locations on the site.', 'hyp-theme-25' ) . '</p>';
}

function hyp_misc_section_text() {
    echo '<p>' . esc_html__('Misc content and settings.', 'hyp-theme-25' ) . '</p>';
}

// Footer Banner
function hyp_render_banner_check() {
    $options = get_option('hyp_theme_options');
    $checked = !empty($options['banner_enable']) ? 'checked' : '';
    echo "<label><input type='checkbox' name='hyp_theme_options[banner_enable]' value='1' $checked /> " . esc_html__('Enable the notification banner in footer', 'hyp-theme-25' ) . "</label>";
}

// Footer Banner Title
function hyp_render_banner_title() {
    $options = get_option('hyp_theme_options');
    $value = $options['banner_title'] ?? '';
    echo "<input type='text' name='hyp_theme_options[banner_title]' value='" . esc_attr($value) . "' class='regular-text' />";
}

// Footer Banner Text
function hyp_render_banner_text() {
	$options = get_option('hyp_theme_options');
    $value = $options['banner_text'] ?? '';
    echo "<textarea name='hyp_theme_options[banner_text]' rows='2' class='large-text'>" . esc_textarea($value) . "</textarea>";
}

// Footer Banner Button Text
function hyp_render_banner_button_text() {
    $options = get_option('hyp_theme_options');
    $value = $options['banner_button_text'] ?? '';
    echo "<input type='text' name='hyp_theme_options[banner_button_text]' value='" . esc_attr($value) . "' class='regular-text' />";
}

// Footer Banner Button URL
function hyp_render_banner_button_url() {
    $options = get_option('hyp_theme_options');
    $value = $options['banner_button_url'] ?? '';
    echo "<input type='text' name='hyp_theme_options[banner_button_url]' value='" . esc_attr($value) . "' class='regular-text' />";
}

// Accessibility Tool
function hyp_render_accessibility_check() {
    $options = get_option('hyp_theme_options');
    $checked = !empty($options['accessibility_enable']) ? 'checked' : '';
    echo "<label><input type='checkbox' name='hyp_theme_options[accessibility_enable]' value='1' $checked /> " . esc_html__('Enable the Accessibility Tool', 'hyp-theme-25' ) . "</label>";
}

// Cookie Banner
function hyp_render_cookie_check() {
    $options = get_option('hyp_theme_options');
    $checked = !empty($options['cookie_enable']) ? 'checked' : '';
    echo "<label><input type='checkbox' name='hyp_theme_options[cookie_enable]' value='1' $checked /> " . esc_html__('Enable the Cookie Consent Banner', 'hyp-theme-25' ) . "</label>";
}

// Advanced Excerpts
function hyp_render_excerpts_check() {
    $options = get_option('hyp_theme_options');
    $checked = !empty($options['excerpts_enable']) ? 'checked' : '';
    echo "<label><input type='checkbox' name='hyp_theme_options[excerpts_enable]' value='1' $checked /> " . esc_html__('Enable Advanced WYSIWYG Excerpts', 'hyp-theme-25' ) . "</label>";
}

// Font Sizes
function hyp_render_tinymce_check() {
    $options = get_option('hyp_theme_options');
    $checked = !empty($options['font_sizes_enable']) ? 'checked' : '';
    echo "<label><input type='checkbox' name='hyp_theme_options[font_sizes_enable]' value='1' $checked /> " . esc_html__('Enable Font Size Menu in Text Editor', 'hyp-theme-25' ) . "</label>";
}

// Newsletter Button
function hyp_render_newsletter_check() {
    $options = get_option('hyp_theme_options');
    $checked = !empty($options['newsletter_button']) ? 'checked' : '';
    echo "<label><input type='checkbox' name='hyp_theme_options[newsletter_button]' value='1' $checked /> " . esc_html__('Enable floating Newsletter sign up button in footer of site', 'hyp-theme-25' ) . "</label>";
}

// Cookie Banner Consent Text
function hyp_render_cookie_text() {
    $options = get_option('hyp_theme_options');
    $value = $options['cookie_text'] ?? '';
    echo "<textarea name='hyp_theme_options[cookie_text]' rows='4' class='large-text'>" . esc_textarea($value) . "</textarea>";
}

// Disclaimer: Blog Posts
function hyp_render_disclaimer_blog() {
    $options = get_option('hyp_theme_options');
    $value = $options['disclaimer_blog'] ?? '';
    echo "<textarea name='hyp_theme_options[disclaimer_blog]' rows='4' class='large-text'>" . esc_textarea($value) . "</textarea>";
}

// Disclaimer: Videos
function hyp_render_disclaimer_video() {
    $options = get_option('hyp_theme_options');
    $value = $options['disclaimer_video'] ?? '';
    echo "<textarea name='hyp_theme_options[disclaimer_video]' rows='4' class='large-text'>" . esc_textarea($value) . "</textarea>";
}

// Disclaimer: Footer
function hyp_render_disclaimer_footer() {
    $options = get_option('hyp_theme_options');
    $value = $options['disclaimer_footer'] ?? '';
    echo "<textarea name='hyp_theme_options[disclaimer_footer]' rows='4' class='large-text'>" . esc_textarea($value) . "</textarea>";
}

// News Page Title
function hyp_render_news_title() {
    $options = get_option('hyp_theme_options');
    $value = $options['news_title'] ?? '';
    echo "<textarea name='hyp_theme_options[news_title]' rows='2' class='large-text'>" . esc_textarea($value) . "</textarea>";
}

// Give Butter Code
function hyp_render_givebutter_code() {
    $options = get_option('hyp_theme_options');
    $value = $options['givebutter_code'] ?? '';
    echo "<input type='text' name='hyp_theme_options[givebutter_code]' value='" . esc_attr($value) . "' class='regular-text' />";
}

// Render the settings page
function hyp_render_settings() {
    ?>
    <div class="wrap">
        <h1><?= esc_html__('Theme Settings', 'hyp-theme-25' ) ?></h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('hyp_settings_group');
                do_settings_sections('hyp-theme-settings');
                submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Add external link meta field to posts
function hyp_register_meta() {
    register_post_meta( 'post', 'external-link', [
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'auth_callback' => function() {
            return current_user_can( 'edit_posts' );
        }
    ]);
}
add_action( 'init', 'hyp_register_meta' );

// Replace permalinks with external link on posts if available
function hyp_use_external_link_as_permalink( $permalink, $post ) {

    // Get the external link custom field
    $external_link = get_post_meta( $post->ID, 'external-link', true );

    // If the field exists and is a valid URL, use it
    return !empty( $external_link ) && filter_var( $external_link, FILTER_VALIDATE_URL ) ?
        esc_url_raw( $external_link ) : $permalink;

}
add_filter( 'post_link', 'hyp_use_external_link_as_permalink', 10, 2 );

// Register theme settings strings with PolyLang if installed
function hyp_register_pll_strings() {
    if (function_exists('pll_register_string')) {
        pll_register_string('cookie_text', hyp_setting('cookie_text'), 'Theme Setting', true);
		pll_register_string('news_title', hyp_setting('news_title'), 'Theme Setting');
        pll_register_string('blog_disclaimer', hyp_setting('disclaimer_blog'), 'Theme Setting');
		pll_register_string('video_disclaimer', hyp_setting('disclaimer_video'), 'Theme Setting');
		pll_register_string('footer_disclaimer', hyp_setting('disclaimer_footer'), 'Theme Setting');
    }
}
add_action('admin_init', 'hyp_register_pll_strings');

// Add support for polylang translation strings
function hyp_setting_translate( $setting ) {

	$string = hyp_setting($setting);

	if ( function_exists('pll__') )
		$translated = pll__($string);

	return isset($translated) ? $translated : $string;

}

// Adds page width to sidebar of pages
function hyp_add_page_width_meta_box() {
    add_meta_box(
        'page_width_box',
        'Page Width',
        'hyp_render_page_width_meta_box',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'hyp_add_page_width_meta_box');

// Add meta field for page width
function hyp_render_page_width_meta_box($post) {
    $value = get_post_meta($post->ID, '_page_width', true);
    ?>
    <label for="page_width">Chose Width: </label>
    <select name="page_width" id="page_width">
        <option value="">Default</option>
		<option value="hyp-wrap-tight" <?php selected($value, 'hyp-wrap-tight'); ?>>Narrow</option>
		<option value="hyp-wrap-full" <?php selected($value, 'hyp-wrap-full'); ?>>Full</option>
        <option value="hyp-wrap-wide" <?php selected($value, 'hyp-wrap-wide'); ?>>Wide</option>
        <option value="hyp-wrap-max" <?php selected($value, 'hyp-wrap-max'); ?>>Max</option>
    </select>
    <?php
}

// Save meta field for page width
function hyp_save_page_width_meta($post_id) {
    if (array_key_exists('page_width', $_POST)) {
        update_post_meta($post_id, '_page_width', sanitize_text_field($_POST['page_width']));
    }
}
add_action('save_post', 'hyp_save_page_width_meta');

// Set page width
function hyp_page_width() {
	$width = get_post_meta(get_the_ID(), '_page_width', true);
	return ( $width && !is_single() ) ? $width : 'hyp-wrap-tight';
}
