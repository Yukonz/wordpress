var delay_time = params['delay_time'];
var show_time = params['show_time'];
var title_text = params['title_text'];
var main_text = params['main_text'];
var close_button = params['close_button'];
var esc_button = params['esc_button'];
var overlay_click = params['overlay_click'];

jQuery(document).ready(function($){
    setTimeout(function(){

        // $.ajax({
        //     url: '/wp-content/plugins/wp_mypopup/popup_template.php'
        // }).done(function(data) {
        //     $('body').append(data);
        // });
        //
        // $('<h1>' + title_text + '</h1>').appendTo('#popup');
        // $('<h4>' + main_text + '</h4>').appendTo('#popup');


        $( "<div id='overlay'><div id='popup'><h1>" + title_text + "</h1><h4>" + main_text + "</h4><button id='close_popup'>Close</button></div></div>" ).appendTo( 'body' );

        if(!close_button){
            $('#close_popup').css({'display' : 'none'});
        };

        if(esc_button){
            $(this).keydown(function(eventObject){
                if (eventObject.which == 27)
                    $('#overlay').hide();
            });
        };

        if(overlay_click){
            $('#overlay').on('click', function(){
                $('#overlay').hide();
            });
        };

        $('#close_popup').on('click', function(){
            $('#overlay').hide();
        });

        setTimeout(function(){
            $('#overlay').hide();
        }, show_time);

    }, delay_time);
});

