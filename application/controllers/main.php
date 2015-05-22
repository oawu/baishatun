<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

class Main extends Site_controller {

  public function __construct () {
    parent::__construct ();
  }
  public function api ($id = 0) {
    header('Content-type: text/html');
    header('Access-Control-Allow-Origin: http://comdan66.github.io');

    $paths = Path::find ('all', array ('conditions' => array ('id > ?', $id)));

    $result = array_map (function ($t) {
      return array ('id' => $t->id, 'lat' => $t->lat, 'lng' => $t->lng);
    }, $paths);

    return $this->output_json ($result);
  }
  public function crontab () {
    $this->load->library ('phpQuery');
    $url = 'http://www.baishatun.com.tw/gps/';

    if (!($get_html_str = str_replace ('&amp;', '&', urldecode (file_get_contents ($url)))))
      return ErrorLog::create (array (
          'message' => '取不到原始碼！'
        ));

    preg_match_all ('/addMarker\s*\((?P<lat>.*)\s*,\s*(?P<lng>.*)\);/', $get_html_str, $result);

    if (!($result['lat'] && $result['lng']&& $result['lat'][0] && $result['lng'][0]))
      return ErrorLog::create (array (
          'message' => '網頁內容有誤！'
        ));

    if (!verifyCreateOrm ($path = Path::create (array (
                'lat' => $result['lat'][0],
                'lng' => $result['lng'][0],
              ))))
      return ErrorLog::create (array (
          'message' => '重複！'
        ));
    // else
    //   $this->output->delete_all_cache ();
  }
  public function index () {

    foreach ($paths = Path::all () as $path) {
      $this->add_hidden (array ('class' => 'latlng', 'data-id' => $path->id, 'data-lat' => $path->lat, 'data-lng' => $path->lng));
    }

    $this->add_js ('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&language=zh-TW', false)
         ->add_hidden (array ('id' => 'set_position_url', 'value' => base_url ($this->get_class (), 'set_position')))
         ->load_view (null);
  }
  public function set_position () {
    if (!$this->is_ajax ())
      return show_error ("It's not Ajax request!<br/>Please confirm your program again.");
    
    $id = $this->input_post ('id');
    $lat = $this->input_post ('lat');
    $lng = $this->input_post ('lng');

    if (!($id && $lat && $lng && ($path = Path::find_by_id ($id))))
      return $this->output_json (array ('status' => false));

    $path->lat = $lat;
    $path->lng = $lng;
    $path->save ();

    return $this->output_json (array ('status' => true));
  }
}
