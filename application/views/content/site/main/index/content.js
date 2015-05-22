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
    return {id: $(this).data ('id'), lat: $(this).data ('lat'), lng: $(this).data ('lng')};
  });

  var lastLatlng = latlngs[latlngs.length - 1];

  var markers = [];
  var polyline = null;

  function circlePath (r) {
    return 'M 0 0 m -' + r + ', 0 '+
           'a ' + r + ',' + r + ' 0 1,0 ' + (r * 2) + ',0 ' +
           'a ' + r + ',' + r + ' 0 1,0 -' + (r * 2) + ',0';
  }
  function setPosition (id, lat, lng) {
    $.ajax ({
      url: $('#set_position_url').val (),
      data: { id: id, lat: lat, lng: lng },
      async: true, cache: false, dataType: 'json', type: 'POST',
      beforeSend: function () {}
    })
    .done (function (result) {
      if (!result.status)
        location.reload ();
    })
    .fail (function (result) { ajaxError (result); })
    .complete (function (result) { });
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

    markers = latlngs.map (function (i, t) {
      var marker = new google.maps.Marker ({
          map: _map,
          draggable: true,
          position: new google.maps.LatLng (t.lat, t.lng),
          icon: i == latlngs.length - 1 ? 'resource/image/gps.png' : {
            path: circlePath (10),
            strokeColor: 'rgba(249, 39, 114, 1)',
            strokeWeight: 1,
            fillColor: 'rgba(249, 39, 114, .8)',
            fillOpacity: 0.5
          }
        });

        google.maps.event.addListener (marker, 'dragend', function (e) {
          // setPosition (e.position);
          setPosition (t.id, e.latLng.lat (), e.latLng.lng ());

          polyline.setPath (markers.map (function (i, t) {
            return t.position;
          }));
        });

        return marker;
    });

    polyline = new google.maps.Polyline ({
        map: _map,
        path: markers.map (function (i, t) { return t.position; }),
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