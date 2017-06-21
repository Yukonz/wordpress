jQuery(document).ready(function($) {
    var data = {action: 'get_search_data'};
    $.post( ajaxurl, data, function(search) {
        $('#search_id-search-input').val(search);
    });
});
