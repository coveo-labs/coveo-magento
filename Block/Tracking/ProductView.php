<?php
namespace Coveo\Search\Block\Tracking;

use Coveo\Search\Api\Block\ScriptInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Coveo\Search\Api\Service\TrackingInterface;
use Coveo\Search\Api\Service\Config\AnalyticsConfigInterface;

class ProductView extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'coveo-tracking-productview';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * @var AnalyticsConfigInterface
     */
    protected $analyticsConfig;

    /**
     * ProductView constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param TrackingInterface $tracking
     * @param AnalyticsConfigInterface $analyticsConfig
     */
    public function __construct(
        Context $context,
        Registry $registry,
        TrackingInterface $tracking,
        AnalyticsConfigInterface $analyticsConfig
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->tracking = $tracking;
        $this->analyticsConfig = $analyticsConfig;
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }

    /**
     * Get current product
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    protected function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get current product tracking params
     *
     * @return array|null
     */
    public function getProductTrackingParams()
    {
        $product = $this->getCurrentProduct();
        if ($product === null) {
            return null;
        }

        return $this->tracking->getProductTrackingParamsPDP($product);
    }

    /**
     * Check if Javascript library is included
     *
     * @return boolean
     */
    public function isLibraryIncluded()
    {
        return $this->analyticsConfig->isLibraryIncluded();
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        if ($this->getCurrentProduct() === null) {
            return '';
        }
        return parent::_toHtml();
    }
}
