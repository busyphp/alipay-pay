<?php

namespace BusyPHP\alipay\pay;

use BusyPHP\trade\interfaces\PayRefundNotifyResult;
use BusyPHP\trade\interfaces\PayRefundQuery;
use BusyPHP\trade\interfaces\PayRefundQueryResult;
use BusyPHP\trade\model\refund\TradeRefundField;
use Exception;

/**
 * 统一收单交易退款查询
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午10:19 AliPayRefundQuery.php $
 * @see https://opendocs.alipay.com/apis/api_1/alipay.trade.fastpay.refund.query
 */
abstract class AliPayRefundQuery extends AliPayPay implements PayRefundQuery
{
    /**
     * 获取接口方法
     * @return string
     */
    protected function getMethod() : string
    {
        return 'alipay.trade.fastpay.refund.query';
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
     * @return PayRefundQueryResult
     * @throws Exception
     */
    public function query() : PayRefundQueryResult
    {
        // 构建返回参数
        $notifyResult = new PayRefundNotifyResult();
        $res          = new PayRefundQueryResult($notifyResult);
        
        try {
            $result = $this->execute('alipay_trade_fastpay_refund_query_response');
            
            // 如何判断退款是否成功
            // http://forum.alipay.com/mini-app/post/22901012#reply_23200027
            // https://opensupport.alipay.com/support/helpcenter/193/201602484962#anchor__3
            // 根据以上指引说明，只要返回 out_trade_no、trade_no或refund_amount 就是退款成功
            // 如果接口没有查询到具体的退款信息则代表未退款成功，可以调用退款接口进行重试。重试时请务必保证退款请求号out_request_no以及请求参数一致。
            $result['out_trade_no']   = $result['out_trade_no'] ?? null;
            $result['trade_no']       = $result['trade_no'] ?? null;
            $result['refund_amount']  = $result['refund_amount'] ?? null;
            $result['out_request_no'] = $result['out_request_no'] ?? null;
            if (!$result['trade_no'] || !$result['out_trade_no'] || !$result['refund_amount'] || !$result['out_request_no']) {
                $notifyResult->setNeedReHandle(true);
                $notifyResult->setErrMsg('返回参数为空: ' . implode(',', [
                        $result['trade_no'],
                        $result['out_trade_no'],
                        $result['refund_amount'],
                        $result['out_request_no'],
                    ]));
            } else {
                $notifyResult->setNeedReHandle(false);
                $notifyResult->setStatus(true);
                $notifyResult->setPayApiTradeNo($result['trade_no']);
                $notifyResult->setPayTradeNo($result['out_trade_no']);
                $notifyResult->setRefundNo($result['out_request_no']);
                
                // 退入账户
                $refundRoyaltys = $result['refund_royaltys'] ?? [];
                if ($refundRoyaltys) {
                    $notifyResult->setRefundAccount($refundRoyaltys['trans_in_email'] ?? '');
                }
                
                foreach ($result as $key => $item) {
                    $res->addDetail($key, $item);
                }
            }
        } catch (AliPayPayException $e) {
            // 重新发起查询请求，如果多次重试后还是返回系统错误，请联系支付宝小二处理
            // https://opendocs.alipay.com/apis/api_1/alipay.trade.fastpay.refund.query#%E4%B8%9A%E5%8A%A1%E9%94%99%E8%AF%AF%E7%A0%81
            if ($e->getSubCode() === 'ACQ.SYSTEM_ERROR') {
                $notifyResult->setNeedReHandle(true);
                $notifyResult->setErrMsg($e->getMessage());
            } else {
                throw $e;
            }
        }
        
        return $res;
    }
}