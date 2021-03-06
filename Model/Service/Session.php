<?php declare(strict_types=1);

namespace Coveo\Search\Model\Service;

use Coveo\Search\Api\Service\Config\AnalyticsConfigInterface;
use Coveo\Search\Api\Service\SessionInterface;
use Coveo\Search\Api\Service\TrackingInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Coveo\Search\SDK\ClientBuilder;

class Session implements SessionInterface
{
    /**
     * @var string
     */
    const COOKIE_SEARCHID = 'CoveoSearchId';
    /**
     * @var string
     */
    const COOKIE_VISITORID = 'coveo_visitorId';

    /**
     * @var string
     */
    const COOKIE_USERID = '_ta';
    const COOKIE_CUSTOMERID = 'coveo_UID';

    /**
     * @var string
     */
    const CLIENT_ID_PREFIX = 'TA.';

    /**
     * @var string
     */
    const SEARCH_ID_PREFIX = 'magento_';

    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var CustomerSession
     */
    protected $session;

    /**
     * @var AnalyticsConfigInterface
     */
    private $analyticsConfig;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * @var ClientBuilder
     */
    protected $clientBuilder;
    private $currentVisitor;

    /**
     * @param SessionManagerInterface $sessionManager
     * @param CustomerSession $session ,
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param AnalyticsConfigInterface $analyticsConfig
     * @param ClientBuilder $clientBuilder
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        CustomerSession $session,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        AnalyticsConfigInterface $analyticsConfig,
        ClientBuilder $clientBuilder
    ) {
        $this->sessionManager = $sessionManager;
        $this->session = $session;
        $this->analyticsConfig = $analyticsConfig;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->clientBuilder = $clientBuilder;

    }

    /**
     * @inheritdoc
     */
    public function setSearchId($value)
    {
        $this->cookieManager->setPublicCookie(
            self::COOKIE_SEARCHID,
            $value,
            $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setDurationOneYear()
                //->setHttpOnly(true)
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain())
        );
    }

    /**
     * @inheritdoc
     */
    public function setVisitorId($value)
    {
        $this->cookieManager->setPublicCookie(
            self::COOKIE_VISITORID,
            $value,
            $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setDurationOneYear()
                //->setHttpOnly(true)
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain())
        );
    }

    
    /**
     * @inheritdoc
     */
    public function getSearchId()
    {
        $searchId = $this->cookieManager->getCookie(self::COOKIE_SEARCHID);
        if($searchId === null || trim($searchId) === ''){
            //$uuid = $this->clientBuilder->build()->getUuid();
            //$searchId = substr(self::SEARCH_ID_PREFIX.$uuid, 0, 36);
            $searchId = '';
        }
        return $searchId;
    }

    
    /**
     * @inheritdoc
     */
    public function getVisitorId()
    {
        
        $visId = $this->cookieManager->getCookie(self::COOKIE_VISITORID);
        if($visId === null || trim($visId) === ''){
          //create a new one
          /*if ($this->currentVisitor===null) {
            $visId = $this->clientBuilder->build()->getUuid();
            $session->setVisitorId($visId);
            $this->currentVisitor = $visId;
          }*/
          return '';
        }
        return $visId;
    }

     /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        $storeId = $this->analyticsConfig->getStoreId();
        return $storeId;
    }

    /**
     * @inheritdoc
     */
    public function getClientId()
    {
      return $this->getVisitorId();
        /*$cid = $this->cookieManager->getCookie(self::COOKIE_USERID);
        if ($cid === null || $cid === false || $cid === '') {
            $cid = $this->generateClientId();
            $domain = $this->analyticsConfig->getCookieDomain();
            $this->cookieManager->setPublicCookie(
                self::COOKIE_USERID,
                $cid,
                $this->cookieMetadataFactory
                    ->createPublicCookieMetadata()
                    ->setHttpOnly(false)
                    ->setDuration(63072000)
                    ->setPath('/')
                    ->setDomain($domain)
            );
        }

        return substr($cid ?? '', -36);*/
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
      if ($this->isLoggedIn()) {
        $cust = $this->cookieManager->getCookie(self::COOKIE_CUSTOMERID);
        if ($cust === null || $cust === false || $cust === '') {
            $cust = $this->session->getCustomer();
            if ($cust === null){
              return null;
            }
            //Will be handled by logout/login events
            /*$this->cookieManager->setPublicCookie(
              self::COOKIE_CUSTOMERID,
              $cust->getId(),
              $this->cookieMetadataFactory
                  ->createPublicCookieMetadata()
                  ->setDurationOneYear()
                  //->setHttpOnly(true)
                  ->setPath($this->sessionManager->getCookiePath())
                  ->setDomain($this->sessionManager->getCookieDomain())
          
            );*/
            $cust = $cust->getId();
        }
       return $cust;
      }
      else return '';
    }

    /**
     * @inheritdoc
     */
    public function isLoggedIn()
    {
        return $this->session->isLoggedIn();
    }

    /**
     * Generate new client ID
     *
     * @return string
     */
    private function generateClientId()
    {
        $uuid = $this->clientBuilder->build()->getUuid();
        return self::CLIENT_ID_PREFIX.$uuid; //TODO: use constant
    }
}
