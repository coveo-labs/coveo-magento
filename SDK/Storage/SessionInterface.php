<?php
namespace Coveo\Search\SDK\Storage;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Gennaro Vietri <gennaro.vietri@bitbull.it>
 */
interface SessionInterface
{
    /**
     * Store Search ID into session
     *
     * @param string $value
     */
    public function setSearchId($value);

    /**
     * Store Visitor ID into session
     *
     * @param string $value
     */
    public function setVisitorId($value);

    /**
     * Get Search ID from session
     *
     * @return string
     */
    public function getSearchId();

    /**
     * @inheritdoc
     */
    public function getVisitorId();

    /**
     * @inheritdoc
     */
    public function getStoreId();
}