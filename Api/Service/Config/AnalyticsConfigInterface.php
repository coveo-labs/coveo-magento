<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service\Config;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it> & Wim Nijmeijer <wnijmeijer@coveo.com>
 */
interface AnalyticsConfigInterface
{

  /**
     * Get storeId
     *
     * @return string
     */
    public function getStoreId();

    /**
     * Get getStoreCode
     *
     * @return string
     */
    public function getStoreCode();

     /**
     * Get getLanguage
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Get cookie domain
     *
     * @param string $default
     * @return array
     */
    public function getCookieDomain($default = null);

    /**
     * Get library endpoint
     *
     * @return string|null
     */
    public function getLibraryEndpoint();

    /**
     * Get API endpoint
     *
     * @return string|null
     */
    public function getAPIEndpoint();

    /**
     * Get API version
     *
     * @return string|null
     */
    public function getAPIVersion();

    /**
     * Get Analytics key
     *
     * @return string|null
     */
    public function getKey();

    /**
     * Get product link selector in search page
     *
     * @return string|null
     */
    public function getProductLinkSelector();

    /**
     * Get product container selector
     *
     * @return string|null
     */
    public function getProductContainerSelector();

    /**
     * Get product attribute name
     *
     * @return string|null
     */
    public function getProductAttributeName();

    /**
     * Get search id attribute
     *
     * @return string|null
     */
    public function getSearchIdAttribute();

     /**
     * Get search id attribute
     *
     * @return string|null
     */
    public function getHub();

     /**
     * Get search id attribute
     *
     * @return string|null
     */
    public function getTab();

    /**
     * Get pagination type
     *
     * @return string|null
     */
    public function getPaginationType();

    /**
     * Is library included
     *
     * @return boolean
     */
    public function isLibraryIncluded();

    /**
     * Is in debug mode
     *
     * @return boolean
     */
    public function isDebugMode();

    /**
     * Is customer users tracking enabled
     *
     * @return boolean
     */
    public function isUserIdTrackingEnable();
}
