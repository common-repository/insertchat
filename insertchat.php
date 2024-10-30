<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

/**
 * Plugin Name: InsertChat
 * Plugin URI: https://www.insertchat.com
 * Description: Integrate powerful AI chatbots seamlessly into your WordPress website.
 * Version: 1.1.6
 * Author: InsertChat
 * Author URI: https://www.insertchat.com/
 * License: GPL2
 */

function icg_ai_add_options_page() {
    $response = wp_remote_get(plugins_url('favicon_white.svg', __FILE__));

    if (is_wp_error($response)) {
        return;
    }

    $icon_svg = wp_remote_retrieve_body( $response );
    $icon_svg = str_replace('<svg ', '<svg width="20" height="20" ', $icon_svg);
    $icon_svg_base64 = 'data:image/svg+xml;base64,' . base64_encode($icon_svg);

    /*
    add_menu_page(
        'InsertChat AI',
        'InsertChat AI',
        'manage_options',
        'insertchat',
        'icg_ai_options_page',
        $icon_svg_base64,
        6
    );
    */

    $icon_url = plugins_url('favicon_white.svg', __FILE__);

    add_menu_page(
        'InsertChat AI',
        'InsertChat AI',
        'manage_options',
        'insertchat',
        'icg_ai_options_page',
        $icon_url,
        6
    );
}

function icg_ai_register_settings() {
    register_setting('icg_ai_options_group', 'icg_ai_options', 'icg_ai_options_validate');
    add_settings_section('icg_ai_main', '', 'icg_ai_section_text', 'insertchat');
    add_settings_field('icg_ai_id', 'Chatbot ID', 'icg_ai_setting_string', 'insertchat', 'icg_ai_main');
}

function icg_ai_section_text() {
    // echo "";
}

function icg_ai_setting_string() {
    $options = get_option('icg_ai_options');
    $chatbot_id = isset($options['id']) ? esc_attr($options['id']) : '';
    
    echo "<div class='insertchat-form-group'>
        <h2>" . esc_html__('How do I start using the InsertChat plugin?', 'insertchat') . "</h2>
        <ul>
            <li>" . esc_html__('1. Install the InsertChat plugin on your WordPress website.', 'insertchat') . "</li>
            <li>" . esc_html__('2. Sign up for an account at InsertChat.', 'insertchat') . " <a href='" . esc_url('https://app.insertchat.com/auth/access') . "' target='_blank'>" . esc_html__('InsertChat', 'insertchat') . "</a></li>
            <li>" . esc_html__('3. Create and customize your first AI Chatbot at InsertChat.', 'insertchat') . " <a href='" . esc_url('https://app.insertchat.com/chatbots') . "' target='_blank'>" . esc_html__('InsertChat', 'insertchat') . "</a></li>
            <li>" . esc_html__('4. Retrieve the AI chatbot ID from the "Install Instructions" 4. Retrieve the AI chatbot ID from the "Install Instructions" page of your newly created AI chatbot.', 'insertchat') . "</li>
            <li>" . esc_html__('5. Return to your WordPress website and go to \'InsertChat AI\' in the dashboard sidebar.', 'insertchat') . "</li>
            <li>" . esc_html__('6. Paste your AI Chatbot ID into the designated field.', 'insertchat') . "</li>
            <li>" . esc_html__('7. Click \'Save Changes\' to activate your AI Chatbot seamlessly.', 'insertchat') . "</li>
            <li>" . esc_html__('8. Clear any caching mechanisms (Cloudflare, LiteSpeed Cache, WP Super Cache, W3 Total Cache, ...).', 'insertchat') . "</li>
            <li>" . esc_html__('9. Visit your website to ensure your AI chatbot bubble loads correctly.', 'insertchat') . "</li>
            <li>" . esc_html__('10. Continuously tweak, customize, and train your AI chatbot for optimal performance.', 'insertchat') . "</li>
        </ul>
        <br /><hr /><br />
        <label for='insertchat_id'>" . esc_html__('Your AI Chatbot ID', 'insertchat') . "</label>
        <input type='text' name='icg_ai_options[id]' id='insertchat_id' value='" . $chatbot_id . "' />
        <p class='description'>" . esc_html__('Copy your AI Chatbot ID from your chatbot "Install Instructions" tab directly from the InsertChat app.', 'insertchat') . " <a href='" . esc_url('https://app.insertchat.com/chatbots') . "' target='_blank'>" . esc_html__('InsertChat app', 'insertchat') . "</a>.</p>
    </div>";
}

function icg_ai_options_page() {
?>
    <div class="wrap insertchat-wrap">
        <?php settings_errors('icg_ai_options'); ?>

        <div class="insertchat-form-container">
            <form method="post" action="options.php">
                <div class="insertchat-logo-container">
                    <a href="https://www.insertchat.com" target="_blank">
                        <img alt="InsertChat" loading="lazy" src="<?php echo esc_url(plugins_url('logo_black.svg', __FILE__)); ?>">
                    </a>
                </div>

                <?php settings_fields('icg_ai_options_group'); ?>
                <?php do_settings_sections('insertchat'); ?>
                <?php wp_nonce_field('icg_ai_options_nonce_action', 'icg_ai_options_nonce'); ?>

                <div class="submit-btn-container">
                    <?php submit_button(); ?>
                </div>
            </form>
        </div>
    </div>
<?php
}

function icg_ai_options_validate($input) {
    // Check if our nonce is set
    if (!isset($_POST['icg_ai_options_nonce'])) {
        return $input;
    }

    // Verify that the nonce is valid
    if (!wp_verify_nonce(
        sanitize_text_field(wp_unslash($_POST['icg_ai_options_nonce'])),
        'icg_ai_options_nonce_action'
    )) {
        return $input;
    }

    add_settings_error(
        'icg_ai_options',
        'icg_ai_settings_saved',
        'Settings saved successfully.',
        'updated'
    );

    return $input;
}

function icg_ai_embed_chatbot() {
    $options = get_option('icg_ai_options');
    $insertchat_id = trim($options['id'] ?? '');

    if (!empty($insertchat_id)) {
        $script_url = 'https://bot.insertchat.com/widgets/chatbot.js?wordpress=true&widget_id=' . esc_attr($insertchat_id);

        wp_enqueue_script(
            'insertchat-bubble-script',
            $script_url,
            array(),
            '1.0.0',
            true
        );

        // Add async attribute to the script
        wp_script_add_data('insertchat-bubble-script', 'async', true);

        // Define a global window variable for the chatbot
        $inline_script = "
            window.ICG_BOT_ID = '" . esc_js($insertchat_id) . "';
            window.ICG_BOT_TYPE = 'bubble';
            window.ICG_BOT_HEIGHT = 750;
            window.ICG_BOT_AUTOFOCUS = false;
            window.ICG_BOT_OVERRIDE_OPENER = '';
            window.ICG_USER_ID = '';
            window.ICG_USER_EMAIL = '';
            window.ICG_USER_FIRSTNAME = '';
            window.ICG_USER_LASTNAME = '';
            window.ICG_USER_TAGS = [];
            window.ICG_USER_METADATA = {};
        ";

        wp_add_inline_script('insertchat-bubble-script', $inline_script);
    }
}

function icg_ai_admin_styles($hook) {
    if ($hook != 'toplevel_page_insertchat') {
        return;
    }

    wp_enqueue_style(
        'insertchat-admin-style',
        plugins_url('insertchat-admin-style.css', __FILE__),
        array(),
        '1.0.6'
    );
}

function icg_ai_remove_unrelated_admin_notices() {
    global $pagenow;

    if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'insertchat') {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices', 10, 1); 
    }
}

function icg_ai_admin_icon_css() {
    $custom_css = "
        .toplevel_page_insertchat .wp-menu-image img {
            width: 20px !important;
            height: 20px !important;
            padding: 7px 0 !important;
        }
    ";

    wp_add_inline_style('insertchat-admin-style', $custom_css);
}

add_action('wp_enqueue_scripts', 'icg_ai_embed_chatbot');
add_action('admin_head', 'icg_ai_admin_icon_css');
add_action('admin_enqueue_scripts', 'icg_ai_remove_unrelated_admin_notices');
add_action('admin_enqueue_scripts', 'icg_ai_admin_styles');
add_action('admin_menu', 'icg_ai_add_options_page');
add_action('admin_init', 'icg_ai_register_settings');