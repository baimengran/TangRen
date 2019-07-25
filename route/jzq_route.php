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
Route::rule('index/turns/index','index/Turns/index');
//获取交易区域接口
Route::rule('index/report/index','index/Report/index');

//酒店首页接口
Route::rule('index/hotel/first','index/Hotel/first');
//酒店详情接口
Route::rule('index/hotel/index','index/Hotel/index');
//用户评论酒店评论接口
Route::rule('index/hotel/comment','index/Hotel/comment');

//叫车首页接口
Route::rule('index/taxi/index','index/Taxi/index');
//叫车详情接口
Route::rule('index/taxi/details','index/Taxi/details');
//用户评论叫车评论接口
Route::rule('index/taxi/comment','index/Taxi/comment');

//小程序首页美食推荐接口
Route::rule('index/dining/homeelect','index/Dining/homeelect');
//搜索接口(废弃)
Route::get('index/dining/search','index/Dining/search');

//美食首页接口
Route::rule('index/dining/index','index/Dining/index');
//美食详情接口
Route::rule('index/dining/details','index/Dining/details');
//用户评论美食评论接口
Route::rule('index/dining/comment','index/Dining/comment');
//获取评论接口
Route::post('index/dining/selectcomm','index/Dining/selectcomm');
////用户评论美食评论接口
//Route::get('index/dining/viewcomm','index/Dining/viewcomm');


//个人中心首页接口
Route::get('index/personal/index','index/Personal/index');
//意见反馈接口
Route::post('index/personal/idea','index/Personal/idea');


//个人中心积分商城接口
Route::get('index/personal/fraction_goods','index/Personal/fraction_goods');
//个人中心积分兑换商品接口
Route::post('index/personal/buy','index/Personal/buy');
//个人中心积分兑换商品支付页接口
Route::get('index/personal/integral_shop','index/Personal/integral_shop');
//个人中心积分兑换商品查看订单接口
Route::post('index/personal/order','index/Personal/order');
//个人中心手机号修改接口
Route::post('index/personal/update_account','index/Personal/update_account');
//个人中心默认地址修改接口
Route::post('index/personal/update_defa','index/Personal/update_defa');
//个人中心地址添加接口
Route::post('index/personal/address','index/Personal/address');
//个人中心地址查看接口
Route::get('index/personal/address_select','index/Personal/address_select');
//个人中心编辑地址接口
Route::post('index/personal/address_edit','index/Personal/address_edit');
//个人中心删除地址接口
Route::post('index/personal/address_del','index/Personal/address_del');
//个人中心积分兑换接口
Route::get('index/personal/integral','index/Personal/integral');
//用户签到接口
Route::post('index/personal/sign','index/Personal/sign');
//用户分享接口
Route::post('index/personal/share','index/Personal/share');
//领取积分任务接口
Route::post('index/personal/integral_task','index/Personal/integral_task');
//查看个人任务完成接口
Route::post('index/personal/select_task','index/Personal/select_task');

//查看关于我们接口
Route::get('index/personal/about','index/Personal/about');

//购买积分接口
Route::post('index/pay/shop','index/Pay/shop');
//回调方法接口
Route::post('index/pay/paywx','index/Pay/paywx');

////接收上传图片接口
//Route::post('index/report/images','index/Report/images');
//发布信息接口
//Route::post('index/report/message','index/Report/message');

/**
 * 后台模块
 */

//轮播图列表
Route::rule('admin/turns/index','index/Turns/index');
//添加轮播图
Route::rule('admin/turns/add_turns','index/Turns/add_turns');
//编辑轮播图
Route::get('admin/turns/edit_turns','index/Turns/edit_turns');
//删除轮播图
Route::rule('admin/turns/del','index/Turns/del');

//酒店列表
Route::rule('admin/hotel/index','index/Hotel/index');
//添加酒店
Route::post('admin/hotel/add','index/Hotel/add');
//编辑酒店
Route::post('admin/hotel/edit','index/Hotel/edit');
//删除酒店
Route::post('admin/hotel/del_hotel','index/Hotel/del_hotel');


//叫车列表
Route::rule('admin/taxi/index','index/Taxi/index');
//添加叫车
Route::rule('admin/taxi/add_taxi','index/Taxi/add_taxi');
//编辑叫车
Route::rule('admin/taxi/edit_taxi','index/Taxi/edit_taxi');
//删除叫车
Route::rule('admin/taxi/del_taxi','index/Taxi/del_taxi');

//美食列表
Route::rule('admin/taxi/index','index/Taxi/index');
//添加叫车
Route::rule('admin/taxi/add','index/Taxi/add');
//编辑叫车
Route::rule('admin/taxi/add','index/Taxi/add');
//删除叫车
Route::rule('admin/taxi/del','index/Taxi/del');

