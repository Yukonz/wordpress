jQuery(document).ready(function($) {
    $('#toplevel_page_tweets_table').on('click', function(){
        var data = {action: 'drop_search_data'};
        $.post( ajaxurl, data, function() {});
    });
});
