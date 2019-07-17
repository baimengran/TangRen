<?php
// Route::请求方式('模块名/路由名/方法名','，模块名/控制名/方法名');

/**
 * 前台模块
 */

//前台用户登录模块
Route::post('index/userlogin/login','index/Userlogin/login');
//用户信息录入接口(废弃)
//Route::post('index/userlogin/create','index/Userlogin/create');

//显示前台轮播图
Route::post('index/turns/index','index/Turns/index');
//获取交易区域接口
Route::get('index/report/index','index/Report/index');

//酒店首页接口
Route::get('index/hotel/first','index/Hotel/first');
//酒店详情接口
Route::post('index/hotel/index','index/Hotel/index');
//用户评论酒店评论接口
Route::post('index/hotel/comment','index/Hotel/comment');

//叫车首页接口
Route::get('index/taxi/index','index/Taxi/index');
//叫车详情接口
Route::post('index/taxi/details','index/Taxi/details');
//用户评论叫车评论接口
Route::post('index/taxi/comment','index/Taxi/comment');


//美食首页接口
Route::get('index/dining/index','index/Dining/index');
//美食详情接口
Route::post('index/dining/details','index/Dining/details');
//用户评论美食评论接口
Route::post('index/dining/comment','index/Dining/comment');


//个人中心首页接口
Route::get('index/personal/index','index/Personal/index');

//个人中心积分商城接口
Route::get('index/personal/fraction_goods','index/Personal/fraction_goods');
//个人中心积分兑换商品接口
Route::post('index/personal/buy','index/Personal/buy');
//个人中心地址管理接口
Route::post('index/personal/address','index/Personal/address');
//个人中心积分兑换接口
Route::get('index/personal/integral','index/Personal/integral');
//用户签到接口
Route::post('index/personal/sign','index/Personal/sign');
//用户分享接口
Route::post('index/personal/share','index/Personal/share');
//积分任务接口
Route::post('index/personal/integral_task','index/Personal/integral_task');
//查看个人任务完成接口
Route::post('index/personal/select_task','index/Personal/select_task');

////接收上传图片接口
//Route::post('index/report/images','index/Report/images');

//发布信息接口
//Route::post('index/report/message','index/Report/message');
