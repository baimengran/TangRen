<?php
//配置文件
use think\Env;

return [
	'default_return_type'	=> 'json',
    'wx_AppID'     => Env::get('wx_AppID'),
    'wx_AppSecret' => Env::get('wx_AppSecret'),
    'wx_LoginUrl'  => Env::get('wx_LoginUrl'),
    'app_id'=>Env::get('app_id'),
    'mch_id'=>Env::get('mch_id'),
    'pay_key'=>Env::get('pay_key'),
];

