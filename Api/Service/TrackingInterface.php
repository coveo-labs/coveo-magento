<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it> & Wim Nijmeijer <wnijmeijer@coveo.com>
 */
interface TrackingInterface
{
    /**
     * Get tracking API user agent
     *
     * @return string
     */
    public function getApiAgent();

    public function getSession();

    public function setSession($session);

    /**
     * Get current PHP version
     *
     * @return string
     */
    public function getPHPVersion();

    /**
     * Get current Magento version
     *
     * @return string
     */
    public function getMagentoVersion();

    /**
     * Get current installed module version
     *
     * @return string
     */
    public function getModuleVersion();

    /**
     * Get profiling params to identify caller
     *
     * @param array $override
     * @return array
     */
    public function getProfilingParams($override = null);

    /**
     * Load single category
     *
     * @param integer $id
     * @return null|array
     */
    public function loadCategory($id);

    /**
     * Load products categories
     *
     * @param array $ids
     * @return null|array
     */
    public function loadCategories($ids);

    /**
     * Get product tracking params
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param integer $position
     * @param integer $quantity
     * @return null|array
     */
    public function getProductTrackingParams($product, $position = 0, $quantity = 1);

    /**
     * Get product tracking params
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return null|array
     */
    public function getProductTrackingParamsPDP($product);

    /**
     * Get order tracking params
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return null|array
     */
    public function getOrderTrackingParams($order);

    /**
     * Get client IP
     *
     * @return string
     */
    public function getRemoteAddr();

    /**
     * Get client user agent
     *
     * @return string
     */
    public function getUserAgent();

    /**
     * Get last page
     *
     * @return string
     */
    public function getLastPage();

    /**
     * Get current page
     *
     * @return string
     */
    public function getCurrentPage();

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode();
    public function getAnalytics();

    /**
     * Get currency code
     *
     * @param array $params
     * @return boolean
     */
    public function executeTrackingRequest($params);
    public function executeTrackingSearchRequest($params);

    /**
     * Get currency code
     *
     * @return string
     */
    public function getUuid();
}
