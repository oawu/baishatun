<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

class Tool_cell extends Cell_Controller {

  /* render_cell ('tool_cell', 'api2', $id); */
  public function _cache_api2 ($id) {
    return array ('time' => 60 * 10, 'key' => $id);
  }
  public function api2 ($id) {
    if ($id == 0) {
      $all_count = 150;
      $now_count = 95;
      $pre_count = $all_count - $now_count;

      $now_point = 300;
      $now = ShowtaiwanPath::find ('all', array ('limit' => $now_point, 'order' => 'id DESC', 'conditions' => array ()));
      $now = $now[count ($now) - 1];

      $count = round ($now_point / $now_count);
      $nows = array_reverse (ShowtaiwanPath::find ('all', array ('order' => 'id DESC', 'conditions' => array ('id > ? AND mod(id, ' . $count . ') = 0', $now->id))));

      $pre_point = ShowtaiwanPath::count () - 300;
      $count = round ($pre_point / $pre_count);
      $pres = ShowtaiwanPath::find ('all', array ('order' => 'id ASC', 'conditions' => array ('id < ? AND mod(id, ' . $count . ') = 0', $now->id)));

      $paths = array_merge ($pres, $nows);

    } else {
      $paths = ShowtaiwanPath::find ('all', array ('conditions' => array ('id > ?', $id)));
    }

    $paths = array_map (function ($path) {

    return array (
          'id' => $path->id,
          'lat' => isset ($path->lat2) && ($path->lat2 != '') ? $path->lat2 : $path->lat,
          'lng' => isset ($path->lng2) && ($path->lng2 != '') ? $path->lng2 : $path->lng,
          'time' => $path->time_at->format ('Y-m-d H:i:s')
        );
    }, $paths);

    if (!$id && ($last = ShowtaiwanPath::last ()) && ($paths[count ($paths) - 1]['id'] != $last->id))
      array_push ($paths, array (
          'id' => $last->id,
          'lat' => isset ($last->lat2) && ($last->lat2 != '') ? $last->lat2 : $last->lat,
          'lng' => isset ($last->lng2) && ($last->lng2 != '') ? $last->lng2 : $last->lng,
          'time' => $last->time_at->format ('Y-m-d H:i:s')
        ));
    return $paths;
  }
}