<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

class Main extends Site_controller {

  public function __construct () {
    parent::__construct ();
  }

  private function s1 () {
    $url = 'http://showtaiwan.hinet.net/event/201505A/get_current_location.php?_=1432428335943';

    if (!($get_html_str = str_replace ('&amp;', '&', urldecode (file_get_contents ($url))))) {
      ErrorLog::create (array (
          'message' => '[showtaiwan1] 取不到原始碼！'
        ));
      return false;
    }

    $objs = json_decode ($get_html_str, true);
    if (!$objs) {
      ErrorLog::create (array (
          'message' => '[showtaiwan1] 沒有陣列！'
        ));
      return false;
    }

    foreach ($objs as $obj)
      if (!verifyCreateOrm ($path = ShowtaiwanPath::create (array (
                  'lat' => $obj['y'],
                  'lng' => $obj['x'],
                  'lat2' => $obj['y'] + ('0.000' . rand (0, 100)),
                  'lng2' => $obj['x'] + ('0.000' . rand (0, 100)),
                  'address' => $obj['addr'],
                  'target' => $obj['target'],
                  'distance' => $obj['distance'],
                  'time_at' => $obj['year'] . '-' . $obj['month'] . '-' . $obj['day'] . ' ' . $obj['hour'] . ':' . $obj['min'] . ':' . '00',
                ))))
        ErrorLog::create (array (
            'message' => '[showtaiwan1] 重複！'
          ));

    return true;
  }
  private function s2 () {
    $url = 'http://showtaiwan.hinet.net/event/201505A/links/data/get_current_location.php';

    if (!($get_html_str = str_replace ('&amp;', '&', urldecode (file_get_contents ($url))))) {
      ErrorLog::create (array (
          'message' => '[showtaiwan2] 取不到原始碼！'
        ));
      return false;
    }

    $obj = json_decode ($get_html_str, true);
    if (!$obj) {
      ErrorLog::create (array (
          'message' => '[showtaiwan2] 沒有陣列！'
        ));
      return false;
    }
    if (!verifyCreateOrm ($path = ShowtaiwanPath::create (array (
                'lat' => $obj['y'],
                'lng' => $obj['x'],
                'lat2' => $obj['y'] + ('0.000' . rand (0, 100)),
                'lng2' => $obj['x'] + ('0.000' . rand (0, 100)),
                'address' => $obj['addr'],
                'target' => $obj['target'],
                'distance' => $obj['distance'],
                'time_at' => '2015' . '-' . $obj['month'] . '-' . $obj['day'] . ' ' . $obj['hour'] . ':' . $obj['min'] . ':' . '00',
              ))))
      ErrorLog::create (array (
          'message' => '[showtaiwan2] 重複！'
        ));

    return true;
  }
  public function showtaiwan ($id = 0) {
    if (!$this->s1 ())
      if (!$this->s2 ())
        return ErrorLog::create (array (
            'message' => '[showtaiwan] 緊急錯誤！'
          ));
    clean_cell ('*');
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
                'lat2' => $result['lat'][0] + ('0.00' . rand (0, 3) . rand (0, 100)),
                'lng2' => $result['lng'][0] + ('0.00' . rand (0, 3) . rand (0, 100)),
              ))))
      return ErrorLog::create (array (
          'message' => '重複！'
        ));
  }

  public function api ($id = 0) {
    header ('Content-type: text/html');
    header ('Access-Control-Allow-Origin: http://comdan66.github.io');
    // header ('Access-Control-Allow-Origin: *');

    $count = round (Path::count () / 200);

    $paths = array_map (function ($path) {
      return array (
          'id' => $path->id,
          'lat' => isset ($path->lat2) && ($path->lat2 != '') ? $path->lat2 : $path->lat,
          'lng' => isset ($path->lng2) && ($path->lng2 != '') ? $path->lng2 : $path->lng,
          'time' => $path->created_at->format ('Y-m-d H:i:s')
        );
    }, $id == 0 ? Path::find_by_sql ("select * from paths where  id > " . $id . " AND mod(id, " . $count . ") = 0;") : Path::find ('all', array ('conditions' => array ('id > ?', $id))));

    if (!$id && ($last = Path::last ()) && ($paths[count ($paths) - 1]['id'] != $last->id))
      array_push ($paths, array (
          'id' => $last->id,
          'lat' => isset ($last->lat2) && ($last->lat2 != '') ? $last->lat2 : $last->lat,
          'lng' => isset ($last->lng2) && ($last->lng2 != '') ? $last->lng2 : $last->lng,
          'time' => $last->created_at->format ('Y-m-d H:i:s')
        ));

    return $this->output_json ($paths);
  }


  public function merge () {
    foreach (ShowtaiwanPath::all () as $path)
      TempPath::create (array (
                  'lat' => $path->lat,
                  'lng' => $path->lng,
                  'address' => $path->address,
                  'target' => $path->target,
                  'distance' => $path->distance,
                  'time_at' => $path->time_at,
                ));

    ShowtaiwanPath::query ('TRUNCATE TABLE `showtaiwan_paths`');

    foreach (Path::find ('all', array ('conditions' => array ('id < 1226'))) as $path)
      ShowtaiwanPath::create (array (
                  'lat' => $path->lat,
                  'lng' => $path->lng,
                  'address' => '',
                  'target' => '',
                  'distance' => '',
                  'time_at' => $path->created_at,
                ));

    foreach (TempPath::all () as $path)
      ShowtaiwanPath::create (array (
                  'lat' => $path->lat,
                  'lng' => $path->lng,
                  'address' => $path->address,
                  'target' => $path->target,
                  'distance' => $path->distance,
                  'time_at' => $path->time_at,
                ));
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
  public function index () {
    redirect ('http://comdan66.github.io/matsu/2015/baishatun.html');
  }
  public function index1 ($code = '', $limit = 0) {
    if (md5 ($code) !== '1c63129ae9db9c60c3e8aa94d3e00495')
      return false;
    $paths = Path::find ('all', array ('order' => 'id DESC', 'limit' => $limit, 'conditions' => array ()));

    foreach (array_reverse ($paths) as $path) {
      $this->add_hidden (array ('class' => 'latlng', 'data-id' => $path->id, 'data-lat' => isset ($path->lat2) && ($path->lat2 != '') ? $path->lat2 : $path->lat, 'data-lng' => isset ($path->lng2) && ($path->lng2 != '') ? $path->lng2 : $path->lng));
    }

    $this->add_js ('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&language=zh-TW', false)
         ->add_hidden (array ('id' => 'set_position_url', 'value' => base_url ($this->get_class (), 'set_position1')))
         ->set_method ('index')
         ->load_view (null);
  }
  public function set_position1 () {
    if (!$this->is_ajax ())
      return show_error ("It's not Ajax request!<br/>Please confirm your program again.");

    $id = $this->input_post ('id');
    $lat = $this->input_post ('lat');
    $lng = $this->input_post ('lng');

    if (!($id && $lat && $lng && ($path = Path::find_by_id ($id))))
      return $this->output_json (array ('status' => false));

    $path->lat2 = $lat;
    $path->lng2 = $lng;
    $path->save ();

    return $this->output_json (array ('status' => true));
  }
  public function index2 ($code = '', $limit = 0) {
    if (md5 ($code) !== '1c63129ae9db9c60c3e8aa94d3e00495')
      return false;
    $paths = ShowtaiwanPath::find ('all', array ('order' => 'id DESC', 'limit' => $limit, 'conditions' => array ()));

    foreach (array_reverse ($paths) as $path) {
      $this->add_hidden (array ('class' => 'latlng', 'data-id' => $path->id, 'data-lat' => isset ($path->lat2) && ($path->lat2 != '') ? $path->lat2 : $path->lat, 'data-lng' => isset ($path->lng2) && ($path->lng2 != '') ? $path->lng2 : $path->lng));
    }

    $this->add_js ('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&language=zh-TW', false)
         ->add_hidden (array ('id' => 'set_position_url', 'value' => base_url ($this->get_class (), 'set_position2')))
         ->set_method ('index')
         ->load_view (null);
  }
  public function set_position2 () {
    if (!$this->is_ajax ())
      return show_error ("It's not Ajax request!<br/>Please confirm your program again.");

    $id = $this->input_post ('id');
    $lat = $this->input_post ('lat');
    $lng = $this->input_post ('lng');

    if (!($id && $lat && $lng && ($path = ShowtaiwanPath::find_by_id ($id))))
      return $this->output_json (array ('status' => false));

    $path->lat2 = $lat;
    $path->lng2 = $lng;
    $path->save ();

    clean_cell ('*');

    return $this->output_json (array ('status' => true));
  }
  public function api2 ($id = 0) {
    header ('Content-type: text/html');
    header ('Access-Control-Allow-Origin: http://comdan66.github.io');
    // header ('Access-Control-Allow-Origin: *');

    $paths = render_cell ('tool_cell', 'api2', $id);

    return $this->output_json ($paths);
  }
  public function clean_cell () {
    clean_cell ('*');
  }
}
