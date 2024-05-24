<?php
namespace Magento\NovaTwoPay\Model\Payment;

class Simple extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'custompayment';
    protected $_isOffline = true;

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        // 在此方法中定义支付方法是否可用的逻辑
        //ls-liu
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('liu 是否可用的逻辑 Simple');
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        // 实现授权逻辑

        //ls-liu
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('liu 实现授权逻辑 Simple');
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        // 实现捕获逻辑
        //ls-liu
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('liu 实现捕获逻辑 Simple');
    }
}
