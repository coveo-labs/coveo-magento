<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Wim Nijmeijer
 */
interface ConfigInterface
{
    /**
     * Get current language
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLanguage();

    /**
     * Get current store code
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreCode();

    /**
     * @return string
     */
    public function getStoreId();

    /**
     * @return string
     */
    public function getUseVariantAsProduct();

    /**
     * Get API key
     *
     * @return string
     */
    public function getApiKeyIndex();
    /**
     * Get API key
     *
     * @return string
     */
    public function getApiKeySearch();

    /**
     * Get API Version
     *
     * @return string
     */
    public function getApiVersion();

    /**
     * Get API base URL
     *
     * @return string
     */
    public function getApiBaseUrl();

    /**
     * Get API base URL
     *
     * @return string
     */
    public function getApiBase();

    /**
     * Get API base URL
     *
     * @return string
     */
    public function getApiSource();

    /**
     * Get API base URL
     *
     * @return string
     */
    public function getApiOrg();

    /**
     * Get API base URL
     *
     * @return string
     */
    public function getApiSearchUrl();

    /**
     * Is debug mode active
     *
     * @return boolean
     */
    public function isDebugModeEnabled();

    /**
     * Is search service active
     *
     * @return boolean
     */
    public function isSearchEnabled();

    /**
     * Is tracking service active
     *
     * @return boolean
     */
    public function isTrackingEnabled();

    /**
     * Is suggestion client side active
     *
     * @return boolean
     */
    public function isSuggestionEnabled();

    /**
     * Is SDK library active
     *
     * @return boolean
     */
    public function isSdkEnabled();

    /**
     * Is SpeechToText active
     *
     * @return boolean
     */
    public function isSpeechToTextEnabled();
}
