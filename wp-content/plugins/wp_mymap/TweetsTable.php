<?php
class Tweets_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct( [
            'singular' => __( 'Tweet', 'wp-mymap' ), //singular name of the listed records
            'plural'   => __( 'Tweets', 'wp-mymap' ), //plural name of the listed records
            'ajax'     => false //should this table support ajax?
        ] );

    }

    function get_columns()
    {
        $columns = [
            'cb'      => '<input type="checkbox" />',
            'id'    => __( 'ID', 'wp-mymap' ),
            'name'    => __( 'Name', 'wp-mymap' ),
            'text' => __( 'Tweet', 'wp-mymap' ),
            'date'    => __( 'Date', 'wp-mymap' ),
            'tag'    => __( 'Subject', 'wp-mymap' )
        ];

        return $columns;
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'name' => array( 'name', false ),
            'date' => array( 'date', true ),
            'id' => array( 'id', false )
        );

        return $sortable_columns;
    }

    public static function get_tweets_from_db( $per_page = 5, $page_number = 1 )
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}tweets";

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }
        $sql .= ' LIMIT ' . $per_page;
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $result;
    }

    public static function delete_tweet( $id )
    {
        global $wpdb;
        $wpdb->delete(
            "{$wpdb->prefix}tweets",
            [ 'id' => $id ],
            [ '%d' ]
        );
    }

    public static function tweets_count()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}tweets";

        return $wpdb->get_var($sql);
    }

    public function no_items()
    {
        _e( 'No tweets avaliable.', 'wp-mymap' );
    }

    public function column_name( $item )
    {
        $delete_nonce = wp_create_nonce( 'sp_delete_tweet' );
        $title = '<strong>' . $item['name'] . '</strong>';
        $actions = [
            'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
        ];

        return $title . $this->row_actions( $actions );
    }

    public function column_default( $item, $column_name )
    {
        switch ( $column_name ) {
            case 'id':
            case 'date':
            case 'tag':
            case 'text':
            case 'name':

            return $item[ $column_name ];
        }
    }

    function column_cb( $item )
    {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }

    public function get_bulk_actions()
    {
        $actions = [
            'bulk-delete' => 'Delete'
        ];

        return $actions;
    }

    public function prepare_items() {
        $this->_column_headers = $this->get_column_info();

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( 'tweets_per_page', 8 );
        $current_page = $this->get_pagenum();
        $total_items  = self::tweets_count();

        $this->set_pagination_args( [
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ] );

        $this->items = self::get_tweets_from_db( $per_page, $current_page );
    }

    public function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {

            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'sp_delete_tweet' ) ) {
                die( 'Error' );
            }
            else {
                self::delete_tweet( absint( $_GET[$id] ) );

                var_dump(absint( $_GET['id'] ));
                die();

                 wp_redirect( esc_url( add_query_arg() ) );

                exit;
            }
        }

        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
            || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {

            $delete_ids = esc_sql( $_POST['bulk-delete'] );

            // loop over the array of record IDs and delete them
            foreach ( $delete_ids as $id ) {
                self::delete_tweet( $id );
            }

            wp_redirect( esc_url( add_query_arg() ) );

            exit;
        }
    }
}