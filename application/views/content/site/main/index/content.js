/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

$(function () {

  var $loading = $('<div />').attr ('id', 'loading')
                             .append ($('<div />'))
                             .appendTo ($('body'));
  var $map = $('#map');

  function initialize () {
    var option = {
        zoom: 16,
        scaleControl: true,
        navigationControl: true,
        disableDoubleClickZoom: true,
        mapTypeControl: true,
        zoomControl: true,
        scrollwheel: true,
        streetViewControl: true,
        center: new google.maps.LatLng (23.568038757736595, 120.30465692281723),
      };

    var _map = new google.maps.Map ($map.get (0), option);

    $loading.fadeOut (function () {
      $(this).hide (function () {
        $(this).remove ();
      });
    });
  }
  google.maps.event.addDomListener (window, 'load', initialize);

});