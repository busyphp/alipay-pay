<?php

namespace BusyPHP\alipay\pay;

use BusyPHP\helper\net\Http;
use BusyPHP\helper\util\Transform;
use BusyPHP\trade\interfaces\PayRefund;
use BusyPHP\trade\interfaces\PayRefundResult;
use BusyPHP\trade\model\refund\TradeRefundField;
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
     * 设置平台退款订单数据对象
     * @param TradeRefundField $info
     */
    public function setTradeRefundInfo(TradeRefundField $info)
    {
        $this->bizContent['out_trade_no']   = $info->payTradeNo;
        $this->bizContent['trade_no']       = $info->payApiTradeNo;
        $this->bizContent['out_request_no'] = $info->refundNo;
        $this->bizContent['refund_amount']  = Transform::formatMoney(floatval($info->refundPrice));
        $this->bizContent['refund_reason']  = $info->remark;
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
        } catch (Throwable $e) {
            throw new AliPayPayException("HTTP请求失败: {$e->getMessage()} [{$e->getCode()}]");
        }
        
        
        $result = json_decode($result, true);
        $res    = new PayRefundResult();
        $result = $result['alipay_trade_refund_response'];
        if ($result['code'] != '10000') {
            switch (strtoupper(trim($result['sub_code'] ?? ''))) {
                case 'ACQ.SYSTEM_ERROR': // 请使用相同的参数再次调用
                case 'ACQ.SELLER_BALANCE_NOT_ENOUGH': // 商户支付宝账户充值后重新发起退款即可
                case 'ACQ.REFUND_CHARGE_ERROR': // 退收费异常, 请过一段时间后再重试发起退款
                    $res->setNeedRehandle(true);
                break;
                default:
                    throw new AliPayPayException($result['msg'], $result['code'], $result['sub_msg'], $result['sub_code']);
            }
        } else {
            $res->setRefundAccount($result['buyer_logon_id']);
        }
        
        return $res;
    }
}