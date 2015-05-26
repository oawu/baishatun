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
                  'lat2' => $obj['y'] + (rand (-19999, 19999) * 0.00000001),
                  'lng2' => $obj['x'] + (rand (-19999, 19999) * 0.00000001),
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
                'lat2' => $obj['y'] + (rand (-19999, 19999) * 0.00000001),
                'lng2' => $obj['x'] + (rand (-19999, 19999) * 0.00000001),
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
                'lat2' => $result['lat'][0] + (rand (-19999, 19999) * 0.00000001),
                'lng2' => $result['lng'][0] + (rand (-19999, 19999) * 0.00000001),
              ))))
      return ErrorLog::create (array (
          'message' => '重複！'
        ));
  }

  public function api ($id = 0) {
    // header ('Content-type: text/html');
    // header ('Access-Control-Allow-Origin: http://comdan66.github.io');
    // header ('Access-Control-Allow-Origin: *');

    // $count = round (Path::count () / 200);

    // $paths = array_map (function ($path) {
    //   return array (
    //       'id' => $path->id,
    //       'lat' => isset ($path->lat2) && ($path->lat2 != '') ? $path->lat2 : $path->lat,
    //       'lng' => isset ($path->lng2) && ($path->lng2 != '') ? $path->lng2 : $path->lng,
    //       'time' => $path->created_at->format ('Y-m-d H:i:s')
    //     );
    // }, $id == 0 ? Path::find_by_sql ("select * from paths where  id > " . $id . " AND mod(id, " . $count . ") = 0;") : Path::find ('all', array ('conditions' => array ('id > ?', $id))));

    // if (!$id && ($last = Path::last ()) && ($paths[count ($paths) - 1]['id'] != $last->id))
    //   array_push ($paths, array (
    //       'id' => $last->id,
    //       'lat' => isset ($last->lat2) && ($last->lat2 != '') ? $last->lat2 : $last->lat,
    //       'lng' => isset ($last->lng2) && ($last->lng2 != '') ? $last->lng2 : $last->lng,
    //       'time' => $last->created_at->format ('Y-m-d H:i:s')
    //     ));

    // return $this->output_json ($paths);

    echo "Hi " . $this->input->ip_address () . ', Nice to meat you! What are you looking for? : )';
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
    if (md5 ($code) !== '9056749f0dde456780a336ea05640d0a')
      redirect ('http://comdan66.github.io/matsu/2015/baishatun.html');
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
    if (md5 ($code) !== '9056749f0dde456780a336ea05640d0a')
      redirect ('http://comdan66.github.io/matsu/2015/baishatun.html');
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

  public function x () {
    // http://175.99.76.212/Vehicle/GMapCarMazu

    $url = 'http://175.99.76.212/Vehicle/GMapCarMazu';

    $options = array (
      CURLOPT_URL => $url, CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => '{ajaxvalues: "BSTTV,353358015273399"}',//http_build_query (array ('ajaxvalues' => 'BSTTV,353358015273399')),
      CURLOPT_TIMEOUT => 120, CURLOPT_HEADER => false, CURLOPT_MAXREDIRS => 10,
      CURLOPT_AUTOREFERER => true, CURLOPT_CONNECTTIMEOUT => 30, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_USERAGENT => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36",
      CURLOPT_HTTPHEADER => array("X-Requested-With: XMLHttpRequest", "Content-Type:application/x-www-form-urlencoded")
    );

    $ch = curl_init ($url);
    curl_setopt_array ($ch, $options);
    $data = curl_exec ($ch);
    curl_close ($ch);

    echo '<meta http-equiv="Content-type" content="text/html; charset=utf-8" /><pre>';
    var_dump ($data);
    exit ();
  }
}
