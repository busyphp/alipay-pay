<?php

namespace BusyPHP\alipay\pay;

use BusyPHP\helper\net\Http;
use BusyPHP\trade\interfaces\PayRefundNotifyResult;
use BusyPHP\trade\interfaces\PayRefundQuery;
use BusyPHP\trade\model\refund\TradeRefundField;
use Exception;

/**
 * 统一收单交易退款查询
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/7/8 下午8:34 下午 AliPayRefund.php $
 * @see https://opendocs.alipay.com/apis/api_1/alipay.trade.fastpay.refund.query
 */
abstract class AliPayRefundQuery extends AliPayPay implements PayRefundQuery
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
        $this->params['method']    = 'alipay.trade.fastpay.refund.query';
        $this->params['format']    = 'JSON';
        $this->params['charset']   = 'utf-8';
        $this->params['sign_type'] = $this->isRsa2 ? 'RSA2' : 'RSA';
        $this->params['timestamp'] = date('Y-m-d H:i:s');
        $this->params['version']   = '1.0';
    }
    
    
    /**
     * 设置平台退款订单数据对象
     * @param TradeRefundField $info
     */
    public function setTradeRefundInfo(TradeRefundField $info)
    {
        $this->bizContent['out_trade_no']   = $info->payTradeNo;
        $this->bizContent['trade_no']       = $info->payApiTradeNo;
        $this->bizContent['out_request_no'] = $info->refundNo;
    }
    
    
    /**
     * 执行查询
     * @return PayRefundNotifyResult
     * @throws AliPayPayException
     */
    public function query() : PayRefundNotifyResult
    {
        $this->params['biz_content'] = json_encode($this->bizContent, JSON_UNESCAPED_UNICODE);
        $this->params['sign']        = self::rsaSign(self::createSignTemp($this->params, 'sign'), $this->rsaPrivatePath, '', $this->isRsa2);
        
        try {
            $result = Http::get('https://openapi.alipay.com/gateway.do', $this->params);
        } catch (Exception $e) {
            throw new AliPayPayException("HTTP请求失败: {$e->getMessage()} [{$e->getCode()}]");
        }
        
        
        $result = json_decode($result, true);
        $result = $result['alipay_trade_fastpay_refund_query_response'];
        if ($result['code'] != '10000') {
            throw new AliPayPayException($result['msg'], $result['code'], $result['sub_msg'], $result['sub_code']);
        }
        
        $res = new PayRefundNotifyResult();
        $res->setStatus(true);
        $res->setPayApiTradeNo($result['trade_no']);
        $res->setPayTradeNo($result['out_trade_no']);
        $res->setRefundNo($result['out_request_no']);
        
        // 退入账户
        $refundRoyaltys = $result['refund_royaltys'] ?? false;
        if ($refundRoyaltys) {
            $res->setRefundAccount($refundRoyaltys['trans_in_email'] ?? '');
        }
        
        return $res;
    }
}