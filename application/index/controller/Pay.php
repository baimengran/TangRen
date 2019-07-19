<?php
namespace app\index\controller;

use app\index\library\WxPayLibrary;
use app\index\model\PayModel;
use think\Controller;
use think\Db;
use think\Request;

class Pay extends Controller
{
    public function shop(\think\Request $request)
    {
        //获取参数
        $post = $request->post();

        //判断参数是否正确
        $rule =   [
            'user_token'    => 'require',
            'integral_id'   => 'require|number',
            'integral_id'   => 'require|number',
            'out_trade_no'  => 'require',
            'form_id'       => 'require',
        ];

        $message  = [
            'user_token.require'       => '用户token值不能为空',
            'integral_id.require'      => '积分ID不能为空',
            'integral_id.number'       => '积分ID格式错误',
            'out_trade_no.require'     => '积分ID格式错误',
            'form_id.require'          => 'form_id不能为空',
        ];

        //实例化验证器
        $result=$this->validate($post,$rule,$message);

        //判断有无错误
        if(true !== $result){
            $date = ['errcode'=> 1,'errMsg'=>'error','ertips'=>$result];
            // 验证失败 输出错误信息
            return json_encode($date,320);
        }

        //查询购买数量是不是0
        $PayModel= new PayModel();
        $integral_number = $PayModel->integral_list($post['integral_id']);

        //判断积分是否为0
        if(!$integral_number['integral_number']){
            return $err = json_encode(['errCode'=>'1','msg'=>'error','ertips'=>'购买失败','retData'=>$integral_number['integral_id']],320);
        }
        //生成订单号
        $post['order_number'] = $this->order_number($integral_number['integral_id']);

        //换算成微信金额
        $rmb = $integral_number['rmb_number'] * 100;
        //实例化统一下单支付接口
        $pay = new WxPayLibrary();

        //定义回调方法
        $callback = "https://{$_SERVER['HTTP_HOST']}/index/pay/paywx";

        $_POST = [
            'token' => $post['user_token'],
            'body' => $integral_number['integral_number'],
            //微信支付的金额
            'rmb' => $rmb,
            'out_trade_no' => $post['order_number']
        ];

        //调用统一下单方法  $callback回调地址
        $wxunif = $pay->wxUnifiedApy($callback);

        if($wxunif['msg'] == 'error'){
            return returnData('error',$wxunif['data']);
        }




    }


    /**
     * 微信支付
     * @return
     */
    public function weixinPay(Request $request)
    {
        $orderNum = $request->orderNum;
        $money = $request->money;

        if (!$orderNum){
            return $this->ajaxMsgError('订单编号不能为空');
        }

        if (!$money){
            return $this->ajaxMsgError('支付金额不能为空');
        }

        //填写配置参数
        $options = array(

            'appid'     => 'wx17b397eb17e3e0eb',                            //填写微信分配的公众账号ID
            'mch_id'    => '1516480081',                                    //填写微信支付分配的商户号
            'notify_url'=> 'http://www.longmaochina.com/api/pay/wxNotify',  //填写微信支付结果回调地址
            'key'       => '70b95c7ba5625cd83cd6ee531943144f'               //填写微信商户支付密钥
        );
        //初始化配置
        $wechatAppPay = new weixinAppPay($options);
        $total_fee   =  floatval($money);
        //下单必要的参数
        $params['body'] = 'AppWxPay';           //商品描述
        $params['out_trade_no'] = $orderNum;    //自定义的订单号
        $params['total_fee'] = $total_fee;      //订单金额 只能为整数 单位为分
        $params['nonce_str'] = uniqid();        //随机数
        $params['spbill_create_ip'] =$this->getIp();
        $params['trade_type'] = 'APP';          //交易类型 JSAPI | NATIVE | APP | WAP
        //统一下单
        $result = $wechatAppPay->unifiedOrder($params);

        //创建APP端预支付参数
        $data = $wechatAppPay->getAppPayParams($result);

        return $this->ajaxOk($data);
    }

    /**
     * 微信支付结果回调地址
     */
    public function wxNotify()
    {
        $xmlData = file_get_contents('php://input');

        //解析方法如下:
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $data_json = json_encode($data);

        //将微信返回的数据写进
        file_put_contents ( storage_path().'/logs/appWxPay.txt', date ( "Y-m-d H:i:s" ) . "  " . $data_json . "\r\n", FILE_APPEND );
        //对于支付结果通知的内容一定要做签名验证,防止数据泄漏导致出现“假通知”,造成资金损失。签名验证方法如下:
        ksort($data);
        $buff = '';
        foreach ($data as $k => $v){
            if($k != 'sign'){
                $buff .= $k . '=' . $v . '&';
            }
        }
        $stringSignTemp = $buff . 'key=70b95c7ba5625cd83cd6ee531943144f';//key为证书密钥
        $sign = strtoupper(md5($stringSignTemp));
        //判断算出的签名和通知信息的签名是否一致
        if($sign == $data['sign']){

            //签名验证成功后，判断返回微信返回的
            if ($data['result_code'] == 'SUCCESS') {

                $info['pay_channel']  = 'app微信支付';               //支付方式
                $info['out_trade_no'] = $data['out_trade_no'];     //获取订单号
                $info['trade_no']     = $data['transaction_id'];   //交易号
                $info['gmt_payment']  = date('Y-m-d H:i:s',strtotime($data['time_end'])); //订单支付时间

                //对比金额
                $order_amount_total = Order::where('order_no',$data['out_trade_no'])->value('order_amount_total');
                //if ($data['cash_fee'] == $order_amount_total*100){

                //支付成功逻辑处理.............
                $res = $this->payDone($info);
                //处理完成之后，告诉微信成功结果！
                if($res){
                    echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                    exit();
                }else{
                    file_put_contents ( storage_path().'/logs/appWxPay.txt', date ( "Y-m-d H:i:s" ) . "  " . '错误信息：状态更改失败' . "\r\n", FILE_APPEND );
                }
                //金额不符，返回实付金额
//                }else{
//
//                    file_put_contents ( storage_path().'/logs/appWxPay.txt', date ( "Y-m-d H:i:s" ) . "  " . '错误信息：金额不符，实返金额为'.$data['cash_fee'] . "\r\n", FILE_APPEND );
//                }

            }
            //支付失败，输出错误信息
            else{

                file_put_contents ( storage_path().'/logs/appWxPay.txt', date ( "Y-m-d H:i:s" ) . "  " . '错误信息：' .$data['return_msg']. "\r\n", FILE_APPEND );
            }
            //验签失败
        }else{
            file_put_contents ( storage_path().'/logs/appWxPay.txt', date ( "Y-m-d H:i:s" ) . "  " . '错误信息：签名验证失败' . "\r\n", FILE_APPEND );
        }

    }


    private function order_number($id)
    {
        //获取用户ID
        return time().mt_rand(100000,999999).$id;
    }

}