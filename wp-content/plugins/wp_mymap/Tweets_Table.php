<?php

class Tweets_Table extends WP_List_Table
{
    public function prepare_items($search ='')
    {
        $per_page = $this->get_items_per_page('tweets_per_page', 5);

        $this -> _column_headers = array(
            $this -> get_columns(),
            $this -> get_hidden_columns(),
            $this -> get_sortable_columns()
        );

        $data = $this -> get_tweets_data($search);
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

    function column_cb($item)
    {
        return '<input type="checkbox" name="bulk-delete[]" value="'.$item['id'].'" />';
    }

    function column_name($item)
    {
        return $item['name'].' '.$this -> row_actions(array(
                'edit'   => '<a href="?page='.$_REQUEST['page'].'&action=edit&id='.$item['id'].'">Edit</a>',
                'delete' => '<a href="?page='.$_REQUEST['page'].'&action=delete&id='.$item['id'].'">Delete</a>',
            ));
    }

    function get_bulk_actions()
    {
        return $actions = ['bulk-delete' => 'Delete'];
    }

    public function process_bulk_action()
    {
        if (isset($_POST['edit_text'])){
            global $wpdb;
            $sql = "UPDATE {$wpdb->prefix}tweets SET name='" . $_POST['edit_name'] . "', text='" . $_POST['edit_text'] ."', date='" . $_POST['edit_date'] . "' WHERE id={$_GET['id']}";
            $wpdb->query($sql);
            wp_redirect( esc_url( add_query_arg() ) );
        }

        if ( 'delete' === $this->current_action() ) {
            $this->delete_tweet ($_GET['id']);
            wp_redirect( esc_url( add_query_arg() ) );
        }

        if ( 'edit' === $this->current_action() ) {
            global $wpdb;
            $sql = "SELECT * FROM {$wpdb->prefix}tweets WHERE id={$_GET['id']}";
            $result = $wpdb->get_results( $sql, 'ARRAY_A' );
            $this->view_edit_form($result);
        }

        if ( 'to_xls' === $this->current_action() ) {
            $this->tweets_to_xls();
        }

        if ( 'to_csv' === $this->current_action() ) {
            $this->tweets_to_csv();
        }

        if ( 'bulk-delete' === $this->current_action() ) {
            $delete_ids = esc_sql($_POST['bulk-delete']);
            foreach ($delete_ids as $delete_id){
                $this->delete_tweet ($delete_id);
            }
            wp_redirect( esc_url( add_query_arg() ) );
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

        $search = urldecode($search);
        $sql = "SELECT * FROM {$wpdb->prefix}tweets";

        if(!empty($search)){
            $sql .= " WHERE name LIKE '%{$search}%' OR text LIKE '%{$search}%' OR date LIKE '%{$search}%' OR tag LIKE '%{$search}%' ";
        }

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }

        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $result;
    }

    public function tweets_to_xls()
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}tweets";
        $results = $wpdb->get_results($sql, 'ARRAY_A');

        ob_clean();

        $tableHeader="<table>
                            <tr>
                                <td>ID</td>
                                <td>Name</td>
                                <td>Tweet</td>
                                <td>Date</td>
                                <td>#Tag</td>
                            </tr>";

        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=Tweets.xls');
        echo "\xEF\xBB\xBF";
        echo $tableHeader;
        foreach ($results as $result){

            echo "<tr>
                      <td>{$result['id']}</td>
                      <td>{$result['name']}</td>
                      <td>{$result['text']}</td>
                      <td>{$result['date']}</td>
                      <td>{$result['tag']}</td>
                  </tr>";
        }
        echo "</table>";
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

    public function view_edit_form ($result)
    {
        echo '<div id="edit_form">
                        <form name="edit" method="get" action="save_data">
                            <p>Name</p>
                            <input type="text" name="edit_name" class="edit-field" value="' . $result[0]['name'] .'">
                            <p>Date</p>
                            <input type="text" name="edit_date" class="edit-field" value="' . $result[0]['date'] .'">
                            <p>Tweet</p>
                            <textarea class="tweet-text" name="edit_text">' . $result[0]['text'] . '</textarea>
                            <br>
                            <input type="submit" value="OK">
                        </form> 
                  </div>';
    }
}