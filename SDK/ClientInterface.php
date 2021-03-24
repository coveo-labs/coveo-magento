<?php
namespace Coveo\Search\SDK;

use Coveo\Search\SDK\Index\Result as IndexResult;
use Coveo\Search\SDK\Search\Result as SearchResult;


/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it> & Wim Nijmeijer <wnijmeijer@coveo.com>
 */
interface ClientInterface
{
    /**
     * @param string $apiKey
     * @param string $version
     * @param string $apiBaseUrl
     * @param string $language
     * @param string $storeCode
     */
    public function __construct($apiKey, $version, $apiBaseUrl, $language, $storeCode,$logger = null, $reportSender = null, $sessionStorage = null, $agent = 'Unknown',$pipeline='',$useRecommendations=null);

    /**
     * Perform a search
     *
     * @param string $query
     * @param boolean $typoCorrection
     * @param array $extraParams
     * @param boolean $enriched
     * @param int $page
     * @param int $limit
     * @return SearchResult
     * @throws Exception
     */
    public function search($query, $typoCorrection = true, $extraParams = array(), $enriched = false, $page = null, $limit = null, $hub=null, $tab=null,$tracking=null, $limitPage=null, $fromQS=null, $qs=null, $storeId=null);

    /**
     * Send data to index
     *
     * @param mixed $csvContent
     * @param array $params
     * @return IndexResult
     * @throws Exception
     */
    public function index($csvContent, $params);

    /**
     * Generate uuid
     *
     * @return string
     */
    public function getUuid();
}
