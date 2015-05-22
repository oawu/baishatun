<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 OA Wu Design
 */

class Migration_Add_error_logs extends CI_Migration {
  public function up () {
    $this->db->query (
      "CREATE TABLE `error_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `message` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '訊息',
        `updated_at` datetime NOT NULL DEFAULT '" . date ('Y-m-d H:i:s') . "' COMMENT '註冊時間',
        `created_at` datetime NOT NULL DEFAULT '" . date ('Y-m-d H:i:s') . "' COMMENT '更新時間',
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
    );
  }
  public function down () {
    $this->db->query (
      "DROP TABLE `error_logs`;"
    );
  }
}