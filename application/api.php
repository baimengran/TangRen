<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/11
 * Time: 13:04
 */


use think\Route;

//二手商品列表
Route::get('api/used_product/index', 'api/UsedProduct/index');
//二手商品发布
Route::post('api/used_product/save', 'api/UsedProduct/save');
//二手商品详情
Route::get('api/used_product/show', 'api/UsedProduct/show');
//二手商品点赞
Route::get('api/used_product/praise', 'api/UsedProduct/praise');
//二手商品评论列表
Route::get('api/used_comment/index', 'api/UsedComment/index');
//二手商品评论发布
Route::post('api/used_comment/save', 'api/UsedComment/save');

//房屋出租列表
Route::get('api/rent_house/index', 'api/RentHouse/index');
//房屋出租发布
Route::post('api/rent_house/save', 'api/RentHouse/save');
//房屋出租详情
Route::get('api/rent_house/show', 'api/RentHouse/show');
//房屋出租点赞
Route::get('api/rent_house/praise', 'api/RentHouse/praise');
//房屋出租评论列表
Route::get('api/rent_comment/index', 'api/RentComment/index');
//房屋出租评论发布
Route::post('api/rent_comment/save', 'api/RentComment/save');

//求职招聘列表
Route::get('api/job_seek/index', 'api/JobSeek/index');
//求职招聘发布
Route::post('api/job_seek/save', 'api/JobSeek/save');
//求职招聘详情
Route::get('api/job_seek/show', 'api/JobSeek/show');
//求职招聘点赞
Route::get('api/job_seek/praise', 'api/JobSeek/praise');
//求职招聘评论列表
Route::get('api/job_comment/index', 'api/JobComment/index');
//求职招聘评论发布
Route::post('api/job_comment/save', 'api/JobComment/save');

//社区列表
Route::get('api/community/index', 'api/Community/index');
//社区发布
Route::post('api/community/save', 'api/Community/save');
//社区详情
Route::get('api/community/show', 'api/Community/show');
//社区点赞
Route::get('api/community/praise', 'api/Community/praise');
//社区收藏
Route::get('api/community/collect', 'api/Community/collect');
//社区评论列表
Route::get('api/community_comment/index', 'api/CommunityComment/index');
//社区评论发布
Route::post('api/community_comment/save', 'api/CommunityComment/save');
//上传图片
Route::post('api/upload_image/upload', 'api/UploadImage/upload');

//获取区域
Route::get('api/region_cate/index', 'api/RegionCate/index');
//获取行业
Route::get('api/profession_cate/index', 'api/ProfessionCate/index');
//获取话题
Route::get('api/topic_cate/index','api/TopicCate/index');
//获取置顶积分
Route::get('api/sticky/index','api/Sticky/index');

//我的收藏
Route::get('api/my/collect', 'api/my/collect');
//发现
Route::get('api/coupon/index', 'api/coupon/index');