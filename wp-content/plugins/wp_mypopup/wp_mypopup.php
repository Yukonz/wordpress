<?php
/*
Plugin Name: WP_MyPopup
Description: My second plugin
Version: 1.0
Author: Viktor.G
Text Domain: wp-mypopup
*/

add_action('admin_menu', 'wp_mypopup_menu');
function wp_mypopup_menu()
{
    add_menu_page('WP MyPopup Settings',
        'WP MyPopup Settings',
        'administrator',
        __FILE__,
        'wp_mypopup_settings',
         plugins_url('/wp_mypopup.png', __FILE__));
}

add_action( 'admin_init', 'register_mypopup_settings' );
function register_mypopup_settings()
{
    register_setting( 'wp_mypopup-settings-group', 'title_text' , 'check_title_text');
    register_setting( 'wp_mypopup-settings-group', 'main_text' , 'check_main_text');
    register_setting( 'wp_mypopup-settings-group', 'delay_time', 'check_delay_time' );
    register_setting( 'wp_mypopup-settings-group', 'show_time', 'check_show_time');
    register_setting( 'wp_mypopup-settings-group', 'close_button' );
    register_setting( 'wp_mypopup-settings-group', 'esc_button' );
    register_setting( 'wp_mypopup-settings-group', 'overlay_click' );
}


function check_delay_time($data){
    $data = sanitize_text_field( $data );
    if (is_numeric($data)) return $data;
    return get_option('delay_time');
}

function check_show_time($data){
    $data = sanitize_text_field( $data );
    if (is_numeric($data)) return $data;
    return get_option('show_time');
}

function check_title_text($data){
    $data = sanitize_text_field( $data );
    add_settings_error( 'title_text', 'settings_updated', __('Settings saved','wp-mypopup') , 'updated' );
    return $data;
}

function check_main_text($data){
    $data = sanitize_text_field( $data );
    return $data;
}


function wp_mypopup_settings()
{
    ?>
    <div class="wrap">
        <h2>WP MyPopup Plugin</h2>
        <?php settings_errors(); ?>
        <form id='settings_form' method='post' action='options.php'>

            <?php settings_fields( 'wp_mypopup-settings-group' ); ?>
            <table class="form-table">
                <tr>
                    <td>
                        <label for='title_text'><?php _e('Title text','wp-mypopup') ?></label><br>
                        <input type = 'text' name='title_text' value='<?php echo get_option('title_text'); ?>'>
                    </td>
                </tr>
                <tr>
                    <td>
                        <textarea cols='50' rows='10' name='main_text'><?php echo get_option('main_text'); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for='delay_time'><?php _e('Time before popup shows, ms','wp-mypopup') ?></label><br>
                        <input id='delay_time' type = 'text' name='delay_time' required value='<?php echo get_option('delay_time'); ?>'>
                        <div id='delay_message'></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for='show_time'><?php _e('Popup show time, ms','wp-mypopup') ?></label><br>
                        <input id='show_time' type = 'text' name='show_time' required value='<?php echo get_option('show_time'); ?>'>
                        <div id='show_message'></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type = 'checkbox' name='close_button' value='1' <?php checked(1, get_option('close_button'), true); ?>>
                        <label for='close_button'><?php _e('Show Close button','wp-mypopup') ?></label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type = 'checkbox' name='esc_button' value='1' <?php checked(1, get_option('esc_button'), true); ?>>
                        <label for='esc_button'><?php _e('Close popup by ESK key pressing','wp-mypopup') ?></label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type = 'checkbox' name='overlay_click' value='1' <?php checked(1, get_option('overlay_click'), true); ?>>
                        <label for='overlay_click'><?php _e('Close popup by overlay click','wp-mypopup') ?></label>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input id='submit' type="submit" class="button-primary" value="<?php _e('Save changes','wp-mypopup') ?>" />
            </p>
        </form>
        <div id='success_message'></div>
    </div>

<?php }

function plugin_assets()
{
    wp_enqueue_style( 'style_css', plugin_dir_url( __FILE__ ) . '/css/style.css' );
    wp_enqueue_script('jquery');
    wp_enqueue_script( 'popup_js', plugin_dir_url( __FILE__ ) . '/js/popup.js' );

    $popup_params = array('title_text'=>get_option('title_text'),
                        'main_text'=>get_option('main_text'),
                        'delay_time'=>get_option('delay_time'),
                        'show_time'=>get_option('show_time'),
                        'close_button'=>get_option('close_button'),
                        'esc_button'=>get_option('esc_button'),
                        'overlay_click'=>get_option('overlay_click')
                        );
    wp_localize_script( 'popup_js', 'params', $popup_params );
}
add_action( 'wp_enqueue_scripts', 'plugin_assets');

function admin_plugin_assets()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script( 'admin_js', plugin_dir_url( __FILE__ ) . '/js/admin.js' );
}
add_action( 'admin_enqueue_scripts', 'admin_plugin_assets');

function init_textdomain()
{
    load_plugin_textdomain('wp-mypopup', false ,'wp_mypopup/languages/');
}
add_action('init', 'init_textdomain');




