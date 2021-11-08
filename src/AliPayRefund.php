<?php

namespace BusyPHP\alipay\pay;

use BusyPHP\helper\TransHelper;
use BusyPHP\trade\interfaces\PayRefund;
use BusyPHP\trade\interfaces\PayRefundResult;
use BusyPHP\trade\model\refund\TradeRefundInfo;

/**
 * 统一收单交易退款接口
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午10:19 AliPayRefund.php $
 * @see https://docs.open.alipay.com/api_1/alipay.trade.refund
 */
abstract class AliPayRefund extends AliPayPay implements PayRefund
{
    /**
     * 获取接口方法
     * @return string
     */
    protected function getMethod() : string
    {
        return 'alipay.trade.refund';
    }
    
    
    /**
     * 设置平台退款订单数据对象
     * @param TradeRefundInfo $info
     */
    public function setTradeRefundInfo(TradeRefundInfo $info)
    {
        $this->bizContent['out_trade_no']   = $info->payTradeNo;
        $this->bizContent['trade_no']       = $info->payApiTradeNo;
        $this->bizContent['out_request_no'] = $info->refundNo;
        $this->bizContent['refund_amount']  = TransHelper::formatMoney(floatval($info->refundPrice));
        $this->bizContent['refund_reason']  = $info->remark;
    }
    
    
    /**
     * 设置退款结果通知url
     * @param string $notifyUrl
     * @deprecated 无意义
     */
    public function setNotifyUrl(string $notifyUrl)
    {
    }
    
    
    /**
     * 执行退款
     * @return PayRefundResult
     * @throws AliPayPayException
     */
    public function refund() : PayRefundResult
    {
        $res = new PayRefundResult();
        try {
            $result = $this->execute('alipay_trade_refund_response');
            $res->setRefundAccount($result['buyer_logon_id'] ?? '');
        } catch (AliPayPayException $e) {
            // 需重试
            // https://opendocs.alipay.com/apis/api_1/alipay.trade.refund#%E4%B8%9A%E5%8A%A1%E9%94%99%E8%AF%AF%E7%A0%81
            if ($e->getSubCode() === 'ACQ.SYSTEM_ERROR') {
                $res->setNeedRehandle(true);
            } else {
                throw $e;
            }
        }
        
        return $res;
    }
}