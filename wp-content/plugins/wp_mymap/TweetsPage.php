<?php
class Tweets_Page
{
    // class instance
    static $instance;

    // customer WP_List_Table object
    public $tweets_obj;

    // class constructor
    public function __construct()
    {
        add_filter('set-screen-option', [__CLASS__, 'set_screen'], 5, 3);
        add_action('admin_menu', [$this, 'plugin_menu']);
    }

    public static function set_screen($status, $option, $value)
    {
        return $value;
    }

    public function plugin_menu()
    {
        $hook = add_menu_page(
            'Tweets Table',
            'Tweets Table',
            'manage_options',
            'tweets_table',
            [$this, 'tweets_page']
        );

        add_action("load-$hook", [$this, 'screen_option']);
    }

    public function screen_option()
    {
        $option = 'per_page';
        $args = [
            'label' => 'Tweets per page',
            'default' => 5,
            'option' => 'tweets_per_page'
        ];

        add_screen_option($option, $args);

        $this->tweets_obj = new Tweets_Table();
    }

    public function tweets_page()
    {
        ?>
        <div class="wrap">
            <h2>Tweets Table</h2>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <?php
                                $this->tweets_obj->prepare_items();
                                $this->tweets_obj->display();
                                ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
        <?php
    }

    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}