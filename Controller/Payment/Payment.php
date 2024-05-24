<?php

namespace Magento\NovaTwoPay\Controller\Payment;


use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;


/**
 * Class YourController
 * @CsrfIgnore
 */
class Payment extends \Magento\Framework\App\Action\Action
{
	protected $jsonFactory;

	protected $request;


	public function __construct(
		Context      $context,
		JsonFactory $jsonFactory,
		RequestInterface $request

		//\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	)
	{
		parent::__construct($context);
		$this->jsonFactory = $jsonFactory;
		$this->request = $request;
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

		//		// 创建JSON响应
		$result = $this->jsonFactory->create();
		//ls-liu
		$logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
		$requestData = $this->getRequest()->getPostValue();

		$logger->debug('liu order_req22222_opost'.json_encode($requestData));
		//$result->setData(['url' => 'https://pay-uat.embolld.com/pay.php?aToken=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYmYiOjE3MTU4NDEwNjIsImV4cCI6MTcxNjAxMzg2MiwiYWNjdENvZGUiOiI3OTk5OTk1MyJ9.8ntd1NXGiFywO5ZkjkQFRJccmHjsI6pI3rCkSQcSdWo&oId=OD20240516143102107741']);

//		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
//		$resultRedirect->setUrl('https://pay-uat.embolld.com/pay.php?aToken=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYmYiOjE3MTU4NDEwNjIsImV4cCI6MTcxNjAxMzg2MiwiYWNjdENvZGUiOiI3OTk5OTk1MyJ9.8ntd1NXGiFywO5ZkjkQFRJccmHjsI6pI3rCkSQcSdWo&oId=OD20240516143102107741');
//		return $result;




//		// 创建JSON响应
		$result = $this->jsonFactory->create();
//		$result->setData(['message' => 'Hello, World!']);
//		return $result;

		 //获取POST参数
		$requestData = $this->request->getContent();
		$noticeData = json_decode($requestData, true);

		//$postParam = $this->getRequest()->getPost();
//		$result->setData($noticeData);
//		return $result;

		$tradeStatus = $noticeData['tradeStatus'];

		if (($tradeStatus ?? null) == 'Approved') {

			// 获取对象管理器
			$objectManager = ObjectManager::getInstance();

// 获取资源连接对象
			$resource = $objectManager->get(ResourceConnection::class);

// 获取数据库连接和表前缀
			$connection = $resource->getConnection();
			$tablePrefix = $resource->getTablePrefix();

			// 获取支付方法
			$getPaymentSql = "SELECT * FROM {$tablePrefix}core_config_data  WHERE `path` = 'payment/nova_two_pay/RSAPublicKey'";

			// Execute the query
			$result = $connection->query($getPaymentSql);

			// Fetch results
			$data = $result->fetchAll();
			$storeInfo = $data[0];

			$RSAPublicKey = $storeInfo['value'];

			// 检查字符串长度是否是偶数
			if (strlen($noticeData['tf_sign']) % 2 !== 0) {
				// 如果长度不是偶数，添加一个额外的0
				$noticeData['tf_sign'] = '0' . $noticeData['tf_sign'];
			}

			$noticeData['tf_sign'] = hex2bin($noticeData['tf_sign']);

			$this->verifySign($noticeData, $RSAPublicKey);

			$orderId = $noticeData['orderId'];
			$paid_amount = $noticeData['amount'];
			$currency = $noticeData['currency'];
//

//
// 要修改的数据相关信息，例如：更新特定用户的邮箱
			$customerEmail = '89.99';
			$customerId = '51'; // 示例用户ID

// 创建SQL更新语句
			$updateSql = "UPDATE {$tablePrefix}sales_order";
			$updateSql .= " SET total_paid = '{$paid_amount}',";
			$updateSql .= "base_total_paid = '{$paid_amount}',";
			$updateSql .= "grand_total = '{$paid_amount}',";
			$updateSql .= "base_grand_total = '{$paid_amount}'";
			$updateSql .= " WHERE entity_id = {$orderId}";

			$logger->debug('liu order_query_sql--'.$updateSql);

 //执行SQL更新语句
			try {
				$connection->query($updateSql);
				// 成功后的操作，例如记录日志或其他
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				// 出现错误时的操作，例如记录日志或其他
				$result->setData(['message' => 'query fail']);
				return $result;
			}
			$result->setData(['message' => 'notify success']);
			return $result;
		} else {
			$result->setData(['message' => 'pay status error']);
			return $result;
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
			$isValid = 1;
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