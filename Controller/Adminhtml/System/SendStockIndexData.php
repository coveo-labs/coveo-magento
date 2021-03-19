<?php
namespace Coveo\Search\Controller\Adminhtml\System;

use Coveo\Search\Api\Service\Indexer\DataSenderInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Coveo\Search\Api\Service\LoggerInterface;

class SendStockIndexData extends Action
{
    const PERMISSION_RESOURCE = 'Coveo_Search::send_stock_data';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DataSenderInterface
     */
    private $dataSender;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param DataSenderInterface $dataSender
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        DataSenderInterface $dataSender
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->dataSender = $dataSender;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $this->logger->debug('[DataSender Stock] Request catalog data send..');
        $isSuccess = $this->dataSender->sendStock();

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        if ($isSuccess === false) {
            return $result->setHttpResponseCode(500);
        }

        $this->logger->debug('[DataSender Stock] Done!');
        return $result->setData(['status' => 'ok']);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed() && $this->_authorization->isAllowed(self::PERMISSION_RESOURCE);
    }
}
?>
