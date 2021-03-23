<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service\Config;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it> & Wim Nijmeijer <wnijmeijer@coveo.com>
 */
interface SearchConfigInterface
{
    /**
     * Get default limit
     *
     * @return int
     */
    public function getDefaultLimit();

    /**
     * Get default limit
     *
     * @return string
     */
    public function getHub();

    /**
     * Get default limit
     *
     * @return string
     */
    public function getTab();

    /**
     * Get supported order types
     *
     * @return array
     */
    public function getSupportedOrderTypes();

    /**
     * Get query params to exclude as filter
     *
     * @return array
     */
    public function getParamFilterExclusion();

    /**
     * Override filter price param, default is 'price'
     *
     * @return string
     */
    public function getFilterPriceParam();

    /**
     * Get message style
     *
     * @return string|null
     */
    public function getMessageStyle();

    /**
     * Is search response enriched
     *
     * @return boolean
     */
    public function isEnriched();

    /**
     * Is Magento search fallback enable
     *
     * @return boolean
     */
    public function isFallbackEnable();
}
