var name = params['name'];
var text = params['text'];
var date = params['date'];
var id = params['id'];

jQuery(document).ready(function($){
    $("#edit_form").fadeIn(300);
    $("#edit_name").val(name);
    $("#edit_date").val(date);
    $("#edit_text").val(text);

    $('#edit_button').on('click', function(){
        var data = {
            action: 'edit_tweet',
            edit_name: $("#edit_name").val(),
            edit_date: $("#edit_date").val(),
            edit_text: $("#edit_text").val(),
            edit_id: id
        };

        var url = document.location.origin + '/wp-admin/admin.php?page=tweets_table&paged=' + $("#current-page-selector").val();

        $.post( ajaxurl, data, function() {
            $( "#edit_form" ).fadeOut(300);
            window.location.replace(url);
            $("#current_page_selector").val('4');
        });
    })
});