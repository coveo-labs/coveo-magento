<?php declare(strict_types=1);

namespace Coveo\Search\Model\Service;

use Coveo\Search\Api\Service\Config\SearchConfigInterface;
use Coveo\Search\Api\Service\ConfigInterface;
use Coveo\Search\Api\Service\LoggerInterface;
use Coveo\Search\Api\Service\Search\RequestParserInterface;
use Coveo\Search\Api\Service\SearchInterface;
use Coveo\Search\Api\Service\TrackingInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductCollection;
use Coveo\Search\SDK\Exception;
use Coveo\Search\SDK\ClientBuilder;
use Coveo\Search\SDK\Search\ResultFactory;
use Coveo\Search\SDK\Search\Result;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Search implements SearchInterface
{
    const SEARCH_RESULT_REGISTRY_KEY = 'coveo_search_response';
    const SEARCH_COLLECTION_REGISTRY_KEY = 'coveo_search_collection';
/**
     * @var string
     */
    const COOKIE_VISITORID = 'coveo_visitorId';
    const COOKIE_SEARCHID = 'coveo_searchUid';

    const PARAM_FILTER = 'aq';
    const PARAM_ORDER = 'sortCriteria';
    const PARAM_PARENT_SEARCH_ID = 'parentSearchId';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SearchConfigInterface
     */
    protected $searchConfig;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ClientBuilder
     */
    protected $clientBuilder;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var RequestParserInterface
     */
    protected $requestParser;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var CookieManagerInterface
     */
    private $cookie;

    private $fromQuerySuggest;
    private $qs;
    private $onMain;

    /**
     * Search constructor.
     *
     * @param ConfigInterface $config
     * @param SearchConfigInterface $searchConfig
     * @param TrackingInterface $tracking
     * @param LoggerInterface $logger
     * @param ResourceConnection $resourceConnection
     * @param Registry $registry
     * @param RequestParserInterface $requestParser
     * @param ClientBuilder $clientBuilder
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        ConfigInterface $config,
        SearchConfigInterface $searchConfig,
        TrackingInterface $tracking,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection,
        Registry $registry,
        RequestParserInterface $requestParser,
        ClientBuilder $clientBuilder,
        ResultFactory $resultFactory
    )
    {
        $this->config = $config;
        $this->searchConfig = $searchConfig;
        $this->tracking = $tracking;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
        $this->registry = $registry;
        $this->requestParser = $requestParser;
        $this->clientBuilder = $clientBuilder;
        $this->resultFactory = $resultFactory;
        $this->fromQuerySuggest = false;
        $this->qs = [];
        $this->onMain = false;
    }

    public function getTracking(){
      return $this->tracking;
    }

    public function getSearchConfig(){
      return $this->searchConfig;
    }

    public function setFromQS($suggestions){
      $this->fromQuerySuggest = true;
      $this->qs = $suggestions;
    }

    public function setOnMain($val){
      $this->onMain = $val;
    }

    /**
     * @inheritdoc
     */
    public function execute($queryText = null)
    {
        $result = $this->getResult();

        if ($result !== null) {
            $this->logger->debug('[search] Search already executed, returning result');
            return $result;
        }

        if ($queryText === null) {
            $queryText = $this->requestParser->getQueryText();
        }

        //Check if we are on the main result page
        $onMain = false;
        if ($this->tracking!=null) {
          if (str_contains($this->tracking->getLastPage(),'q=')){
            $onMain=true;
            $this->setOnMain(true);
          }
        }

        $this->logger->debug("[search] Searching for '$queryText'..");

        $typoCorrection = $this->requestParser->isTypoCorrectedSearch();
        $parentSearchId = null;
        if ($typoCorrection === false) {
            $parentSearchId = $this->requestParser->getParentSearchId();
            $this->logger->debug("[search] Set parent search id to '$parentSearchId'");
        }

        // Do search
        try {
            $params = $this->tracking->getProfilingParams();

            if ($parentSearchId !== null) {
                $params[self::PARAM_PARENT_SEARCH_ID] = $parentSearchId;
            }

            if (true) {
                $filter = $this->requestParser->getFilterParam();
                if ($filter!==null && $filter!=='') {
                  $params[self::PARAM_FILTER] = $filter;
                  $this->logger->debug('[search] Executing with filter: '.self::PARAM_FILTER.'=>'.$params[self::PARAM_FILTER]);
                }
            }
            if (true) {
                $order = $this->requestParser->getOrderParam();
                if ($order!==null && $order!=='') {
                  $params[self::PARAM_ORDER] = $order;
                  $this->logger->debug('[search] Executing with order: '.self::PARAM_ORDER.'=>'.$params[self::PARAM_ORDER]);
                }
            }

            $limit = $this->searchConfig->getDefaultLimit();
            $hub = $this->searchConfig->getHub();
            $tab = $this->searchConfig->getTab();
            $limitPage = $this->requestParser->getLimit();
            $pagenr = $this->requestParser->getPage();
            $params['referrer']=$params['dr'];

            $this->logger->debug('[search] Executing search..');
            //$this->logger->debug('[search] Executing search.. visitor: '.$this->cookie->getCookie(self::COOKIE_VISITORID));
            //$this->logger->debug('[search] Executing search.. searchId: '.$this->cookie->getCookie('CoveoSearchId'));
            
            //NOTE: at this point page and limit param are not used
            //public function search($query, $typoCorrection = true, $extraParams = array(), $enriched = false, $page = null, $limit = null, $hub=null, $tab=null, $tracking=null, $limitPage=null)
    
            $result = $this->getClient()->search(
                $queryText,
                $typoCorrection,
                $params,
                $this->searchConfig->isEnriched(),
                $pagenr,
                $limit,
                $hub,
                $tab,
                $this->tracking,
                $limitPage,
                $this->fromQuerySuggest,
                $this->qs,
                $this->config->getStoreId(),
                $this->onMain
            );
            
            $this->logger->debug('[search] Search executed');
            $this->logger->debug('[search] Time to execute: '.$result->getTotalTime());
        } catch (Exception $e) {
            $this->logger->logException($e);
            $result = $this->resultFactory->create();
        }

        $this->registerResult($result);
        //$this->logger->debug('[search] results: '.json_encode($result->getResults()));
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getResult()
    {
        // Check if class instance has result
        if (!$this->result) {
            $this->result = $this->registry->registry(self::SEARCH_RESULT_REGISTRY_KEY);
        }

        return $this->result;
    }

    /**
     * Set result
     *
     * @param Result $result
     */
    protected function registerResult($result)
    {
        $this->logger->debug('[search] Saving search result into registry..');
        $this->result = $result;

        // Store result into registry
        $this->registry->register(self::SEARCH_RESULT_REGISTRY_KEY, $this->result, true);
        $this->logger->debug('[search] Result saved');
    }

    /**
     * @inheritdoc
     */
    public function registerSearchCollection($collection)
    {
        $this->registry->register(self::SEARCH_COLLECTION_REGISTRY_KEY, $collection, true);
    }

    /**
     * @inheritdoc
     */
    public function getSearchCollection()
    {
        return $this->registry->registry(self::SEARCH_COLLECTION_REGISTRY_KEY);
    }

    /**
     * @inheritdoc
     */
    public function getProducts()
    {
        $this->logger->debug('[search] Searching products from result..');
        $products = [];

        if ($this->result !== null) {
          $this->logger->debug('[search] Searching products from result, result is there..');
          $skus = [];
            if ($this->searchConfig->isEnriched()) {
              $this->logger->debug('[search] Searching products from result, enriched..');
              $resultProducts = $this->result->getResults();
                foreach ($resultProducts as $product) {
                    /*if (!is_object($product)) {
                        $skus = $this->result->getResults();
                        break;
                    }*/
                    if (isset($product->raw->sku)){
                      $skus[] = $product->raw->sku;
                    }
                }
            } else {
              $this->logger->debug('[search] Searching products from result, NOT enriched..');
              $resultProducts = $this->result->getResults();
              foreach ($resultProducts as $product) {
                  /*if (!is_object($product)) {
                      $skus = $this->result->getResults();
                      break;
                  }*/
                  if (isset($product->raw->sku)){
                    $skus[] = $product->raw->sku;
                  }
              }
            }

            $i = 1;
            $productIds = $this->getIdsBySkus($skus);

            foreach ($skus as $sku) {
                if (isset($productIds[$sku])) {
                    $products[] = [
                        'sku' => $sku,
                        'product_id' => $productIds[$sku],
                        'relevance' => $i
                    ];
                }

                $i++;
            }
        }

        $this->logger->debug('[search] Found '.sizeof($products).' products');

        return $products;
    }

    /**
     * @inheritdoc
     */
    public function isFallbackEnable()
    {
        return $this->searchConfig->isFallbackEnable();
    }

    /**
     * Get products identifiers by skus
     *
     * @param array $skus
     * @return array
     */
    protected function getIdsBySkus($skus)
    {
        if (count($skus) === 0) {
            return [];
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_entity');

        $where = 'sku IN (';
        $bind = [];

        // Build the where clause with all the required placeholder for binding
        for ($i=0, $iMax = count($skus); $i < $iMax; $i++) {
            $bind[':sku' . $i] = $skus[$i];
        }

        $where .= implode(',', array_keys($bind)) . ')';

        $select = $connection->select()
            ->from($tableName, ['sku', 'entity_id'])
            ->where($where);

        return $connection->fetchPairs($select, $bind);
    }

    /**
     * Get Client
     *
     * @return \Coveo\SDK\Client
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getClient()
    {
        return $this->clientBuilder
            ->withApiKey($this->config->getApiKeySearch())
            ->withApiBaseUrl($this->config->getApiSearchUrl())
            ->withSessionStorage($this->tracking->getSession())
            ->withLanguage($this->config->getLanguage())
            ->withStoreCode($this->config->getStoreCode())
            ->withAgent($this->tracking->getApiAgent())
            ->withLogger($this->logger)
            ->build();
    }
}
