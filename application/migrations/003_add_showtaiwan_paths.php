<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

class Migration_Add_showtaiwan_paths extends CI_Migration {
  public function up () {
    $this->db->query (
      "CREATE TABLE `showtaiwan_paths` (
        `id` int(11) NOT NULL AUTO_INCREMENT,

        `lat` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Lat',
        `lng` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Lng',
        `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '住址',
        `target` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'target',
        `distance` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'distance',
        `time_at` datetime NOT NULL DEFAULT '" . date ('Y-m-d H:i:s') . "' COMMENT '時間',

        `updated_at` datetime NOT NULL DEFAULT '" . date ('Y-m-d H:i:s') . "' COMMENT '註冊時間',
        `created_at` datetime NOT NULL DEFAULT '" . date ('Y-m-d H:i:s') . "' COMMENT '更新時間',
        PRIMARY KEY (`id`),
        UNIQUE KEY `lat_lng_unique` (`lat`, `lng`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
    );
  }
  public function down () {
    $this->db->query (
      "DROP TABLE `showtaiwan_paths`;"
    );
  }
}