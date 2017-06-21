jQuery(document).ready(function($){
    $('a.edit_link').on('click', function(event){

        var id = $(this).closest('tr').find('span.tweet-id').html();
        var name = $(this).closest('tr').find('span.tweet-name').html();
        var text = $(this).closest('tr').find('span.tweet-text').html();
        var date = $(this).closest('tr').find('span.tweet-date').html();

        var currentTweet = this;

        $( "#edit_form" ).fadeIn(300);

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

            $.post( ajaxurl, data, function() {
                name = $("#edit_name").val();
                date = $("#edit_date").val();
                text = $("#edit_text").val();

                $(currentTweet).closest('tr').find('.tweet-name').html(name);
                $(currentTweet).closest('tr').find('.tweet-text').html(text);
                $(currentTweet).closest('tr').find('.tweet-date').html(date);

                $( "#edit_form" ).fadeOut(300);

            });

        });
        event.preventDefault();
    });
});