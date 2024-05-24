<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NovaTwoPay\Gateway\Request;


use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Payment\Model\InfoInterface;

class AuthorizationRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        // 获取POST参数
        $requestData = $this->request->getContent();


        //ls-liu
        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);


        $requestData = json_decode($requestData, true);

        $cardInfo = $requestData['paymentMethod']['additional_data'];
        $firstname = $requestData['billingAddress']['firstname'];
        $lastname = $requestData['billingAddress']['lastname'];


//        // 校验卡信息
//        $check_card = $this->wppg_ntopay_check_card($cardInfo, $firstname, $lastname);
//
//
//        if($check_card['status'] == false) {
//            throw new CouldNotSaveException(
//                __($check_card['error'])
//            );
//        }
//
//
//        if (!isset($buildSubject['payment'])
//            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
//        ) {
//            throw new \InvalidArgumentException('Payment data object should be provided');
//        }
//
//
//        /** @var PaymentDataObjectInterface $payment */
//        $payment = $buildSubject['payment'];
//
//        $order = $payment->getOrder();
//        //$address = $order->getShippingAddress();
//
//        $order_number = $order->getOrderIncrementId();
//
//
//        // 获取订单的收货地址
//
//
//        $order_length_left = 10 - strlen(floor($order_number));
//        if($order_length_left > 0) {
//            $min_number = pow(10, $order_length_left - 1);
//            $max_number = pow(10, $order_length_left) - 1;
//            $new_order_number = rand($min_number, $max_number). 'WP'. $order_number;
//        } else {
//            $new_order_number = 'WP'. $order_number;
//        }
//
//        $need_email = 'nothave@gmail.com';
//
//        $billingAddress = array(
//            'firstName' => $firstname,
//            'lastName' => $lastname,
//            'street' => $requestData['billingAddress']['street'][0],
//            'houseNumberOrName' => isset($requestData['billingAddress']['street'][1]) ?? $requestData['billingAddress']['street'][0],
//            'city' => $requestData['billingAddress']['city'],
//            'postalCode' => $requestData['billingAddress']['postcode'],
//            'stateOrProvince' => $requestData['billingAddress']['region'],
//            'country' => $requestData['billingAddress']['countryId'],
//            'phone' => $requestData['billingAddress']['telephone'],
//            'email' => $requestData['email'] ?? $need_email
//        );
//
//        $shippingAddress = array(
//            'firstName' => $firstname,
//            'lastName' => $lastname,
//            'street' => $requestData['billingAddress']['street'][0],
//            'houseNumberOrName' => isset($requestData['billingAddress']['street'][1]) ?? $requestData['billingAddress']['street'][0],
//            'city' => $requestData['billingAddress']['city'],
//            'postalCode' => $requestData['billingAddress']['postcode'],
//            'stateOrProvince' => $requestData['billingAddress']['region'],
//            'country' => $requestData['billingAddress']['countryId'],
//            'phone' => $requestData['billingAddress']['telephone'],
//            'email' => $requestData['email'] ?? $need_email
//        );
//
//        $callback_url = 'callback';
//
//        //ls-liu
//        // 获取自定义支付方式的配置值
//
//        $Sandbox = $this->scopeConfig->getValue('payment/nova_two_pay/Sandbox', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//        $environment = $Sandbox;
//        $AccountID = $this->scopeConfig->getValue('payment/nova_two_pay/AccountID', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//        $MD5Cert = $this->scopeConfig->getValue('payment/nova_two_pay/MD5Cert', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//
//
//        $myorder = array(
//            'accountId' => $AccountID,
//            'merOrderId' => $new_order_number,
//            'merTradeId' => $new_order_number,
//            'amount' => array(
//                'currency' => $order->getCurrencyCode(),
//                'value' => sprintf("%.2f", substr(sprintf("%.3f", $order->getGrandTotalAmount()), 0, -1))
//            ),
//            'version' => '2.1',
//            'card' => $check_card['info'],
//            'billingAddress' => $billingAddress,
//            'deliveryAddress' => $shippingAddress,
//            'shopperUrl' => $callback_url,
//            'notifyUrl' => $callback_url,
//            'md5Key' => $MD5Cert,
//        );


//        $result = $this->wppg_ntopay_sendRequest($myorder, $environment);
//		$logger->debug('liu order_res'.json_encode($result));

//        if($result['resultCode'] == '10000') {
//
//			$data_str = json_encode(['url' => $result['checkoutUrl']]);
//
//			$res = $this->curl_request('http://nova-magento.com/nova2pay/payment/payment', $data_str);
//			$logger->debug('liu order_curl_res'.json_encode($res));
//
////			$redirect_url = $result['redirectUrl'];
////			$this->curl_request($redirect_url);
//
//            //修改订单表  应付金额 和 实际支付金额
////            $order = $payment->getOrder();
////            $payAmountNum = $myorder['amount']['value'];
////            $order->setTotalPaid($payAmountNum);
////            $order->setBaseTotalPaid($payAmountNum);
////            $order->setGrandTotal($payAmountNum);
////            $order->setBaseGrandTotal($payAmountNum);
////
////            // 保存订单和支付信息
////            $order->save();
//        } else {
//
//            throw new CouldNotSaveException(
//                __($result['resultMessage'])
//            );
//        }

        //ls

        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }


        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        //var_dump($payment);die;
        $order = $payment->getOrder();
        $address = $order->getShippingAddress();



        $logger->debug('liu order_adress'.json_encode($address));
        $logger->debug('liu melon_order'.json_encode($order));
        $logger->debug('liu melon_adress'.json_encode($order));


        return [
            'TXN_TYPE' => 'A',
            'INVOICE' => $order->getOrderIncrementId(),
            'AMOUNT' => $order->getGrandTotalAmount(),
            'CURRENCY' => $order->getCurrencyCode(),
            //'EMAIL' => $address->getEmail(),
            'MERCHANT_KEY' => $this->config->getValue(
                'merchant_gateway_key',
                $order->getStoreId()
            )
        ];
    }

    /**
     * Check Card
     */
    public function wppg_ntopay_check_card($info, $firstname, $lastname) {

        $number = preg_replace('/[^0-9]/', '', $info['card_num']);
        $expiry = explode('/', $info['card_expiry']);
        $expiry_month = preg_replace('/[^0-9]/', '', $expiry[0]);
        $expiry_year = preg_replace('/[^0-9]/', '', $expiry[1]);
        $cvc = $info['card_cvc'];
        $error = '';

        // Card Number
        if(empty($number)) {
            $error = 'Card Error';
        }

        // First Name
        if(empty($firstname)) {
            $error = 'Name Error';
        }

        // CVC
        if(empty($cvc) || strlen($cvc) < 3) {
            $error = 'CVC ERROR';
        }

        // Month
        if (!(is_numeric($expiry_month) && ($expiry_month > 0) && ($expiry_month < 13))) {
            $error = 'Expiry Month Error';
        }

        // Year
        $current_year = date('Y');

        if(empty($expiry_year)) {
            $error = 'Expiry Year Error';
        } else {
            if (strlen($expiry_year) == 2) {
                $expiry_year = intval(substr($current_year, 0, 2) . $expiry_year);
            };
        }

        if($error) {
            return array(
                'status' => false,
                'error' => $error
            );
        } else {
            return array(
                'status' => true,
                'info' => array(
                    'number' => $number,
                    'expiryMonth' => $expiry_month,
                    'expiryYear' => $expiry_year,
                    'cvc' => $cvc,
                    'firstName' => $firstname,
                    'lastName' => $lastname
                )
            );
        }

    }


    /**
     * Send Request
     */
    public function wppg_ntopay_sendRequest($order, $environment) {
        $order['tf_sign'] = $this->wppg_ntopay_sign($order);


        //return 22;
        $url = $environment == FALSE ? 'https://api.silverexpress.asia/payment-order/api/transaction/apiplus/pay' : 'https://api.test.silverexpress.asia/payment-order/api/transaction/apiplus/pay';

        $data_str = json_encode($order);
		$response = $this->curl_request($url, $data_str);


//        $response = wp_remote_retrieve_body(wp_remote_post($url, array(
//            'headers'   => array('Content-Type' => 'application/json; charset=utf-8'),
//            'body'      => json_encode($order),
//            'method'    => 'POST'
//        )));

        return json_decode($response, true);
    }


	public function curl_request($url, $data_str = '', $method = 'POST')
	{
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );

		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );


		if ($method == 'POST') {
			curl_setopt ( $curl, CURLOPT_POST, 1 );
		}

		if ($data_str) {
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data_str );
			// 设置json头
			curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json; charset=utf-8',
					'Content-Length: ' . strlen($data_str))
			);
		}


		$response = curl_exec ( $curl );
		curl_close ( $curl );
		return $response;
	}


    /**
     * Sign Action
     */
    public function wppg_ntopay_sign($params) {
        if(!empty($params)){
            $p = ksort($params);
            if($p) {
                $str = '';
                foreach ($params as $k => $val) {
                    if(!empty($val)) {
                        if(is_array($val)) {
                            if(count($val) == count($val, 1)) {
                                ksort($val);
                                $val = json_encode($val, 320);
                            } else {
                                foreach($val as $key => $value) {
                                    ksort($val[$key]);
                                };
                                $val = json_encode($val);
                            };
                        }
                        $str .= $k .'=' . $val . '&';
                    }

                };
                $strs = rtrim($str, '&');
                return strtoupper(md5($strs));
            }
            return false;
        }
        return false;
    }
}
