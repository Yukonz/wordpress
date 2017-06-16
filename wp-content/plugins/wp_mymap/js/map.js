var map_latitude = Number(params['map_latitude']);
var map_longitude = Number(params['map_longitude']);
var map_radius_km = Number(params['map_radius']);
var map_radius = map_radius_km * 1000;

function initMap()
{
    var map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: map_latitude, lng: map_longitude},
        zoom: 5
    });

    var circle = new google.maps.Circle({
        strokeColor: '#FF0000',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: '#FF0000',
        fillOpacity: 0.35,
        draggable: true,
        map: map,
        editable: true,
        center: {lat: map_latitude, lng: map_longitude},
        radius: map_radius_km * 1000
    });

    var marker = new google.maps.Marker({
        position: {lat: map_latitude, lng: map_longitude},
        map: map,
        draggable: false,
        animation: google.maps.Animation.DROP,
        title: 'Center'
    });

    google.maps.event.addListener(circle, 'bounds_changed', function() {
        map_radius = circle.getRadius();
        if (map_radius > 1000000) {
            map_radius = 1000000;
            circle.setRadius(map_radius);
        }
        map_radius_km = parseInt(map_radius / 1000);
        jQuery('#map_radius_value').val(map_radius_km);
        jQuery('#map_radius').val(map_radius_km);

        console.log(map_radius_km);  // Радиус круга
    });

    google.maps.event.addListener(circle, 'dragend', function() {
        map_latitude = circle.getCenter().lat();
        map_longitude = circle.getCenter().lng();

        marker.setPosition({lat: map_latitude, lng: map_longitude});

        jQuery('#map_latitude').val(map_latitude);
        jQuery('#map_longitude').val(map_longitude);

        console.log(map_latitude);  // Координаты центра круга
        console.log(map_longitude);  // Координаты центра круга
    });

    google.maps.event.addListener(circle, 'click', function (event) {
        map_latitude = event.latLng.lat();
        map_longitude = event.latLng.lng();

        circle.setCenter({lat: map_latitude, lng: map_longitude});
        marker.setPosition({lat: map_latitude, lng: map_longitude});
    });

    google.maps.event.addListener(map, 'click', function(event) {
        map_latitude = event.latLng.lat();
        map_longitude = event.latLng.lng();

        jQuery('#map_latitude').val(map_latitude);
        jQuery('#map_longitude').val(map_longitude);

        circle.setCenter({lat: map_latitude, lng: map_longitude});
        marker.setPosition({lat: map_latitude, lng: map_longitude});
    });

    jQuery(document).on('input', '#map_radius', function() {
        map_radius_km = jQuery('#map_radius').val();
        jQuery('#map_radius_value').val(map_radius);
        map_radius = Number(map_radius_km) * 1000;
        console.log(map_radius);
        circle.setRadius(map_radius);
    });

    jQuery(document).on('input', '#map_latitude', function() {
        map_latitude = Number(jQuery('#map_latitude').val());
        circle.setCenter({lat: map_latitude, lng: map_longitude});
        marker.setPosition({lat: map_latitude, lng: map_longitude});
    });

    jQuery(document).on('input', '#map_longitude', function() {
        map_longitude = Number(jQuery('#map_longitude').val());
        circle.setCenter({lat: map_latitude, lng: map_longitude});
        marker.setPosition({lat: map_latitude, lng: map_longitude});
    });
};

jQuery(function($)
{
    initMap();
});












