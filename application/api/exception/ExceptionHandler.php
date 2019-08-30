<?php

namespace app\api\exception;


use Exception;
use think\exception\ErrorException;
use think\exception\Handle;
use think\exception\HttpException;
use think\Log;
use think\Request;
use think\Response;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/17
 * Time: 16:27
 */
class ExceptionHandler extends Handle
{
    //http状态码
    private $code;
    //状态
    private $errmsg;
    //自定义状态码
    private $errcode;
    //状态说明
    private $ertips;
    private $msg;

    public function render(Exception $e)
    {
        if (config('app_debug')) {
            return parent::render($e);
        }
        if ($e instanceof HttpException) {
            $this->code = $e->getStatusCode();
            $this->errmsg = 'error';
            $this->errcode = 1;
            $this->ertips = '请求异常';

            if ($e->getStatusCode() == 404) {
//
                header("Location:" . url('admin/error/index'));
                return $this->send();
                die;
            }
            if ($e->getStatusCode() == 500) {
                header("Location:" . url('admin/error/error'));
                return $this->send();
                die;
            }
        } else if ($e instanceof BaseException) {

            $this->code = $e->code;
            $this->errmsg = $e->errmsg;
            $this->errcode = $e->errcode;
            $this->ertips = $e->ertips;
            $this->msg = $e->msg;
            return $this->send();
        }
        if (config('app_debug')) {
            return parent::render($e);
        } else {
            //系统异常
            $this->code = 500;
            $this->errmsg = 'error';
            $this->errcode = 1;//未知错误，不想让客户端知道
            $this->ertips = '服务器内部错误';
            $this->recordErrorLog($e);
            return $this->send();
        }


    }

    /**
     * 输出异常
     * @return \think\response\Json
     */
    private function send()
    {
        $request = Request::instance();

        $header = [];
        switch ($this->code) {
            case 401:
                $header = ['WWW-Authenticate' => 'Authorization'];
                break;
            case 405:
                $header = ['Allow' => 'GET'];
                break;
        }

        $result = [
            'code' => $this->code,
            'errcode' => $this->errcode,
            'errmsg' => $this->errmsg,
            'ertips' => $this->ertips,
            'msg' => $this->msg,
            'request_url' => $request->url()
        ];

        return json($result, $this->code, $header);
    }

    /**
     * 记录日志
     * @param \Exception $e
     */
    private function recordErrorLog(\Exception $e)
    {
        Log::init([
            'type' => 'File',
            'path' => LOG_PATH,
            'leve' => ['error']
        ]);

        Log::record($e->getMessage(), 'error');
    }
}