<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service\Config;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it> & Wim Nijmeijer <wnijmeijer@coveo.com>
 */
interface IndexerConfigInterface
{
    /**
     * Get stores to index
     *
     * @return array
     */
    public function getStores();

    /**
     * Get attributes to export
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Get attributes to export, exclude custom attributes
     *
     * @return array
     */
    public function getAttributesWithoutCustoms();

    /**
     * Get attributes for simple to export
     *
     * @return array
     */
    public function getSimpleAttributes();

    /**
     * Get AWS access key
     *
     * @return string|null
     */
    public function getAwsAccessKey();

    /**
     * Get AWS secret key
     *
     * @return string|null
     */
    public function getAwsSecretKey();

    /**
     * Get AWS bucket name
     *
     * @return string|null
     */
    public function getAwsBucketName();

    /**
     * Get AWS bucket path
     *
     * @return string|null
     */
    public function getAwsBucketPath();

    /**
     * Check if dry run mode is enabled
     *
     * @return boolean
     */
    public function isDryRunModeEnabled();

}
