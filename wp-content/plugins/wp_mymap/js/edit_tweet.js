jQuery(document).ready(function($){
    $('.edit_link').on('click', function(event){
        var id=$(this).attr('href');
        event.preventDefault();

        var data = {
            action: 'get_tweet',
            edit_id: id,
            dataType: "json"
        };

        $.post( ajaxurl, data, function(tweet_data) {
            var data = $.parseJSON(tweet_data);
            $("#edit_form").fadeIn(300);

            $("#edit_text").val(data.text);
            $("#edit_name").val(data.name);
            $("#edit_date").val(data.date);
        });

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
    })
});