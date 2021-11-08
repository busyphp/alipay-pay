<?php

namespace BusyPHP\alipay\pay;

use BusyPHP\helper\RsaHelper;
use BusyPHP\trade\interfaces\PayCreate;
use BusyPHP\trade\interfaces\PayCreateSyncReturn;
use BusyPHP\trade\model\pay\TradePayInfo;
use Throwable;

/**
 * App支付请求下单
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午10:50 AliPayCreate.php $
 * @see https://docs.open.alipay.com/204/105465/
 */
abstract class AliPayCreate extends AliPayPay implements PayCreate
{
    /**
     * 是否生成不包含URL的连接
     * @var bool
     */
    protected $buildUrlIsOnlyQuery = false;
    
    /**
     * 产品代码
     * @var string
     */
    protected $productCode = '';
    
    
    /**
     * 设置交易信息
     * @param TradePayInfo $tradeInfo
     */
    public function setTradeInfo(TradePayInfo $tradeInfo)
    {
        $this->bizContent['out_trade_no'] = $tradeInfo->payTradeNo;
        $this->bizContent['total_amount'] = $tradeInfo->price;
        $this->bizContent['subject']      = $tradeInfo->title;
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
        $this->params['notify_url'] = $notifyUrl;
    }
    
    
    /**
     * 设置同步回调地址
     * @param string $returnUrl
     */
    public function setReturnUrl(string $returnUrl)
    {
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
     * @return string
     * @throws AliPayPayException
     */
    public function create()
    {
        $this->bizContent['product_code'] = $this->productCode;
    
        $this->initParams();
    
        if ($this->buildUrlIsOnlyQuery) {
            return http_build_query($this->params);
        }
    
        return "https://openapi.alipay.com/gateway.do?" . http_build_query($this->params);
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
            $temp   = self::temp($this->request->get(), 'sign,sign_type');
            $sign   = $this->request->get('sign/s', '', 'trim');
            $isRsa2 = strtoupper($this->request->get('sign_type/s', '', 'trim')) == 'RSA2';
            RsaHelper::verify($temp, $sign, $this->publicCert, true, $isRsa2);
            $res->setStatus(true);
        } catch (Throwable $e) {
            $res->setStatus(false);
            $res->setErrorMessage($e->getMessage());
        }
        
        $res->setApiTradeNo($this->request->get('trade_no/s', '', 'trim'));
        $res->setPayTradeNo($this->request->get('out_trade_no/s', '', 'trim'));
        $res->setApiPrice($this->request->get('total_amount/s', '', 'trim'));
        
        return $res;
    }
}