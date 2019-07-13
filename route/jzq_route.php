<?php
// Route::请求方式('模块名/路由名/方法名','，模块名/控制名/方法名');

/**
 * 前台模块
 */

//酒店首页接口
//Route::get('index/hotel/first','index/Hotel/first');
//酒店详情接口
Route::get('index/hotel/index','index/Hotel/index');
//用户评论酒店评论接口
Route::get('index/hotel/comment','index/Hotel/comment');

