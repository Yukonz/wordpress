<?php

class Tweets_Table extends WP_List_Table
{
    public $search;

    public function prepare_items()
    {
        $per_page = $this->get_items_per_page('tweets_per_page', 5);

        $this -> _column_headers = array(
            $this -> get_columns(),
            $this -> get_hidden_columns(),
            $this -> get_sortable_columns()
        );

        if (isset($_REQUEST['s'])) {
            $_SESSION['search'] = $_REQUEST['s'];
        }

        $this->search = $_SESSION['search'];
        $data = $this -> get_tweets_data($this->search);
        $this->process_bulk_action();
        $this -> set_pagination_args( array(
            'total_items' => count($data),
            'per_page'    => $per_page
        ));

        $data = array_slice(
            $data,
            (($this -> get_pagenum() - 1) * $per_page),
            $per_page
        );

        $this -> items = $data;
    }

    public function get_columns()
    {
        return array(
            'cb'        => '<input type="checkbox" />',
            'id'        => 'ID',
            'name'      => 'Name',
            'text'      => 'Tweet',
            'date'      => 'Date',
            'tag'       => '#Tag',
        );
    }

    public function get_sortable_columns()
    {
        return array(
            'id' => array('id', false),
            'name' => array('name', true),
            'date' => array('date', false),
        );
    }

    public function get_hidden_columns()
    {
        return array();
    }

    public function column_cb($item)
    {
        return '<input type="checkbox" name="bulk-delete[]" value="'.$item['id'].'" />';
    }

    public function column_name($item)
    {
        $current_page = $this->get_pagenum();
        return $item['name'].' '.$this -> row_actions(array(
                'edit'   => '<a class="edit_link" href="?page='.$_REQUEST['page'].'&paged=' . $current_page . '&action=edit&id='.$item['id'].'">Edit</a>',
                'delete' => '<a href="?page='.$_REQUEST['page']. '&paged=' . $current_page . '&action=delete&id='.$item['id'].'">Delete</a>',
            ));
    }

    public function get_bulk_actions()
    {
        return $actions = ['bulk-delete' => 'Delete'];
    }

    public function process_bulk_action()
    {
        if ( 'delete' === $this->current_action() ) {
            $this->delete_tweet ($_GET['id']);
            $current_url = '/wp-admin/admin.php?page=tweets_table&paged=' . $this -> get_pagenum();
            wp_redirect($current_url);
        }

        if ( 'edit' === $this->current_action() ) {
            global $wpdb;
            $sql = "SELECT * FROM {$wpdb->prefix}tweets WHERE id={$_GET['id']}";
            $result = $wpdb->get_results( $sql, 'ARRAY_A' );

            wp_enqueue_script('jquery');
            wp_enqueue_script( 'edit_js', plugin_dir_url( __FILE__ ) . '/js/edit.js' , 'jquery');

            $edit_fields = array(
                'id'=>$_GET['id'],
                'name'=>$result[0]['name'],
                'text'=>$result[0]['text'],
                'date'=>$result[0]['date']
            );
            wp_localize_script( 'edit_js', 'params', $edit_fields );
        }

        if ( 'to_xml' === $this->current_action() ) {
            $this->tweets_to_xml();
        }

        if ( 'to_csv' === $this->current_action() ) {
            $this->tweets_to_csv();
        }

        if ( 'bulk-delete' === $this->current_action() ) {
            $delete_ids = esc_sql($_POST['bulk-delete']);
            foreach ($delete_ids as $delete_id){
                $this->delete_tweet ($delete_id);
            }
            $current_url = '/wp-admin/admin.php?page=tweets_table&paged=' . $this -> get_pagenum();
            wp_redirect($current_url);
        }
    }

    public function delete_tweet($id)
    {
        global $wpdb;
        $wpdb->delete(
            "{$wpdb->prefix}tweets",
            [ 'id' => $id ],
            [ '%d' ]
        );
    }

    public function column_default($item, $column_name )
    {
        switch($column_name)
        {
            case 'id':
            case 'name':
            case 'text':
            case 'date':
            case 'tag':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    private function get_tweets_data($search)
    {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}tweets";

        if(!empty($this->search)){
            $search = urldecode($this->search);
            $sql .= " WHERE name LIKE '%{$search}%' OR text LIKE '%{$search}%' OR date LIKE '%{$search}%' OR tag LIKE '%{$search}%' ";
        }

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }

        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $result;
    }

    public function tweets_to_xml()
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}tweets";
        $results = $wpdb->get_results($sql, 'ARRAY_A');

        foreach ($results as $result){
            $res_arr[] = array_flip($result);
        }

        $xml = new SimpleXMLElement('<root/>');
        array_walk_recursive($res_arr, array($xml, 'addChild'));
        ob_clean();
        header('Content-type: text/xml; charset=UTF-8');
        header('Content-Disposition: attachment; filename=Tweets.xml');
        echo $xml->saveXML();
        die();
    }

    public function tweets_to_csv()
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}tweets";
        $results = $wpdb->get_results($sql, 'ARRAY_A');

        ob_clean();

        $csvHeader='"ID";"Name";"Tweet";"Date";"Tag";' ;

        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=Tweets.csv');
        echo "\xEF\xBB\xBF";
        echo $csvHeader;
        echo "\n ";
        foreach ($results as $result){
            echo '"' . $result['id'] . '"' . ';' .
                    '"' . $result['name'] . '"' . ';' .
                    '"' . $result['text'] . '"' . ';' .
                    '"' . $result['date'] . '"' . ';' .
                    '"' . $result['tag'] . '"' . ';';
            echo "\n";
        }

        die();
    }
}