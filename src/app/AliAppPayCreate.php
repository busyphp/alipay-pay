<?php

namespace BusyPHP\alipay\pay\app;

use BusyPHP\alipay\pay\AliPayPay;
use BusyPHP\alipay\pay\AliPayPayException;
use BusyPHP\trade\interfaces\PayCreate;
use BusyPHP\trade\interfaces\PayCreateSyncReturn;
use BusyPHP\trade\model\pay\TradePayField;

/**
 * App支付请求下单
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2018 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2018-06-06 下午5:07 AliAppPayCreate.php $
 * @see https://docs.open.alipay.com/204/105465/
 */
class AliAppPayCreate extends AliPayPay implements PayCreate
{
    protected $bizContent = [];
    
    
    /**
     * 获取配置名称
     * @return string
     */
    protected function getConfigKey()
    {
        return 'app';
    }
    
    
    /**
     * 设置交易信息
     * @param TradePayField $tradeInfo
     */
    public function setTradeInfo(TradePayField $tradeInfo)
    {
        $this->bizContent['out_trade_no'] = $tradeInfo->payTradeNo;
        
        $this->bizContent['total_amount'] = $tradeInfo->price;
        
        $this->bizContent['subject'] = $tradeInfo->title;
    }
    
    
    /**
     * 设置附加数据会原样返回
     * @param string $attach
     */
    public function setAttach(string $attach)
    {
        $this->bizContent['passback_params'] = rawurlencode($attach);
    }
    
    
    /**
     * 设置异步回调地址
     * @param string $notifyUrl
     */
    public function setNotifyUrl(string $notifyUrl)
    {
        $this->params['notify_url'] = trim($notifyUrl);
    }
    
    
    /**
     * 设置同步回调地址
     * @param string $returnUrl
     * @deprecated APP支付无意义
     */
    public function setReturnUrl(string $returnUrl)
    {
    }
    
    
    /**
     * 设置商品展示地址
     * @param string $showUrl
     * @deprecated APP支付无意义
     */
    public function setShowUrl(string $showUrl)
    {
    }
    
    
    /**
     * 执行下单
     * @return string
     * @throws AliPayPayException
     */
    public function create()
    {
        $this->params['app_id']              = $this->appId;
        $this->params['method']              = 'alipay.trade.app.pay';
        $this->params['charset']             = 'UTF-8';
        $this->params['sign_type']           = $this->isRsa2 ? 'RSA2' : 'RSA';
        $this->params['timestamp']           = date('Y-m-d H:i:s');
        $this->params['version']             = "1.0";
        $this->bizContent['timeout_express'] = "15d";
        $this->bizContent['product_code']    = "QUICK_MSECURITY_PAY";
        $this->params['biz_content']         = json_encode($this->bizContent, JSON_UNESCAPED_UNICODE);
        $this->params['sign']                = self::rsaSign(self::createSignTemp($this->params, 'sign'), $this->rsaPrivatePath, 'PRIVATE', $this->isRsa2);
        
        return http_build_query($this->params);
    }
    
    
    /**
     * 解析同步返回结果
     * @return PayCreateSyncReturn
     */
    public function syncReturn() : PayCreateSyncReturn
    {
        return new PayCreateSyncReturn();
    }
}