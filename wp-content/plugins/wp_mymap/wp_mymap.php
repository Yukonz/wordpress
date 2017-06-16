<?php
/*
Plugin Name: WP_MyMap
Description: My third plugin
Version: 1.0
Author: Viktor.G
Text Domain: wp-mymap
*/

register_activation_hook(__FILE__, 'wp_mymap_activation');
register_deactivation_hook(__FILE__, 'wp_mymap_deactivation');
register_uninstall_hook(__FILE__, 'wp_mymap_uninstall');

function wp_mymap_activation()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'tweets';
    $sql = "CREATE TABLE {$table_name} (id int(10) NOT NULL AUTO_INCREMENT,
                                        name text NOT NULL,
                                        text text NOT NULL,
                                        date text NOT NULL,
                                        tag text NOT NULL,
                                        PRIMARY KEY  (id)
                                        )";
    $wpdb->query($sql);
}

function wp_mymap_deactivation()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'tweets';
    $sql = "DROP TABLE IF EXISTS {$table_name}";
    $wpdb->query($sql);
}

function wp_mymap_uninstall()
{
    wp_myplugin_deactivation();
}

add_action('wp', 'wp_hourly_tweets');
add_action('get_hourly_tweets', 'get_tweets');
function wp_hourly_tweets() {
    if ( !wp_next_scheduled( 'get_hourly_tweets' ) ) {
        wp_schedule_event(time(), 'hourly', 'get_hourly_tweets');
    }
}

function get_tweets()
{
    global $wpdb;
    $tweets_count = 10;
    require_once 'TwitterAPIExchange.php';

    $settings = array(
        'oauth_access_token' => "875334979584221188-g4ydaDMd4l9ltdoM8NGSVLPEJEf91ES",
        'oauth_access_token_secret' => "mYr3inGNgPJm6sKa6fIHB9uVQ8pZHuxQVUAwZx00q1EyJ",
        'consumer_key' => "zFjjTO3dlXHrqqLtRSSm6ijzH",
        'consumer_secret' => "IUp6JrynizBW5o1o935lTW0cW2oNlnCHoZN6ZjhZZUfOzmSPUR"
    );

    $twitterJsonUrl = 'https://api.twitter.com/1.1/search/tweets.json';
    $getfield = '?q=' . get_option('twitter_subject') . '&geocode=' . get_option('map_latitude') . ','
                                                                  . get_option('map_longitude') . ','
                                                                  . get_option('map_radius') . 'km,&count='
                                                                  . $tweets_count;

    $twitter = new TwitterAPIExchange($settings);
    $response = $twitter->setGetfield($getfield)
        ->buildOauth($twitterJsonUrl, 'GET')
        ->performRequest();

    $tweets = json_decode($response, $assoc = TRUE);

    $table_name = $wpdb->prefix . 'tweets';
    $wpdb->query("TRUNCATE TABLE {$table_name}");

    foreach ($tweets['statuses'] as $tweet) {
        $tweet_data = array('text'=>$tweet['text'], 'name' => $tweet['user']['name'], 'date'=>$tweet['created_at'], 'tag'=>get_option('twitter_subject'));
        $wpdb->insert($table_name, $tweet_data, array("%s", "%s", "%s"));
        echo '<br><hr>' . $tweet['text'] . ' | ' . $tweet['created_at'] . ' | ' . $tweet['user']['name'];
    }
}


add_action('admin_menu', 'wp_mymap_menu');
function wp_mymap_menu()
{
    add_menu_page('WP Mymap Settings',
        'WP MyMap Settings',
        'administrator',
        'map_settins',
        'wp_mymap_settings',
         plugins_url('/wp_mymap.png', __FILE__));
}

add_action( 'admin_init', 'register_mymap_settings' );
function register_mymap_settings()
{
    register_setting( 'wp_mymap-settings-group', 'twitter_subject' , 'check_twitter_subject');
    register_setting( 'wp_mymap-settings-group', 'map_latitude');
    register_setting( 'wp_mymap-settings-group', 'map_longitude');
    register_setting( 'wp_mymap-settings-group', 'map_radius');
}

function check_twitter_subject($data){
    $data = sanitize_text_field( $data );
    add_settings_error( 'twitter_subject', 'settings_updated', __('Settings saved','wp-mymap') , 'updated');
    return $data;
}

function wp_mymap_settings()
{
    get_tweets(); ?>
    <h2>WP MyMap Plugin</h2>
    <?php settings_errors(); ?>
    <div class="wrapper">
        <div id='form'>
            <form id='settings_form' method='post' action='options.php'>

                <?php settings_fields( 'wp_mymap-settings-group' ); ?>

                <table class="form-table">
                    <tr>
                        <td>
                            <label for='twitter_subject'><?php _e('Subject of the Twitter messages','wp-mymap') ?></label><br>
                            <input id='twitter_subject' type = 'text' name='twitter_subject' value='<?php echo get_option('twitter_subject'); ?>'>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for='map_latitude'><?php _e('Altitude','wp-mymap') ?></label><br>
                            <input id='map_latitude' type = 'text' name='map_latitude' required value='<?php echo get_option('map_latitude'); ?>'>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for='map_longitude'><?php _e('Longitude','wp-mymap') ?></label><br>
                            <input id='map_longitude' type = 'text' name='map_longitude' required value='<?php echo get_option('map_longitude'); ?>'>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for='map_radius'><?php _e('Tweets range, KM','wp-mymap') ?></label><br>
                            <input id='map_radius' name='map_radius' type='range' min='1' max='1000' step='1' value='<?php echo get_option('map_radius'); ?>'/><br>
                            <input id='map_radius_value' name='map_radius_value' type='text' value='<?php echo get_option('map_radius'); ?>'>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input id='submit' type="submit" class="button-primary" value="<?php _e('Save changes','wp-mymap') ?>" />
                </p>
            </form>
        </div>
        <div id='map'></div>
    </div>

<?php }

function plugin_assets()
{
    wp_enqueue_style( 'style_css', plugin_dir_url( __FILE__ ) . '/css/style.css' );
    wp_enqueue_script('jquery');
    wp_enqueue_script( 'map_js', plugin_dir_url( __FILE__ ) . '/js/map.js' , 'jquery');
    wp_enqueue_script( 'google_maps_js', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBqAcpYEHCnK_peTEE0QrQtKO9SuAnE2pQ' , 'map_js', false, true);

    $map_params = array(
                        'map_latitude'=>get_option('map_latitude'),
                        'map_longitude'=>get_option('map_longitude'),
                        'map_radius'=>get_option('map_radius')
                        );
    wp_localize_script( 'map_js', 'params', $map_params );
}
add_action( 'admin_enqueue_scripts', 'plugin_assets');

function init_textdomain()
{
    load_plugin_textdomain('wp-mymap', false ,'wp_mymap/languages/');
}
add_action('init', 'init_textdomain');

function get_tweets_from_db(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'tweets';
    $results = $wpdb->get_results("SELECT text, name, date FROM {$table_name} ORDER BY date ASC LIMIT 20");
    foreach ($results as $result){
        echo "<div class='tweet' style='border: 2px solid #dadada; border-radius: 4px; margin: 20px; padding: 10px'>
                   <h4 style='background-color: #dadada; padding: 10px'>{$result->name}</h4>
                   <p>{$result->text}</p>
                   <span style='background-color: #cefa60'>{$result->date}</span>
              </div>";
    }
}
add_shortcode( 'twitter', 'get_tweets_from_db' );


//WP-TABLE

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
require_once('TweetsTable.php');
require_once('TweetsPage.php');








add_action( 'plugins_loaded', function () {
    Tweets_page::get_instance();
} );