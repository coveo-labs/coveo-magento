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
        AnalyticsConfigInterface $analyticsConfig
    ) {
        $this->logger = $logger;
        $this->tracking = $tracking;
        $this->config = $config;
        $this->analyticsConfig = $analyticsConfig;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
      $this->logger->debug('[cart tracking add]');
      if ($this->config->isTrackingEnabled() === false) {
            return;
        }

        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $observer->getProduct();
        $productData = $this->tracking->getProductTrackingParams($product, 1, round($product->getCartQty()));

        $this->logger->info('[cart tracking add] Product "'. $productData['name'] .'" added to cart with qty ' . $productData['quantity']);

        $this->tracking->executeTrackingRequest([
            't' => 'event',
            'pid' => $productData['id'],
            'pr1id' => $productData['id'],
            'pr1nm' => $productData['name'],
            'pr1ca' => $productData['category'],
            'pr1br' => $productData['brand'],
            'pr1pr' => $productData['price'],
            'pr1qt' => $productData['quantity'],
            'pa' => 'add',
            'ec' => 'cart',
            'ea' => 'add',
        ]);
    }
}
