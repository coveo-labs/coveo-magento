<?php
namespace Coveo\Search\Controller\Adminhtml\System;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Coveo\Search\Api\Service\LoggerInterface;
use Coveo\Search\Api\Service\Indexer\CatalogInterface;

class CatalogReindex extends Action
{
    const PERMISSION_RESOURCE = 'Coveo_Search::catalog_reindex';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CatalogInterface
     */
    private $catalog;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param CatalogInterface $catalog
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        CatalogInterface $catalog
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->catalog = $catalog;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $this->logger->debug('[Reindex Catalog] Request catalog reindex..');
        $this->catalog->execute();
        $this->logger->debug('[Reindex Catalog] Done!');

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
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
