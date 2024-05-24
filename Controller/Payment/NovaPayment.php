<?php

namespace Magento\NovaTwoPay\Controller\Payment;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;


/**
 * Class YourController
 * @CsrfIgnore
 */
class NovaPayment extends \Magento\Framework\App\Action\Action
{
	protected $jsonFactory;

	protected $request;


	protected $urlBuilder;


	public function __construct(
		Context      $context,
		JsonFactory $jsonFactory,
		RequestInterface $request,
		ScopeConfigInterface $scopeConfig
//		UrlInterface $urlBuilder
		//\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	)
	{
		parent::__construct($context);
		$this->jsonFactory = $jsonFactory;
		$this->request = $request;
		$this->scopeConfig = $scopeConfig;
//		$this->urlBuilder = $urlBuilder;
//		$this->scopeConfig = $scopeConfig;
	}

	/**
	 * Disable CSRF validation for this action
	 *
	 * @return bool
	 */
	public function _isAllowedAction($action)
	{
		return true;
	}

	public function execute()
	{
		// 获取POST请求中的参数
		//$postData = $this->getRequest()->getPostValue();
		$requestData = $this->getRequest()->getPostValue();

		//ls-liu
		$logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);

		$logger->debug('liu order_req121_opost'.json_encode($requestData));


				// 创建JSON响应
		$res_return = $this->jsonFactory->create();
//
//		$result->setData(['code' => 200,'url' => 'https://pay-uat.embolld.com/pay.php?aToken=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYmYiOjE3MTU4NDEwNjIsImV4cCI6MTcxNjAxMzg2MiwiYWNjdENvZGUiOiI3OTk5OTk1MyJ9.8ntd1NXGiFywO5ZkjkQFRJccmHjsI6pI3rCkSQcSdWo&oId=OD20240516143102107741']);
//		return $result;

//
//		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
//		$resultRedirect->setUrl('https://pay-uat.embolld.com/pay.php?aToken=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYmYiOjE3MTU4NDEwNjIsImV4cCI6MTcxNjAxMzg2MiwiYWNjdENvZGUiOiI3OTk5OTk1MyJ9.8ntd1NXGiFywO5ZkjkQFRJccmHjsI6pI3rCkSQcSdWo&oId=OD20240516143102107741');
//		return $resultRedirect;

		//$requestData = json_decode($postData, true);

		$cardInfo = [
			'card_num' => $requestData['card_num'],
			'card_expiry' => $requestData['card_expiry'],
			'card_cvc' => $requestData['card_cvc']
		];
		$firstname = $requestData['firstname'];
		$lastname = $requestData['lastname'];

		// 校验卡信息
		$check_card = $this->wppg_ntopay_check_card($cardInfo, $firstname, $lastname);


		if($check_card['status'] == false) {

			throw new CouldNotSaveException(
				__($check_card['error'])
			);
		}


		// 获取订单增量ID
		$order_number = $this->getRequest()->getParam('increment_id');
		$logger->debug('liu order_req121_num'.$order_number);


		// 获取订单的收货地址


		$order_length_left = 10 - strlen(floor($order_number));
		if($order_length_left > 0) {
			$min_number = pow(10, $order_length_left - 1);
			$max_number = pow(10, $order_length_left) - 1;
			$new_order_number = rand($min_number, $max_number). 'WP'. $order_number;
		} else {
			$new_order_number = 'WP'. $order_number;
		}

		$need_email = 'nothave@gmail.com';

		$billingAddress = array(
			'firstName' => $firstname,
			'lastName' => $lastname,
			'street' => $requestData['street'],
			'houseNumberOrName' => $requestData['street'],
			'city' => $requestData['city'],
			'postalCode' => $requestData['postcode'],
			'stateOrProvince' => $requestData['region_id'],
			'country' => 'CN',
			'phone' => $requestData['telephone'],
			'email' => $requestData['email'] ?? $need_email
		);

		$shippingAddress = array(
			'firstName' => $firstname,
			'lastName' => $lastname,
			'street' => $requestData['street'],
			'houseNumberOrName' => $requestData['street'],
			'city' => $requestData['city'],
			'postalCode' => $requestData['postcode'],
			'stateOrProvince' => $requestData['region_id'],
			'country' => 'CN',
			'phone' => $requestData['telephone'],
			'email' => $requestData['email'] ?? $need_email
		);


		// 获取当前网站的基本URL
		//$baseUrl = $this->urlBuilder->getBaseUrl();
		//$baseUrl = 'http://nova-magento.com/';

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$baseUrl = $objectManager->get( 'Magento\Framework\UrlInterface' )->getBaseUrl();

		// 在这里使用基本URL，例如构建异步通知地址
		//$notifyUrl = $baseUrl . 'notify';

		$shopperUrl = $baseUrl . 'checkout/onepage/success/';


		$logger->debug('liu order_base_url----'.$baseUrl);

		$notifyUrl = $baseUrl . 'nova2pay/payment/payment';

		//ls-liu
		// 获取自定义支付方式的配置值

		$Sandbox = $this->scopeConfig->getValue('payment/nova_two_pay/Sandbox', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$environment = $Sandbox;
		$AccountID = $this->scopeConfig->getValue('payment/nova_two_pay/AccountID', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$MD5Cert = $this->scopeConfig->getValue('payment/nova_two_pay/MD5Cert', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


		$myorder = array(
			'accountId' => $AccountID,
			'merOrderId' => $new_order_number,
			'merTradeId' => $new_order_number,
			'amount' => array(
				'currency' => 'USD',
				'value' => sprintf("%.2f", substr(sprintf("%.3f", '94.8'), 0, -1))
			),
			'version' => '2.1',
			'card' => $check_card['info'],
			'billingAddress' => $billingAddress,
			'deliveryAddress' => $shippingAddress,
			'shopperUrl' => $shopperUrl,
			'notifyUrl' => $notifyUrl,
			'md5Key' => $MD5Cert,
		);


		$result = $this->wppg_ntopay_sendRequest($myorder, $environment);
		//var_dump($result); die;
		$logger->debug('liu order_post_data'.json_encode($myorder));


		if($result['resultCode'] == '10000') {

//			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
//			$resultRedirect->setUrl($url);
//			return $resultRedirect;

			$url = $result['checkoutUrl'];
			$res_return->setData(['code' => 200,'url' => $url]);
			return $res_return;

			//修改订单表  应付金额 和 实际支付金额
//            $order = $payment->getOrder();
//            $payAmountNum = $myorder['amount']['value'];
//            $order->setTotalPaid($payAmountNum);
//            $order->setBaseTotalPaid($payAmountNum);
//            $order->setGrandTotal($payAmountNum);
//            $order->setBaseGrandTotal($payAmountNum);
//
//            // 保存订单和支付信息
//            $order->save();
		} else {

			throw new CouldNotSaveException(
				__($result['resultMessage'])
			);
		}


	}
	public function createCsrfValidationException(RequestInterface $request): ? InvalidRequestException
	{
		return null;
	}

	public function validateForCsrf(RequestInterface $request): ?bool
	{
		return true;
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

		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt ( $curl, CURLOPT_POST, 1 );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data_str );

		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );

		// 设置json头
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json; charset=utf-8',
				'Content-Length: ' . strlen($data_str))
		);

		$response = curl_exec ( $curl );
		curl_close ( $curl );

//        $response = wp_remote_retrieve_body(wp_remote_post($url, array(
//            'headers'   => array('Content-Type' => 'application/json; charset=utf-8'),
//            'body'      => json_encode($order),
//            'method'    => 'POST'
//        )));

		return json_decode($response, true);
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


	/**
	 * 验签
	 */
	public function verifySign($signParmas, $publicKey)
	{
		try {
			$sign = $signParmas['tf_sign'];
			unset($signParmas['tf_sign']);
			$data = $this->ksortData($signParmas);

			$formattedData = '';

			foreach ($data as $key => $value) {
				if (is_array($value)) {
					$value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				}
				if ($value !== '' && $value !== null) {
					$formattedData .= $key . '=' . $value . '&';
				}
			}
			$formattedData = rtrim($formattedData, '&');

			$isValid = $this->verifySignature($formattedData, $sign, $publicKey);

			//var_dump($isValid);die;

			if ($isValid) {
				return $isValid;
			} else {
				// 创建JSON响应
				$result = $this->jsonFactory->create();
				$result->setData(['message' => ' isValid  fail']);
				return $result;
			}
		} catch (\Magento\Framework\Exception\LocalizedException $e) {
			// 创建JSON响应
			$result = $this->jsonFactory->create();
			$result->setData(['message' => ' isValid  fail']);
			return $result;
		}
	}

	/**
	 * 根据待签名原文 和 私钥 生成 签名串
	 * @param privateKey  加签私钥
	 * @param params      待加签的参数对象
	 * @return 签名串
	 */
	public function verifySignature($data, $sign, $pubKey)
	{
		$sign = base64_decode($sign);
		$pubKey = "-----BEGIN PUBLIC KEY-----\n" .
			wordwrap($pubKey, 64, "\n", true) .
			"\n-----END PUBLIC KEY-----";
		$key = openssl_pkey_get_public($pubKey);
		$result = openssl_verify($data, $sign, $key, OPENSSL_ALGO_SHA1) === 1;
		return $result;
	}

	public function ksortData($data)
	{
		ksort($data);

		$recursiveKsort = function (&$array) use (&$recursiveKsort) {
			foreach ($array as $key => &$value) {
				if (is_array($value)) {
					$recursiveKsort($value);
					if (empty($value)) {
						unset($array[$key]);
					}
				} elseif ($value === '' || $value === null) {
					unset($array[$key]);
				}
			}
			ksort($array);
		};

		$recursiveKsort($data);

		return $data;
	}
}