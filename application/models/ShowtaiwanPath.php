<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

class ShowtaiwanPath extends OaModel {

  static $table_name = 'showtaiwan_paths';

  static $has_one = array (
  );

  static $has_many = array (
  );

  static $belongs_to = array (
  );

  static $validates_uniqueness_of = array (
    array (array ('lat', 'lng'), 'message' => 'columns(lat, lng) Repeat!')
  );

  public function __construct ($attributes = array (), $guard_attributes = true, $instantiating_via_find = false, $new_record = true) {
    parent::__construct ($attributes, $guard_attributes, $instantiating_via_find, $new_record);


  }
}