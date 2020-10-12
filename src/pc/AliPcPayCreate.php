<?php

namespace BusyPHP\alipay\pay\pc;

use BusyPHP\alipay\pay\AliPayPay;
use BusyPHP\alipay\pay\AliPayPayException;
use BusyPHP\trade\interfaces\PayCreate;
use BusyPHP\trade\interfaces\PayCreateSyncReturn;
use BusyPHP\trade\model\pay\TradePayField;
use Throwable;

/**
 * PC场景下单并支付
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/7/8 下午8:38 下午 AliPcPayCreate.php $
 * @see https://docs.open.alipay.com/270/105899/
 */
class AliPcPayCreate extends AliPayPay implements PayCreate
{
    protected $bizContent = [];
    
    
    /**
     * 获取配置名称
     * @return string
     */
    protected function getConfigKey()
    {
        return 'pc';
    }
    
    
    /**
     * 设置交易信息
     * @param TradePayField $tradeInfo
     */
    public function setTradeInfo(TradePayField $tradeInfo)
    {
        // 商家单号
        $this->bizContent['out_trade_no'] = $tradeInfo->payTradeNo;
        
        // 支付金额
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
     */
    public function setReturnUrl(string $returnUrl)
    {
        $this->params['return_url'] = trim($returnUrl);
    }
    
    
    /**
     * 设置商品展示地址
     * @param string $showUrl
     */
    public function setShowUrl(string $showUrl)
    {
    }
    
    
    /**
     * 执行下单
     * @return string 跳转的链接
     * @throws AliPayPayException
     */
    public function create()
    {
        $this->bizContent['product_code'] = 'FAST_INSTANT_TRADE_PAY';
        
        
        $this->params['app_id']      = $this->appId;
        $this->params['method']      = 'alipay.trade.page.pay';
        $this->params['format']      = 'JSON';
        $this->params['charset']     = 'utf-8';
        $this->params['sign_type']   = $this->isRsa2 ? 'RSA2' : 'RSA';
        $this->params['timestamp']   = date('Y-m-d H:i:s');
        $this->params['version']     = '1.0';
        $this->params['biz_content'] = json_encode($this->bizContent, JSON_UNESCAPED_UNICODE);
        $this->params['sign']        = self::rsaSign(self::createSignTemp($this->params, 'sign'), $this->rsaPrivatePath, '', $this->isRsa2);
        
        return 'https://openapi.alipay.com/gateway.do?' . http_build_query($this->params);
    }
    
    
    /**
     * 获取公钥路径
     * @return string
     */
    public function getRsaPublicPath()
    {
        return $this->rsaPublicPath;
    }
    
    
    /**
     * 解析同步返回结果
     * @return PayCreateSyncReturn
     */
    public function syncReturn() : PayCreateSyncReturn
    {
        $res = new PayCreateSyncReturn();
        
        // 校验签名
        try {
            self::checkRsaSign(self::createSignTemp($_GET, 'sign,sign_type'), $_GET['sign'], $this->getRsaPublicPath(), '', strtoupper($_GET['sign_type']) == 'RSA2');
            $res->setStatus(true);
        } catch (Throwable $e) {
            $res->setStatus(false);
            $res->setMessage('签名校验错误');
        }
        
        $res->setApiTradeNo($_GET['trade_no']);
        $res->setPayTradeNo($_GET['out_trade_no']);
        $res->setApiPrice($_GET['total_amount']);
        
        return $res;
    }
}