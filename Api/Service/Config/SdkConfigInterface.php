<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service\Config;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it> & Wim Nijmeijer <wnijmeijer@coveo.com>
 */
interface SdkConfigInterface
{
    /**
     * Get library endpoint
     *
     * @return string
     */
    public function getLibraryEndpoint();

    /**
     * Get core key
     *
     * @return string
     */
    public function getCoreKey();

    /**
     * Get inpute selector
     *
     * @return string
     */
    public function getInputSelector();

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Check if debug mode is enabled
     *
     * @return boolean
     */
    public function isDebugModeEnabled();
}
