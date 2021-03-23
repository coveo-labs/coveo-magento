<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service;

use \Coveo\Search\SDK\Storage\SessionInterface as SDKSessionInterface;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it> & Wim Nijmeijer <wnijmeijer@coveo.com>
 */
interface SessionInterface extends SDKSessionInterface
{
    /**
     * Get Client ID from cookie
     *
     * @return string
     */
    public function getClientId();

    /**
     * Get Magento customer ID
     *
     * @return integer
     */
    public function getCustomerId();

    /**
     * Check if user is logged in
     *
     * @return boolean
     */
    public function isLoggedIn();
}
