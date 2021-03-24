<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service;

use Coveo\Search\Result;
use Magento\Catalog\Ui\DataProvider\Product\ProductCollection;
use Coveo\Search\Api\Service\SessionInterface;

/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it> & Wim Nijmeijer <wnijmeijer@coveo.com>
 */
interface SearchInterface
{

  public function getTracking();
  public function getSearchConfig();
  public function setFromQS($suggestions);

    /**
     * Execute Coveo search
     *
     * @param string $queryText
     * @return Result
     */
    public function execute($queryText = null);

    /**
     * Get last result
     *
     * @return Result
     */
    public function getResult();

    /**
     * Extract product ids and score from Coveo response
     *
     * @return array
     */
    public function getProducts();

    /**
     * Is search fallback enable
     *
     * @return bool
     */
    public function isFallbackEnable();

    /**
     * Register search collection
     *
     * @param ProductCollection $collection
     */
    public function registerSearchCollection($collection);

    /**
     * Get search collection
     *
     * @return ProductCollection $collection
     */
    public function getSearchCollection();

}
