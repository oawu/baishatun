<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

class Migration_Edit_showtaiwan_paths extends CI_Migration {
  public function up () {
    $this->db->query (
      "ALTER TABLE `showtaiwan_paths` ADD `lat2` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Lat2' AFTER `lng`;"
    );
    $this->db->query (
      "ALTER TABLE `showtaiwan_paths` ADD `lng2` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Lng2' AFTER `Lat2`;"
    );
  }
  public function down () {
    $this->db->query (
      "ALTER TABLE `showtaiwan_paths` DROP `lat2`"
    );
    $this->db->query (
      "ALTER TABLE `showtaiwan_paths` DROP `lng2`"
    );
  }
}