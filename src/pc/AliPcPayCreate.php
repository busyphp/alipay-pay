<?php

namespace BusyPHP\alipay\pay\pc;

use BusyPHP\alipay\pay\AliPayCreate;

/**
 * PC场景下单并支付
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午10:20 AliPcPayCreate.php $
 * @see https://docs.open.alipay.com/270/105899/
 */
class AliPcPayCreate extends AliPayCreate
{
    protected $productCode = 'FAST_INSTANT_TRADE_PAY';
    
    
    /**
     * 获取配置名称
     * @return string
     */
    protected function getConfigKey() : string
    {
        return 'pc';
    }
    
    
    /**
     * 获取接口方法
     * @return string
     */
    protected function getMethod() : string
    {
        return 'alipay.trade.page.pay';
    }
    
    
    /**
     * 设置同步回调地址
     * @param string $returnUrl
     */
    public function setReturnUrl(string $returnUrl)
    {
        $this->params['return_url'] = $returnUrl;
    }
}