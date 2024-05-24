<?php
namespace Magento\NovaTwoPay\Controller\Index;

use Magento\Framework\Controller\Result\JsonFactory;

class Test extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	protected $jsonFactory;


	public function __construct(
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		JsonFactory $jsonFactory,
		\Magento\Framework\App\Action\Context $context
	)
	{
		$this->_pageFactory = $pageFactory;
		$this->jsonFactory = $jsonFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		// 创建JSON响应
		$result = $this->jsonFactory->create();
		$result->setData(['message' => ' isValid  fail']);
		return $result;
	}

	public function mini_see ()
	{
		// 创建JSON响应
		$result = $this->jsonFactory->create();
		$result->setData(['message' => ' isValid  fail']);
		return $result;
	}
}