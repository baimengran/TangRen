<?php
namespace app\index\library;

class WxPayLibrary
{
    /**
     * 名  称 : wxUnifiedApy()
     * 创  建 : 2019/07/18
     * 功  能 : 微信统一下单支付接口
     * 变  量 : --------------------------------------
     * 输  入 : (string) `token`          => `用户token`              【必填】
     * 输  入 : (string) `body`           => `商品或支付单简要描述`   【必填】
     * 输  入 : (string) `attach`         => `商品附加数据`           【必填】
     * 输  入 : (string) `total_fee`      => `商品支付总金额`         【必填】
     * 输  入 : (string) `out_trade_no`   => `商户订单号`             【选填】
     * 输  出 : {"errNum":0,"retMsg":"提示信息","retData":{}
     * 输  出 : {"errNum":1,"retMsg":"提示信息","retData":false
     */
    public function wxUnifiedApy($callback)
    {
        $rule =   [
            'token'         => 'require',
            'body'          => 'require',
            'attach'        => 'require',
            'total_fee'     => 'require',
            'out_trade_no'  => 'require',
        ];
        $message  = [
            'token.require'      => '轮播图类型不能为空',
            'body.require'       => '轮播图类型错误',
            'attach.require'     => '轮播图类型错误',
            'total_fee.require'  => '轮播图类型错误',
            'out_trade_no.require' => '轮播图类型错误',
        ];

        //实例化验证器
        $result=$this->validate($_POST,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }
    }
}