/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

$(function () {

  var $loading = $('<div />').attr ('id', 'loading')
                             .append ($('<div />'))
                             .appendTo ($('body'));
  var $map = $('#map');

  var latlngs = $('.latlng').map (function () {
    return {lat: $(this).data ('lat'), lng: $(this).data ('lng')};
  });

  var lastLatlng = latlngs[latlngs.length - 1];

  var merks = [];

  function circlePath (r) {
    return 'M 0 0 m -' + r + ', 0 '+
           'a ' + r + ',' + r + ' 0 1,0 ' + (r * 2) + ',0 ' +
           'a ' + r + ',' + r + ' 0 1,0 -' + (r * 2) + ',0';
  }

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
        center: new google.maps.LatLng (lastLatlng.lat, lastLatlng.lng),
      };

    var _map = new google.maps.Map ($map.get (0), option);

    merks = latlngs.map (function (i, t) {
      return new google.maps.Marker ({
          map: _map,
          draggable: false,
          position: new google.maps.LatLng (t.lat, t.lng),
          icon: i == latlngs.length - 1 ? 'resource/image/gps.png' : {
            path: circlePath (10),
            strokeColor: 'rgba(249, 39, 114, 1)',
            strokeWeight: 1,
            fillColor: 'rgba(249, 39, 114, .8)',
            fillOpacity: 0.5
          }
        });
    });

    new google.maps.Polyline ({
        map: _map,
        path: merks.map (function (i, t) { return t.position; }),
        strokeColor: 'rgba(249, 39, 114, .15)',
        strokeWeight: 10
      });

    $loading.fadeOut (function () {
      $(this).hide (function () {
        $(this).remove ();
      });
    });
  }

  google.maps.event.addDomListener (window, 'load', initialize);
});