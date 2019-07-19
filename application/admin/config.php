<?php

return [
    //模板参数替换
    //线上加   /public
    'view_replace_str' => array(
        '__PUBLIC__' => $_SERVER['SERVER_NAME'].'/public',
        '__CSS__' => '/public/static/admin/css',
        '__JS__' => '/public/static/admin/js',
        '__IMG__' => '/public/static/admin/images',
    ),
];
