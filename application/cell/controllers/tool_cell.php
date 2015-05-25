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
      $last = ShowtaiwanPath::last ();

      $ids = array ();
      for ($i = 0; ($id = round (($i * (2 + ($i - 1) * 0.25)) / 2)) < $last->id; $i++)
        array_push ($ids, $last->id - $id);

      $ids = array_reverse ($ids);
      $paths = ShowtaiwanPath::find ('all', array ('select' => 'id, lat, lng, time_at', 'order' => 'FIELD(id, ' . implode (', ', $ids) . ')', 'conditions' => array ('id IN (?)', $ids)));

      if(isset ($paths[count ($paths) - 1]) && ($paths[count ($paths) - 1]->id != $last->id))
        array_push ($paths, $last);

    } else {
      $paths = ShowtaiwanPath::find ('all', array ('select' => 'id, lat, lng, time_at', 'conditions' => array ('id > ?', $id)));
    }

    $paths = array_map (function ($path) {
      return array (
            'id' => $path->id,
            'lat' => isset ($path->lat2) && ($path->lat2 != '') ? $path->lat2 : $path->lat,
            'lng' => isset ($path->lng2) && ($path->lng2 != '') ? $path->lng2 : $path->lng,
            'time' => $path->time_at->format ('Y-m-d H:i:s')
          );
    }, $paths);

    return $paths;
  }
}