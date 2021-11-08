<?php

namespace BusyPHP\alipay\pay\h5;

use BusyPHP\alipay\pay\AliPayCreate;

/**
 * 手机网站支付接口
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午10:42 AliWapPayCreate.php $
 * @see https://docs.open.alipay.com/203/107090/
 */
class AliH5PayCreate extends AliPayCreate
{
    protected $productCode = 'QUICK_WAP_WAY';
    
    
    /**
     * 获取配置名称
     * @return string
     */
    protected function getConfigKey() : string
    {
        return 'h5';
    }
    
    
    /**
     * 获取接口方法
     * @return string
     */
    protected function getMethod() : string
    {
        return 'alipay.trade.wap.pay';
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
        $this->bizContent['quit_url'] = trim($showUrl);
    }
}