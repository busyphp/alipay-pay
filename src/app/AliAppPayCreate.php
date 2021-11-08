<?php

namespace BusyPHP\alipay\pay\app;

use BusyPHP\alipay\pay\AliPayCreate;

/**
 * App支付请求下单
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午10:50 AliAppPayCreate.php $
 * @see https://docs.open.alipay.com/204/105465/
 */
class AliAppPayCreate extends AliPayCreate
{
    protected $buildUrlIsOnlyQuery = true;
    
    protected $productCode         = 'QUICK_MSECURITY_PAY';
    
    
    /**
     * 获取配置名称
     * @return string
     */
    protected function getConfigKey() : string
    {
        return 'app';
    }
    
    
    /**
     * 获取接口方法
     * @return string
     */
    protected function getMethod() : string
    {
        return 'alipay.trade.app.pay';
    }
}