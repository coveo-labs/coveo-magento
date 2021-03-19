<?php
namespace Coveo\Search\Block\Tracking;

use Coveo\Search\Api\Block\ScriptInterface;
use Coveo\Search\Api\Service\Config\AnalyticsConfigInterface;
use Coveo\Search\Api\Service\ConfigInterface;
use Magento\Framework\View\Element\Template\Context;

class Library extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'coveo-tracking-library';

    /**
     * @var AnalyticsConfigInterface
     */
    protected $analyticsConfig;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Library constructor.
     *
     * @param Context $context
     * @param AnalyticsConfigInterface $analyticsConfig
     * @param ConfigInterface $config
     */
    public function __construct(
        Context $context,
        AnalyticsConfigInterface $analyticsConfig,
        ConfigInterface $config
    ) {
        parent::__construct($context);
        $this->analyticsConfig = $analyticsConfig;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }

    /**
     * Get library endpoint
     *
     * @return string|null
     */
    public function getLibraryEndpoint()
    {
        return $this->analyticsConfig->getLibraryEndpoint();
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        if ($this->analyticsConfig->isLibraryIncluded() === false) {
            return '';
        }
        return parent::_toHtml();
    }
}
