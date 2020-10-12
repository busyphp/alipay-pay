<?php

namespace BusyPHP\alipay\pay\pc;

use BusyPHP\alipay\pay\AliPayPay;
use BusyPHP\alipay\pay\AliPayPayException;
use BusyPHP\trade\interfaces\PayNotify;
use BusyPHP\trade\interfaces\PayNotifyResult;
use think\Response;
use Throwable;

/**
 * 电脑网站支付结果异步通知
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/7/8 下午8:40 下午 AliPcPayNotify.php $
 * @see https://docs.open.alipay.com/270/105902/
 */
class AliPcPayNotify extends AliPayPay implements PayNotify
{
    protected $params     = [];
    
    private   $payTradeNo = '';
    
    
    /**
     * 获取配置名称
     * @return string
     */
    protected function getConfigKey()
    {
        return 'pc';
    }
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->params     = $_POST;
        $this->payTradeNo = $this->params['out_trade_no'];
    }
    
    
    /**
     * 执行校验
     * @return PayNotifyResult
     * @throws AliPayPayException
     */
    public function notify()
    {
        // 交易状态不合法
        if ($this->params['trade_status'] != 'TRADE_SUCCESS' && $this->params['trade_status'] != 'TRADE_FINISHED') {
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
        $res->setPayTradeNo($this->payTradeNo);
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
        return $this->payTradeNo;
    }
}