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
    // header('Access-Control-Allow-Origin: *');

    $count = round (Path::count () / 100);

    $paths = array_map (function ($path) {
      return array (
          'id' => $path->id,
          'lat' => $path->lat,
          'lng' => $path->lng,
          'time' => $path->created_at->format ('Y-m-d H:i:s')
        );
    }, $id == 0 ? Path::find_by_sql ("select * from paths where  id > " . $id . " AND mod(id, " . $count . ") = 0;") : Path::find ('all', array ('conditions' => array ('id > ?', $id))));

    if (!$id && ($last = Path::last ()) && ($paths[count ($paths) - 1]['id'] != $last->id))
      array_push ($paths, array (
          'id' => $last->id,
          'lat' => $last->lat,
          'lng' => $last->lng,
          'time' => $last->created_at->format ('Y-m-d H:i:s')
        ));

    return $this->output_json ($paths);
  }
  public function query () {
    $this->load->helper ('file');

    return ErrorLog::create (array (
        'message' => write_file (FCPATH . 'application/logs/query.log', '', FOPEN_READ_WRITE_CREATE_DESTRUCTIVE) ? '清除 query.log 成功！' : '清除 query.log 失敗！'
      ));
  }
  public function clean_output () {
    $this->output->delete_all_cache ();
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
  }
  public function index ($code = '') {
    if (md5 ($code) !== '1c63129ae9db9c60c3e8aa94d3e00495')
      return false;
    $paths = Path::find ('all', array ('order' => 'id DESC', 'limit' => 200, 'conditions' => array ()));

    foreach (array_reverse ($paths) as $path) {
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
