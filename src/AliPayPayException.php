<?php

namespace BusyPHP\alipay\pay;

use RuntimeException;
use Throwable;

/**
 * 支付宝支付异常类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午9:44 AliPayPayException.php $
 */
class AliPayPayException extends RuntimeException
{
    /**
     * @var string
     */
    private $subMsg;
    
    /**
     * @var string
     */
    private $subCode;
    
    /**
     * @var int
     */
    private $errCode;
    
    /**
     * @var string
     */
    private $errMessage;
    
    
    public function __construct(string $errMessage = "", int $errCode = 0, string $subMsg = '', string $subCode = '', Throwable $previous = null)
    {
        $this->errCode    = $errCode;
        $this->errMessage = $errMessage;
        $this->subMsg     = $subMsg;
        $this->subCode    = $subCode;
        
        parent::__construct("{$errMessage}, Code: {$errCode}, SubMsg: {$subMsg}, SubCode: {$subCode}", 0, $previous);
    }
    
    
    /**
     * @return string
     */
    public function getSubMsg() : string
    {
        return $this->subMsg;
    }
    
    
    /**
     * @return string
     */
    public function getSubCode() : string
    {
        return $this->subCode;
    }
    
    
    /**
     * @return int
     */
    public function getErrCode() : int
    {
        return $this->errCode;
    }
    
    
    /**
     * @return string
     */
    public function getErrMessage() : string
    {
        return $this->errMessage;
    }
}