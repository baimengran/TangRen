<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/7/26
 * Time: 11:14
 */

namespace app\api\controller;



use app\admin\model\MemberModel;
use app\admin\model\OrderModel;
use think\Db;
use think\Loader;
use think\Request;

class Pay
{
    protected $body;            //商品描述
    protected $out_trade_no;    //订单号
    protected $total_fee;       //支付金额
    protected $notify_url;      //异步回调地址
    protected $open_id;         //用户标识
    protected $pay_key;

    //小程序支付接口
    public function miniProgramPay($token,$order_desc,$order_id,$total_fee)
    {
        $userModel = new MemberModel();

        //用户token
        //订单号
        $order_code = date('YmdHis',time()).$this->createNoncestr(10).'_'.$order_id;

        if (!$order_code) {
            return jsone('订单不能为空',400);
        }
        if (!$token) {
            return jsone('请登录后操作',401);
        }

        $this->body = $order_desc;
        $this->out_trade_no = $order_code;
        $this->total_fee = $total_fee*100;
        $this->notify_url = url('/api/Pay/notify');
        $this->open_id = Db::name('user_login')->where('user_token',$token)->value('openid');
        $this->pay_key = config('pay_key');
        try {
           $order =  OrderModel::where('id', $order_id)->save(['no' => $order_code]);
        }catch(\Exception $e){
            return jsone('订单生成错误',400);
        }
        return json(['code' => 1, 'data' => $this->unifiedorder(), 'out_trade_no' => $this->out_trade_no]);
    }

    public function notify()
    {
        $xml = file_get_contents('php://input');
        $notify_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if ($notify_data['result_code'] == 'SUCCESS' && $notify_data['return_code'] == 'SUCCESS') {
            //TODO::回调没做，
            $orderInfo = (new \app\admin\model\OrderModel())->where(['no' => $notify_data['out_trade_no']])->find();
            if ($orderInfo['refund_status'] != 0) {
                return false;
            }
            $orderInfo->save([
                'payment_method' => 'WeChat',
                'payment_no'=>$notify_data['transaction_id'],
                'pay_time' => time(),
            ]);
        }
    }

    //统一下单接口
    private function unifiedorder()
    {
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $parameters = array(
            'appid' => config('app_id'),                       //小程序ID
            'mch_id' => config('mch_id'),                  //商户号
            'nonce_str' => $this->createNoncestr(),         //随机字符串
            'body' => $this->body,                          //商品描述
            'out_trade_no' => $this->out_trade_no,           //商户订单号
            'total_fee' => $this->total_fee,                //总金额 单位 分
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],  //终端IP
            'notify_url' => $this->notify_url,              //通知地址  确保外网能正常访问
            'openid' => $this->open_id,                     //用户id
            'trade_type' => 'JSAPI',                         //交易类型
        );
        //统一下单签名
        $parameters['sign'] = $this->getSign($parameters);
        $xmlData = $this->arrayToXml($parameters);

        $return = $this->xmlToArray($this->postXmlCurl($xmlData, $url, 60));
        return $return;
    }

    //作用：产生随机字符串，不长于32位
    private function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //作用：生成签名
    private function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->pay_key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }

    //作用：格式化参数，签名过程需要使用
    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff =null;
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }

            $buff .= $k . "=" . $v . "&";
//            print_r($buff);die;
        }

        $reqPar='';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    private function arrayToXml($arr)
    {
        $xml = "<root>";
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . $this->arrayToXml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</root>";
        return $xml;
    }

    //xml转换成数组
    private function xmlToArray($xml)
    {


        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    private static function postXmlCurl($xml, $url, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);


        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        set_time_limit(0);


        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return jsone('curl出错，错误码:'.$error,400);
        }
    }
}