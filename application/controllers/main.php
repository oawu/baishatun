<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

class Main extends Site_controller {

  public function __construct () {
    parent::__construct ();
  }

  public function crontab () {
    $this->load->library ('phpQuery');
    $url = 'http://www.baishatun.com.tw/gps/';

    if (!($get_html_str = str_replace ('&amp;', '&', urldecode (file_get_contents ($url)))))
    return ;

    preg_match_all ('/addMarker\s*\((?P<lat>.*)\s*,\s*(?P<lng>.*)\);/', $get_html_str, $result);

    if (!($result['lat'] && $result['lng']&& $result['lat'][0] && $result['lng'][0]))
    return ;

    Path::create (array (
        'lat' => $result['lat'][0],
        'lng' => $result['lng'][0],
      ));
  }
  public function index () {

    foreach ($paths = Path::all () as $path) {
      $this->add_hidden (array ('class' => 'latlng', 'data-lat' => $path->lat, 'data-lng' => $path->lng));
    }

    $this->add_js ('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&language=zh-TW', false)
         ->load_view (null);
  }
}
