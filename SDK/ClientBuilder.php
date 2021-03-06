<?php
namespace Coveo\Search\SDK;

use Coveo\Search\SDK\Log\LoggerInterface;
use Coveo\Search\SDK\Log\SendInterface;
use Coveo\Search\SDK\Storage\SessionInterface;


/**
 * @category Coveo
 * @package  Coveo
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it> & Wim Nijmeijer <wnijmeijer@coveo.com>
 */
final class ClientBuilder
{
    const DEFAULT_VERSION = '3';
    const DEFAULT_BASE_URL = 'https://v3dev.api./';
    const DEFAULT_LANGUAGE = 'en-us';

    /**
     * Base url for API calls
     *
     * @var string
     */
    private $_baseUrl;

    /**
     * API key
     *
     * @var string
     */
    private $_apiKey;

    /**
     * Store language
     *
     * @var string
     */
    private $_language;

    private $_useRecommendations;

    /**
     * Store code
     *
     * @var string
     */
    private $_storeCode;

    /**
     * API version
     *
     * @var null|string
     */
    private $_version;

    /**
     * @var SendInterface
     */
    private $_reportSender;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var SessionInterface
     */
    private $_sessionStorage;

    /**
     * @var string
     */
    private $_agent = 'Unknown';

    /**
     * @var string
     */
    private $_pipeline;

    /**
     * ClientBuilder constructor.
     */
    public function __construct()
    {
        $this->_version = self::DEFAULT_VERSION;
        $this->_baseUrl = self::DEFAULT_BASE_URL;
        $this->_language = self::DEFAULT_LANGUAGE;
        $this->_pipeline = '';
    }

     /**
     * @param string $pipeline
     * @return ClientBuilder
     */
    public function withPipeline($pipeline)
    {
        $this->_pipeline = $pipeline;
        return $this;
    }

    /**
     * @param string $apiKey
     * @return ClientBuilder
     */
    public function withApiKey($apiKey)
    {
        $this->_apiKey = $apiKey;
        return $this;
    }

    /**
     * @param string $version
     * @return ClientBuilder
     */
    public function withApiVersion($version)
    {
        $this->_version = $version;
        return $this;
    }

    /**
     * @param string $apiBaseUrl
     * @return ClientBuilder
     */
    public function withApiBaseUrl($apiBaseUrl)
    {
        $this->_baseUrl = $apiBaseUrl;
        return $this;
    }

    /**
     * @param string $language
     * @return ClientBuilder
     */
    public function withLanguage($language)
    {
        $this->_language = strtolower($language);
        return $this;
    }

    /**
     * @param string $storeCode
     * @return ClientBuilder
     */
    public function withStoreCode($storeCode)
    {
        $this->_storeCode = strtolower($storeCode);
        return $this;
    }
    
    /**
     * @param LoggerInterface $logger
     * @return ClientBuilder
     */
    public function withLogger(LoggerInterface $logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * @param SendInterface $reportSender
     * @return ClientBuilder
     */
    public function withReportSender(SendInterface $reportSender)
    {
        $this->_reportSender = $reportSender;
        return $this;
    }

    /**
     * @param SessionInterface $sessionStorage
     * @return ClientBuilder
     */
    public function withSessionStorage(SessionInterface $sessionStorage)
    {
        $this->_sessionStorage = $sessionStorage;
        return $this;
    }

    /**
     * @param string $agent
     * @return ClientBuilder
     */
    public function withAgent($agent)
    {
        $this->_agent = $agent;
        return $this;
    }

     /**
     * @param string $agent
     * @return ClientBuilder
     */
    public function withRecommendations($use)
    {
        $this->_useRecommendations = $use;
        return $this;
    }


    /**
     * @return Client
     */
    public function build()
    {
        $instance = new Client(
            $this->_apiKey,
            $this->_version,
            $this->_baseUrl,
            $this->_language,
            $this->_storeCode,
            $this->_logger,
            $this->_reportSender,
            $this->_sessionStorage,
            $this->_agent,
            $this->_pipeline,
            $this->_useRecommendations
        );

        // Reset builder instance
        $this->_version = self::DEFAULT_VERSION;
        $this->_baseUrl = self::DEFAULT_BASE_URL;
        $this->_language = self::DEFAULT_LANGUAGE;
        $this->_apiKey = null;
        $this->_version = null;
        $this->_baseUrl = null;
        $this->_language = null;
        $this->_storeCode = null;
        $this->_logger = null;
        $this->_reportSender = null;
        $this->_sessionStorage = null;
        $this->_agent =  null;
        $this->_pipeline = '';
        $this->_useRecommendations = false;

        return $instance;
    }
}