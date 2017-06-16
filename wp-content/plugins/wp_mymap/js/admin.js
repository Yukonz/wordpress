/**
 * Created by dev70 on 14.06.17.
 */
jQuery(document).ready(function($){
    $('#settings_form').submit(function(){
        $('#delay_message').empty();
        $('#show_message').empty();

        if (!($.isNumeric($('#delay_time').val()))){
            $("<div id='message' class='error fade'><p>Введите число!</p></div>").appendTo('#delay_message');
            var delay_time_error = true;
        }

        if (!($.isNumeric($('#show_time').val()))){
            $("<div id='message' class='error fade'><p>Введите число!</p></div>").appendTo('#show_message');
            var show_time_error = true;
        }

        if (delay_time_error || show_time_error) return false;
    });
});