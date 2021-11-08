<?php

namespace BusyPHP\alipay\pay;

use BusyPHP\helper\RsaHelper;
use BusyPHP\trade\interfaces\PayNotify;
use BusyPHP\trade\interfaces\PayNotifyResult;
use think\Response;
use Throwable;

/**
 * 支付结果异步通知
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2018 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2018-06-06 上午10:13 AliAppPayNotify.php $
 * @see https://docs.open.alipay.com/204/105301/
 */
abstract class AliPayNotify extends AliPayPay implements PayNotify
{
    private $payTradeNo;
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->params     = $this->request->post();
        $this->payTradeNo = $this->params['out_trade_no'] ?? '';
    }
    
    
    /**
     * 获取接口方法
     * @return string
     */
    protected function getMethod() : string
    {
        return '';
    }
    
    
    /**
     * 执行校验
     * @return PayNotifyResult
     * @throws AliPayPayException
     */
    public function notify() : PayNotifyResult
    {
        // 交易状态不合法
        if (($this->params['trade_status'] ?? '') != 'TRADE_SUCCESS' && ($this->params['trade_status'] ?? '') != 'TRADE_FINISHED') {
            throw new AliPayPayException("交易状态不合法");
        }
        
        // 校验签名
        try {
            $temp   = self::temp($this->params, 'sign,sign_type');
            $sign   = $this->params['sign'] ?? '';
            $isRsa2 = strtoupper($this->params['sign_type'] ?? '') === 'RSA2';
            RsaHelper::verify($temp, $sign, $this->publicCert, true, $isRsa2);
        } catch (Throwable $e) {
            throw new AliPayPayException('签名校验失败', 0, '', '', $e);
        }
        
        
        // 验证ANT结果
        $this->verifyNotify($this->params['notify_id']);
        
        $res = new PayNotifyResult();
        $res->setAttach($this->params['passback_params'] ?? '');
        $res->setPayTradeNo($this->payTradeNo);
        $res->setApiPrice(floatval($this->params['total_amount'] ?? 0));
        $res->setPayType($this->type);
        $res->setApiTradeNo($this->params['trade_no'] ?? '');
        
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
    public function getRequestParams() : array
    {
        return $this->params;
    }
    
    
    /**
     * 获取源请求参数
     * @return string
     */
    public function getRequestSourceParams() : string
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