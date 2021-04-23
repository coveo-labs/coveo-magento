<?php declare(strict_types=1);

namespace Coveo\Search\Model\Service;

use Coveo\Search\Api\Service\Config\AnalyticsConfigInterface;
use Coveo\Search\Api\Service\ConfigInterface;
use Coveo\Search\Api\Service\LoggerInterface;
use Coveo\Search\Api\Service\SessionInterface;
use Coveo\Search\Api\Service\TrackingInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\HTTP\Header;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Coveo\Search\SDK\Client;
use Coveo\Search\SDK\ClientBuilder;
use Coveo\Search\SDK\Exception;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Tracking implements TrackingInterface
{
    /**
     * Tracking request timeout
     * @var integer
     */
    const TRACKING_REQUEST_TIMEOUT = 1000;
    

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var RequestHttp
     */
    protected $request;

    /**
     * @var Header
     */
    protected $httpHeader;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var AnalyticsConfigInterface
     */
    private $analyticsConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var array
     */
    protected $categories;

    /**
     * @var ClientBuilder
     */
    protected $clientBuilder;
    protected $cookieManager;

    /**
     * Config constructor.
     *
     * @param LoggerInterface $logger
     * @param ProductMetadataInterface $productMetadata
     * @param SessionInterface $session
     * @param RequestHttp $request
     * @param Header $httpHeader
     * @param UrlInterface $url
     * @param ConfigInterface $config
     * @param AnalyticsConfigInterface $analyticsConfig
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param ClientBuilder $clientBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        ProductMetadataInterface $productMetadata,
        SessionInterface $session,
        RequestHttp $request,
        Header $httpHeader,
        UrlInterface $url,
        ConfigInterface $config,
        AnalyticsConfigInterface $analyticsConfig,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        CategoryCollectionFactory $categoryCollectionFactory,
        ClientBuilder $clientBuilder,
        CookieManagerInterface $cookieManager
    ) {
        $this->logger = $logger;
        $this->productMetadata = $productMetadata;
        $this->session = $session;
        $this->request = $request;
        $this->httpHeader = $httpHeader;
        $this->url = $url;
        $this->config = $config;
        $this->analyticsConfig = $analyticsConfig;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->clientBuilder = $clientBuilder;
        $this->cookieManager = $cookieManager;
    }

    /**
     * @inheritdoc
     */
    public function getModuleVersion()
    {
        $version = 'undefined';
        try {
            $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
            $vendorDir = dirname(dirname($reflection->getFileName()));
            $packages = json_decode(file_get_contents($vendorDir . '/composer/installed.json'), true);
            foreach ($packages as $package) {
                if ($package['name'] === 'coveo/magento-2-search') {
                    $version = $package['version'];
                    break;
                }
            }
        } catch (\Exception $e) {
            $version = 'error: ' . $e->getMessage();
        }
        return $version;
    }

    public function getSession(){
      return $this->session;
    }

    public function setSession($session) {
      $this->session = $session;
    }

    /**
     * @inheritdoc
     */
    public function getPHPVersion()
    {
        return PHP_VERSION;
    }

    /**
     * @inheritdoc
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * @inheritdoc
     */
    public function getApiAgent()
    {
        return implode(' ', [
            'PHP/' . $this->getPHPVersion(),
            'Magento/' . $this->getMagentoVersion(),
            'Coveo/' . $this->getModuleVersion(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId() {
      if ($this->session->isLoggedIn()) {
        $cid=$this->cookieManager->getCookie('coveo_UID');
        return $cid;
      }
      return '';
    }

    /**
     * @inheritdoc
     */
    public function getSearchId() {
     
       return $this->session->getSearchId();
  }

    /**
     * @inheritdoc
     */
    public function getProfilingParams($override = null)
    {
        $params = [
            'uip' => $this->getRemoteAddr(),
            'ua' => $this->getUserAgent(),
            'cid' => $this->session->getClientId(),
            'dr' => $this->getLastPage(),
            'dl' => $this->getCurrentPage(),
            'tm' => round(microtime(true) * 1000)
        ];

        if ($this->analyticsConfig->isUserIdTrackingEnable() && $this->session->isLoggedIn()) {
            $params['uid'] = $this->session->getCustomerId();
        }

        if ($override !== null && is_array($override)) {
            $params = array_merge($params, $override);
        }

        return $params;
    }

    /**
     * @inheritdoc
     */
    public function getProductTrackingParams($product, $position = 0, $quantity = 1)
    {
        $trackingProductParams = [
            'id' => $product->getSku(),
            'name' => $product->getName(),
            'brand' => $product->getManufacturer(),
            'price' => $product->getFinalPrice(),
            'quantity' => $quantity,
            'position' => ($position),
        ];
        //Add current customer into it
        $trackingProductParams['uid']=$this->getCustomerId();

        $categoryIds = $product->getCategoryIds();
        if ($categoryIds !== null && count($categoryIds) > 0) {
            /*if (!isset($this->categories[$categoryIds[0]])) {
                $this->loadCategory($categoryIds[0]);
            }*/
            $pathSize  = count($categoryIds);
            //$this->logger->info('[getProductTrackingParams] No of Categories : '.$pathSize);
            $path = array();
                for ($i = 1; $i < $pathSize; $i++) {
                    $path[] = $this->loadCategory($categoryIds[$i])->getName();
                }
                
            $trackingProductParams['category'] = implode('/', $path);//$this->categories[$categoryIds[0]]->getName();
        } else {
            $trackingProductParams['category'] = null;
        }
        //$this->logger->info('[getProductTrackingParams] Final Price Item : '.$product->getFinalPrice());
        //$this->logger->info('[getProductTrackingParams] Category         : '.$trackingProductParams['category']);

        return $trackingProductParams;
    }

    /**
     * @inheritdoc
     */
    public function loadCategory($id)
    {
        $categoriesCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addFieldToFilter('entity_id', $id);
        return $category = $categoriesCollection->getFirstItem();
        //$this->categories[$category->getId()] = $category;
    }

    /**
     * @inheritdoc
     */
    public function loadCategories($ids)
    {
        $categoriesCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addFieldToFilter('entity_id', array_map(function ($id) {
                return (string) $id; // fix wrong interpolation with 0
            }, $ids));

        $this->categories = [];
        foreach ($categoriesCollection as $category) {
            $this->categories[$category->getId()] = $category;
        }
    }

    /**
     * @inheritdoc
     */
    public function getOrderTrackingParams($order)
    {
        return [
            'id' => $order->getIncrementId(),
            'shipping' => $order->getShippingAmount(),
            'coupon' => $order->getCouponCode(),
            'tax' => $order->getTaxAmount(),
            'revenue' => $order->getGrandTotal(),
        ];
    }

    private function getRemoteAddrFix() {
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if ($ip !== null && strpos($ip, ',') !== false) {
          $ipP = explode(',', $ip);
          $ip = $ipP[0];
      }
        return $ip;
    }
    /**
     * @inheritdoc
     */
    public function getRemoteAddr()
    {
      $remoteAddr = $this->getRemoteAddrFix();
        /*$remoteAddr = $this->request->getClientIp(true);
        if ($remoteAddr !== null && strpos($remoteAddr, ',') !== false) {
            $remoteAddrParts = explode(',', $remoteAddr);
            $remoteAddr = $remoteAddrParts[0];
        }
        if ($remoteAddr=='::1') {
          $remoteAddr='127.0.0.1';
        }*/
        return $remoteAddr;
    }

    /**
     * @inheritdoc
     */
    public function getUserAgent()
    {
        $userAgent = $this->httpHeader->getHttpUserAgent();
        if ($userAgent === null) {
            $userAgent = 'unknown';
        }
        return $userAgent;
    }

    /**
     * @inheritdoc
     */
    public function getLastPage()
    {
        return $this->httpHeader->getHttpReferer();
    }

    /**
     * @inheritdoc
     */
    public function getCurrentPage()
    {
        return $this->url->getCurrentUrl();
    }

    /**
     * @inheritdoc
     */
    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    public function getAnalytics()
    {
        $myana=[/*"userIp"=> $this->getRemoteAddr(),*/
        "userAgent"=>$this->getUserAgent(),
        /*'pageId'=>$this->getUuid(),*/
        'clientId'=>$this->session->getVisitorId(),
        'documentReferrer'=>$this->getLastPage(),
        'documentLocation'=>$this->getCurrentPage()];
        return $myana;
  
    
    }
    /**
     * @inheritdoc
     */
    public function executeTrackingRequest($params)
    {
      //Check for visitorId
      $visitorId=$this->session->getVisitorId();
      if (isset($params['visitorId']) && $params['visitorId']!=null){
         $visitorId = $params['visitorId'];
         unset($params['visitorId']);
      }
        $profilingParams = $this->getProfilingParams();
        $myana=["userIp"=> $this->getRemoteAddr(),
          "userAgent"=>$this->getUserAgent(),
          /*'pageId'=>$this->getUuid(),*/
          'clientId'=>$visitorId,
          'documentReferrer'=>$this->getLastPage(),
          'documentLocation'=>$this->getCurrentPage()
      ];
        $params = array_merge([
            'z' => $this->getUuid(),
            "uip"=> $this->getRemoteAddr(),
            /*'referrer' => $this->getLastPage(),*/
            /*'analytics' => $myana,*/
            /*'tid' => $this->analyticsConfig->getKey(),*/
            'cid' => $visitorId,
            /*'pid' => $this->getUuid(),*/
            'v' => $this->analyticsConfig->getAPIVersion(),
            'cu' => $this->getCurrencyCode(),
            'context_store_id'=> $this->config->getStoreCode(),
         'context_website' => $this->config->getLanguage(),
         'context_version' => $this->getApiAgent(),
            'ul' => $this->config->getLanguage()
            //,
            //'context' => '{"store_id":'.$this->config->getStoreCode().',"website":"'.$this->config->getLanguage().'"}'
        ], $profilingParams, $params);
        $params['uid']='anonymous';
        if ($this->analyticsConfig->isUserIdTrackingEnable() && $this->session->isLoggedIn()) {
            if ($this->session->getCustomerId()!==''){
              $params['uid'] = $this->session->getCustomerId();
            };
        }
        //$extraParams = array('org='.$this->config->getApiOrg(),'visitor='.$this->session->getVisitorId());
        $client = $this->getClient();
        try {
            $client->doRequest('collect', Client::HTTP_METHOD_POST, $params, self::TRACKING_REQUEST_TIMEOUT, true, null, true);
        } catch (Exception $e) {
            $this->logger->logException($e);
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function executeTrackingSearchRequest($params)
    {
        $custom = [ 'context_store_id'=> $this->config->getStoreCode(),
         'context_website' => $this->config->getLanguage(),
        'context_version' => $this->getApiAgent()];
        //Check if we have qs in parameters
        if (isset($params['qs']) && $params['qs']!=null){
           $custom['suggestions']=str_replace(',',';',$params['qs']);
        }
        unset($params['qs']);
        //Check for visitorId
        $visitorId=$this->session->getVisitorId();
        if (isset($params['visitorId']) && $params['visitorId']!=null){
           $visitorId = $params['visitorId'];
           unset($params['visitorId']);
        }

        $myana=["userIp"=> $this->getRemoteAddr(),
          "userAgent"=>$this->getUserAgent(),
          /*'pageId'=>$this->getUuid(),*/
          'clientId'=>$visitorId,
          'documentReferrer'=>$this->getLastPage(),
          'documentLocation'=>$this->getCurrentPage()
      ];
        $profilingParams = ['uip' => $this->getRemoteAddr(),
        'referrer' => $this->getLastPage(),
        'userAgent' => $this->getUserAgent(),
        'clientId' => $visitorId,
        'originLevel3' => $this->getLastPage(),
        'analytics' => $myana,
        'mobile' => false,
        'language' => $this->config->getLanguage(),
        "actionCause"=> "searchFromLink",
        "actionType"=> "interface",
        "originContext"=> "Search",
        "customData"=> $custom,
        'tm' => round(microtime(true) * 1000)
         ];
         $profilingParams['uid']='anonymous';
         $profilingParams['anonymous']="true";
         $username="anonymous";
         $userDisplayName="anonymous";
        if ($this->analyticsConfig->isUserIdTrackingEnable() && $this->session->isLoggedIn()) {
            if ($this->session->getCustomerId()!==''){
              $profilingParams['uid'] = $this->session->getCustomerId();
              $profilingParams['anonymous']="false";
              $username= $this->session->getCustomerId();
              $userDisplayName=$username;
            };
        }
        $profilingParams['username']=$username;
        $profilingParams['userDisplayName']=$userDisplayName;
        $params = array_merge([
            'z' => $this->getUuid(),
            /*'tid' => $this->analyticsConfig->getKey(),*/
            'v' => $this->analyticsConfig->getAPIVersion()
        ], $profilingParams, $params);
        $extraParams = array('org='.$this->config->getApiOrg(),'visitor='.$visitorId);
        //$this->logger->debug(json_encode($params));
        $this->logger->debug(json_encode($extraParams));
        $client = $this->getClient();
        $returnValue = '';
        try {
            $returnValue=$client->doRequest('searches', Client::HTTP_METHOD_POST, $params, self::TRACKING_REQUEST_TIMEOUT, true,$extraParams);
        } catch (Exception $e) {
            $this->logger->logException($e);
            return false;
        }
        $this->logger->debug('Analytics - Search Request Sent ');
        if ($returnValue!='') {
          $this->session->setVisitorId($returnValue);
        }
        return true;
    }

    /**
     * Generate new client ID
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->clientBuilder->build()->getUuid();
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
            ->withLanguage($this->config->getLanguage())
            ->withApiBaseUrl($this->analyticsConfig->getAPIEndpoint())
            ->withAgent($this->getApiAgent())
            ->withLogger($this->logger)
            ->build();
    }
}
