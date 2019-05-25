<?php 
    return[
         'DB_CONNECT'=> [
             'host' => 'localhost',
             'user' => 'root',
             'pass' => 'admin',
             'dbname' => 'photoalbun',
             'port' => '3306'
         ],
         'DB_CHARSET' => 'utf8',
         //相册层级最大值
         'LEVEL_MAX' => 5,
         //允许的图片格式
         'ALLOW_EXT' =>['jpg','jpeg','png'],
         //缩略图大小
         'THUMB_SIZE' => 260,
        ];
?>