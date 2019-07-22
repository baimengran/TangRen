<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');

// 定义上传目录
define('UPLOAD_PATH', __DIR__ . '/public');
//pdf图片路径
define('PDF',__DIR__.'/public/uploads/pdf');
// 定义应用缓存目录
define('RUNTIME_PATH', __DIR__ . '/runtime/');
define('LOG_PATHS', __DIR__ .'/'. RUNTIME_PATH . '/log');
//获取当前路径
define('ROOT', dirname($_SERVER['SCRIPT_NAME']));
// 开启调试模式
define('APP_DEBUG', false);
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
