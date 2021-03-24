<?php
namespace Coveo\Search\Block\Tracking;

use Coveo\Search\Api\Block\ScriptInterface;
use Coveo\Search\Api\Service\Config\AnalyticsConfigInterface;
use Coveo\Search\Api\Service\ConfigInterface;
use Coveo\Search\Api\Service\TrackingInterface;
use Magento\Framework\View\Element\Template\Context;

class LibraryInit extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'coveo-tracking-library-init';

    /**
     * @var AnalyticsConfigInterface
     */
    protected $analyticsConfig;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * LibraryInit constructor.
     *
     * @param Context $context
     * @param AnalyticsConfigInterface $analyticsConfig
     * @param ConfigInterface $config
     * @param TrackingInterface $tracking
     */
    public function __construct(
        Context $context,
        AnalyticsConfigInterface $analyticsConfig,
        ConfigInterface $config,
        TrackingInterface $tracking
    ) {
        parent::__construct($context);
        $this->analyticsConfig = $analyticsConfig;
        $this->config = $config;
        $this->tracking = $tracking;
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }

    /**
     * Is in debug mode
     *
     * @return boolean
     */
    public function isDebugMode()
    {
        return $this->analyticsConfig->isDebugMode();
    }

    /**
     * Get Analytics key
     *
     * @return string|null
     */
    public function getKey()
    {
        return $this->analyticsConfig->getKey();
    }

    /**
     * Get cookie domain
     *
     * @param string $default
     * @return array
     */
    public function getCookieDomain($default = null)
    {
        return $this->analyticsConfig->getCookieDomain($default);
    }

    /**
     * Get storeId
     *
     * @param string $default
     * @return string
     */
    public function getStoreId()
    {
        return $this->analyticsConfig->getStoreId();
    }

    /**
     * Get storeCode
     *
     * @param string $default
     * @return string
     */
    public function getStoreCode()
    {
        return $this->analyticsConfig->getStoreCode();
    }

    /**
     * Get language
     *
     * @param string $default
     * @return string
     */
    public function getLanguage()
    {
        return $this->analyticsConfig->getLanguage();
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->tracking->getCurrencyCode();
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCustomerId()
    {
      $custId =$this->tracking->getCustomerId();
      if ($custId=='' || $custId==null)  {
        $custId='anonymous';
      }
      return  $custId;
    }

     /**
     * Get searchId
     *
     * @return string
     */
    public function getSearchId()
    {
        return $this->tracking->getSearchId();
    }


     /**
     * Get api
     *
     * @return string
     */
    public function getApi(){
      return str_replace('/v15/analytics/','',$this->analyticsConfig->getAPIEndpoint());
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
