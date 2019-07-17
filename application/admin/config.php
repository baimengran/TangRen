<?php

return [
    //模板参数替换
    'view_replace_str' => array(
        '__PUBLIC__' => $_SERVER['SERVER_NAME'],
        '__CSS__' => '/static/admin/css',
        '__JS__' => '/static/admin/js',
        '__IMG__' => '/static/admin/images',
    ),
];
