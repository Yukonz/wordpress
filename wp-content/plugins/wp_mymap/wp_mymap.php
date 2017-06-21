<?php
/*
Plugin Name: WP_MyMap
Description: My third plugin
Version: 1.0
Author: Viktor.G
Text Domain: wp-mymap
*/

session_start();
//unset($_SESSION['search']);

register_activation_hook(__FILE__, 'mymap_activation');
register_deactivation_hook(__FILE__, 'mymap_deactivation');
register_uninstall_hook(__FILE__, 'mymap_uninstall');

function mymap_activation()
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

function mymap_deactivation()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'tweets';
    $sql = "DROP TABLE IF EXISTS {$table_name}";
    $wpdb->query($sql);
}

function mymap_uninstall()
{
    wp_myplugin_deactivation();
}

add_action('wp', 'set_hourly_tweets');
add_action('get_hourly_tweets', 'get_tweets');
function set_hourly_tweets() {
    if ( !wp_next_scheduled( 'get_hourly_tweets' ) ) {
        wp_schedule_event(time(), 'hourly', 'get_hourly_tweets');
    }
}

//add_action('admin_menu', 'drop_search_results');
//function drop_search_results() {
//    unset($_SESSION['search']);
//}

add_action('update_option', 'get_tweets');
function get_tweets()
{
    global $wpdb;
    $tweets_count = 50;
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
    $sql ="INSERT INTO {$table_name} (name, text, date, tag) VALUES ";
    $subject = get_option('twitter_subject');

    for ($i=0;$i<$tweets_count;$i++){
        $tweet_name = $tweets['statuses'][$i]['user']['name'];
        $tweet_text = $tweets['statuses'][$i]['text'];
        $tweet_date = $tweets['statuses'][$i]['created_at'];

        $tweet_name = remove_special_characters($tweet_name);
        $tweet_text = remove_special_characters($tweet_text);

        echo "<hr>NAME: " . $tweet_name . "<br>TEXT: " . $tweet_text . "<br>DATE: " . $tweet_date . "<hr>" ;

        $sql .= "('{$tweet_name}', 
                  '{$tweet_text}', 
                  '{$tweet_date}', 
                  '{$subject}')";
        if ($i<($tweets_count - 1)){
            $sql .= ", ";
        }
    }
    $wpdb->query($sql);
}

function remove_special_characters ($data){
    $data = sanitize_text_field($data);
    $data = str_replace("'", " ", $data);

    $regex_emoticons = '/[\x{1F300}-\x{1F5FF}]/u';
    $data = preg_replace($regex_emoticons, '',$data);

    $regex_icons = '/[\x{1F600}-\x{1F64F}]/u';
    $data = preg_replace($regex_icons, '',$data);

    $regex_misc = '/[\x{2600}-\x{26FF}]/u';
    $data = preg_replace($regex_misc, '',$data);

    $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
    $data = preg_replace($regex_dingbats, '',$data);

    return $data;
}

add_action('admin_menu', 'register_mymap_menu');
function register_mymap_menu()
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
    do_action( 'load_scripts_for_map' );
//    get_tweets(); ?>
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

add_action( 'load_scripts_for_map', 'load_mymap_scripts');
function load_mymap_scripts()
{
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

add_action( 'load_search_script', 'load_search_save_script');
function load_search_save_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script( 'save_search_js', plugin_dir_url( __FILE__ ) . '/js/save_search.js' , 'jquery');
}

add_action( 'admin_enqueue_scripts', 'load_search_drop_script');
function load_search_drop_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script( 'drop_search_js', plugin_dir_url( __FILE__ ) . '/js/drop_search.js' , 'jquery');
}

add_action('admin_enqueue_scripts', 'load_mymap_styles');
function load_mymap_styles()
{
    wp_enqueue_style( 'style_css', plugin_dir_url( __FILE__ ) . '/css/style.css' );
}


add_action('init', 'init_textdomain');
function init_textdomain()
{
    load_plugin_textdomain('wp-mymap', false ,'wp_mymap/languages/');
}

add_shortcode( 'twitter', 'get_tweets_from_db' );
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

//WP-TABLE

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
require_once('Tweets_Table.php');

function create_tweets_table()
{
    do_action( 'load_search_script' );
    $TweetsTable = new Tweets_Table;
    ?>

    <div id='tweets_table' class="wrap"><h2>Tweets Table </h2>
         <form method="post" >
             <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
             <?php
                $TweetsTable->prepare_items();
                $TweetsTable->search_box( 'search', 'search_id' );
                $TweetsTable->display();
             ?>
         </form>
        <button class='button action'><a href='admin.php?page=tweets_table&action=to_xml' >Save to XML</a></button>
        <br>
        <button class='button action'><a href='admin.php?page=tweets_table&action=to_csv' >Save to CSV</a></button>
    </div>
    <div id="edit_form">
            <p>Name</p>
            <input type="text" id="edit_name" name="edit_name" class="edit-field" value="">
            <p>Date</p>
            <input type="text" id="edit_date" name="edit_date" class="edit-field" value="">
            <p>Tweet</p>
            <textarea class="tweet-text" id="edit_text" name="edit_text"></textarea>
            <br>
            <button id="edit_button">Edit</button>
    </div>

<?php
}

add_action('admin_menu','register_tweets_table');
function register_tweets_table()
{
    $hook = add_menu_page('Tweets Table',
        'Tweets Table',
        'administrator',
        'tweets_table',
        'create_tweets_table',
        plugins_url('/wp_mymap.png',
            __FILE__));

    add_action( "load-$hook", 'screen_option' );
}

function screen_option()
{
    $option = 'per_page';
    $args   = [
        'label'   => 'Tweets per page:',
        'default' => 20,
        'option'  => 'tweets_per_page'
    ];

    add_screen_option( $option, $args );
}

add_filter('set-screen-option', 'tweets_set_option', 10, 3);
function tweets_set_option($status, $option, $value)
{
    return $value;
}

add_action('init', 'do_output_buffer');
function do_output_buffer()
{
    ob_start();
}

add_action('wp_ajax_edit_tweet', 'edit_tweet');
function edit_tweet() {
    global $wpdb;
    $sql = "UPDATE {$wpdb->prefix}tweets SET name='" . $_POST['edit_name'] . "', 
                                             text='" . $_POST['edit_text'] ."', 
                                             date='" . $_POST['edit_date'] . "' 
                                             WHERE id={$_POST['edit_id']}";
    $wpdb->query($sql);

    die();
}

add_action('wp_ajax_get_search_data', 'get_search_session');
function get_search_session() {
    echo $_SESSION['search'];
    die();
}

add_action('wp_ajax_drop_search_data', 'drop_search_session');
function drop_search_session() {
    unset ($_SESSION['search']);
    die();
}
