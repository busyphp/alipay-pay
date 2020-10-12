<?php

namespace BusyPHP\alipay\pay\h5;

use BusyPHP\alipay\pay\AliPayPay;
use BusyPHP\alipay\pay\AliPayPayException;
use BusyPHP\trade\interfaces\PayNotify;
use BusyPHP\trade\interfaces\PayNotifyResult;
use think\Response;
use Throwable;

/**
 * 手机网站支付结果异步通知
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2018 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2018-06-06 上午10:13 AliWapPayNotify.php $
 * @see https://docs.open.alipay.com/203/105286/
 */
class AliWapPayNotify extends AliPayPay implements PayNotify
{
    protected $params  = [];
    
    private   $tradeNo = '';
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->params  = $_POST;
        $this->tradeNo = $this->params['out_trade_no'];
    }
    
    
    /**
     * 获取配置名称
     * @return string
     */
    protected function getConfigKey()
    {
        return 'h5';
    }
    
    
    /**
     * 执行校验
     * @return PayNotifyResult
     * @throws AliPayPayException
     */
    public function notify()
    {
        // 交易状态不合法
        if ($this->params['trade_status'] != 'TRADE_SUCCESS') {
            throw new AliPayPayException("交易状态不合法");
        }
        
        // 校验签名
        try {
            self::checkRsaSign(self::createSignTemp($this->params, 'sign,sign_type'), $this->params['sign'], $this->rsaPublicPath, '', strtoupper($this->params['sign_type']) == 'RSA2');
        } catch (Throwable $e) {
            throw new AliPayPayException('签名校验失败');
        }
        
        $res = new PayNotifyResult();
        $res->setAttach($this->params['passback_params']);
        $res->setPayTradeNo($this->tradeNo);
        $res->setApiPrice($this->params['total_amount']);
        $res->setPayType($this->type);
        $res->setApiTradeNo($this->params['trade_no']);
        
        return $res;
    }
    
    
    /**
     * 失败通知
     * @param Throwable $e
     * @return Response
     */
    public function onError(Throwable $e) : Response
    {
        return Response::create($e->getMessage())->contentType('text/plain');
    }
    
    
    /**
     * 成功通知
     * @param bool $payStatus true 支付成功，false 之前已支付，属于重复性的操作
     * @return Response
     */
    public function onSuccess(bool $payStatus) : Response
    {
        return Response::create('success')->contentType('text/plain');
    }
    
    
    /**
     * 获取请求参数
     * @return array
     */
    public function getRequestParams()
    {
        return $this->params;
    }
    
    
    /**
     * 获取请求参数字符
     * @return string
     */
    public function getRequestString()
    {
        return http_build_query($this->params);
    }
    
    
    /**
     * 获取商户订单号
     * @return string
     */
    public function getPayTradeNo()
    {
        return $this->tradeNo;
    }
}