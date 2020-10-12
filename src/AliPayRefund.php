<?php

namespace BusyPHP\alipay\pay;

use BusyPHP\helper\net\Http;
use BusyPHP\helper\util\Transform;
use BusyPHP\trade\interfaces\PayRefund;
use BusyPHP\trade\interfaces\PayRefundResult;
use BusyPHP\trade\model\pay\TradePayField;
use Throwable;

/**
 * 统一收单交易退款接口
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/7/8 下午8:34 下午 AliPayRefund.php $
 * @see https://docs.open.alipay.com/api_1/alipay.trade.refund
 */
abstract class AliPayRefund extends AliPayPay implements PayRefund
{
    protected $bizContent = [];
    
    
    /**
     * AliPayRefund constructor.
     * @throws AliPayPayException
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->params['app_id']    = $this->appId;
        $this->params['method']    = 'alipay.trade.refund';
        $this->params['format']    = 'JSON';
        $this->params['charset']   = 'utf-8';
        $this->params['sign_type'] = $this->isRsa2 ? 'RSA2' : 'RSA';
        $this->params['timestamp'] = date('Y-m-d H:i:s');
        $this->params['version']   = '1.0';
    }
    
    
    /**
     * 设置平台交易订单数据对象
     * @param TradePayField $info
     */
    public function setTradeInfo(TradePayField $info)
    {
        $this->bizContent['out_trade_no'] = $info->payTradeNo;
        $this->bizContent['trade_no']     = $info->apiTradeNo;
    }
    
    
    /**
     * 设置退款单号
     * @param string $refundNo
     */
    public function setRefundTradeNo($refundNo)
    {
        $this->bizContent['out_request_no'] = trim($refundNo);
    }
    
    
    /**
     * 设置要申请退款的金额
     * @param float $refundPrice 精确到小数点2位
     */
    public function setRefundPrice($refundPrice)
    {
        $this->bizContent['refund_amount'] = Transform::formatMoney(floatval($refundPrice));
    }
    
    
    /**
     * 设置退款原因
     * @param string $reason
     */
    public function setRefundReason($reason)
    {
        $this->bizContent['refund_reason'] = trim($reason);
    }
    
    
    /**
     * 设置退款结果通知url
     * @param string $notifyUrl
     * @deprecated 无意义
     */
    public function setNotifyUrl($notifyUrl)
    {
    }
    
    
    /**
     * 执行退款
     * @return PayRefundResult
     * @throws AliPayPayException
     */
    public function refund() : PayRefundResult
    {
        $this->params['biz_content'] = json_encode($this->bizContent, JSON_UNESCAPED_UNICODE);
        $this->params['sign']        = self::rsaSign(self::createSignTemp($this->params, 'sign'), $this->rsaPrivatePath, '', $this->isRsa2);
        
        try {
            $result = Http::get('https://openapi.alipay.com/gateway.do', $this->params);
            $result = json_decode($result, true);
            $result = $result['alipay_trade_refund_response'];
            if ($result['code'] != '10000') {
                throw new AliPayPayException($result['msg'], $result['code'], $result['sub_msg'], $result['sub_code']);
            }
            
            $res = new PayRefundResult();
            $res->setPayTradeNo($result['out_trade_no']);
            $res->setApiPayTradeNo($result['trade_no']);
            $res->setRefundTradeNo($this->bizContent['out_request_no']);
            $res->setRefundPrice($result['refund_fee']);
            $res->setApiRefundTradeNo('');
            
            return $res;
        } catch (Throwable $e) {
            throw new AliPayPayException("HTTP请求失败: {$e->getMessage()} [{$e->getCode()}]");
        }
    }
}