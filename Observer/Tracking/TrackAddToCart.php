<?php declare(strict_types=1);

namespace Coveo\Search\Observer\Tracking;

use Coveo\Search\Api\Service\ConfigInterface;
use Coveo\Search\Api\Service\Config\AnalyticsConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Coveo\Search\Api\Service\LoggerInterface;
use Coveo\Search\Api\Service\TrackingInterface;

class TrackAddToCart implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AnalyticsConfigInterface
     */
    protected $analyticsConfig;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var TrackingInterface
     */
    protected $tracking;
    protected $request;

    /**
     * @param LoggerInterface $logger
     * @param TrackingInterface $tracking
     * @param ConfigInterface $config
     * @param AnalyticsConfigInterface $analyticsConfig
     */
    public function __construct(
        LoggerInterface $logger,
        TrackingInterface $tracking,
        ConfigInterface $config,
        AnalyticsConfigInterface $analyticsConfig,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->logger = $logger;
        $this->tracking = $tracking;
        $this->config = $config;
        $this->analyticsConfig = $analyticsConfig;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
      //The request now contains the searchId to use
      $searchid = '';//$this->tracking->getSearchId();
      $position = '';
      if (isset($this->request->getParams()['recAdd'])) {
        $searchid = $this->request->getParams()['recAdd'];
      }
      if ($searchid!='') {
        $searchid = 'coveo:search:'.$searchid;
      }
      if (isset($this->request->getParams()['recAddPos'])) {
        $position = $this->request->getParams()['recAddPos']+1;
      }
      $this->logger->debug('[cart tracking add] searchid='.$searchid);
      $this->logger->debug('[cart tracking add] Request:');
      $this->logger->debug('[cart tracking add] pages='.print_r($this->request->getParams(), true));
      if ($this->config->isTrackingEnabled() === false) {
            return;
        }

        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $observer->getProduct();
        $productData = $this->tracking->getProductTrackingParams($product, $position, round($product->getCartQty()));
        $this->logger->info('[cart tracking update] Final Price Item   : '.$product->getFinalPrice());
        $this->logger->info('[cart tracking update] Final Price Product: '.$productData['price']);

        $this->logger->info('[cart tracking add] Product "'. $productData['name'] .'" added to cart with qty ' . $productData['quantity']);

        $this->tracking->executeTrackingRequest([
            't' => 'event',
            /*'pid' => $productData['id'],*/
            'pr1id' => $productData['id'],
            'pr1nm' => $productData['name'],
            'pr1ca' => $productData['category'],
            'pr1br' => $productData['brand'],
            'pr1pr' => $productData['price'],
            'pr1qt' => $productData['quantity'],
            'pr1ps' => $position,
            'pal' => $searchid,
            'pa' => 'add',
            'ec' => 'cart',
            'ea' => 'add',
        ]);
    }
}
