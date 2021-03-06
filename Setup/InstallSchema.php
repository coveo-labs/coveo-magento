<?php
namespace Coveo\Search\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Model\Indexer\StateFactory;
use Coveo\Search\Model\Indexer\Catalog;
use Coveo\Search\Model\Indexer\AttributesValues;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var StateFactory
     */
    private $stateFactory;

    /**
     * InstallSchema constructor.
     * @param StateFactory $stateFactory
     */
    public function __construct(StateFactory $stateFactory)
    {
        $this->stateFactory = $stateFactory;
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @throws \Exception
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Init catalog index status
         */

        $updateTime = new \DateTime();
        $updateTimeStr = $updateTime->format('Y-m-d H:i:s');

        $state = $this->stateFactory->create();
        $state->loadByIndexer(Catalog::INDEX_NAME);
        $state->setStatus(StateInterface::STATUS_INVALID);
        $state->setUpdated($updateTimeStr);
        $state->save(); //TODO: fix deprecation


        /**
         * Init attributes index status
         */

        $updateTime = new \DateTime();
        $updateTimeStr = $updateTime->format('Y-m-d H:i:s');

        $state = $this->stateFactory->create();
        $state->loadByIndexer(AttributesValues::INDEX_NAME);
        $state->setStatus(StateInterface::STATUS_INVALID);
        $state->setUpdated($updateTimeStr);
        $state->save(); //TODO: fix deprecation

        $setup->endSetup();
    }
}

