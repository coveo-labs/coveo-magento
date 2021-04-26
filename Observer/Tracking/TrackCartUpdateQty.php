<?php declare(strict_types=1);

namespace Coveo\Search\Observer\Tracking;

use Coveo\Search\Api\Service\ConfigInterface;
use Coveo\Search\Api\Service\Config\AnalyticsConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Coveo\Search\Api\Service\LoggerInterface;
use Coveo\Search\Api\Service\TrackingInterface;

class TrackCartUpdateQty implements ObserverInterface
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
        if ($this->config->isTrackingEnabled() === false) {
            return;
        }

        $items = $observer->getCart()->getQuote()->getItems();
        $info = $observer->getInfo()->getData();

        foreach ($items as $item) {

            $product = $item->getProduct();
            $qtyFrom = $item->getQty();
            if (!isset($info[$item->getId()]) || !isset($info[$item->getId()]['qty'])) {
                $this->logger->warn('[cart tracking update] Invalid observer data: '.$item->getId().' not found in infos, tracking skipped', [
                    'info' => $info,
                    'item' => $item->getId()
                ]);
                continue;
            }
            $qtyTo = $info[$item->getId()]['qty'];
            $qtyDiff = round($qtyTo) - round($qtyFrom);

            $this->logger->info('[cart tracking update] Product "'. $item->getName() .'" change qty from ' . $qtyFrom . ' to ' . $qtyTo);

            if ($qtyDiff > 0) {
                $event = 'add';
            }else if($qtyDiff < 0){
                $event = 'remove';
            }else{
                $this->logger->warn('[cart tracking update] Product "'. $item->getName() .'" has no changes, qty delta is 0, tracking skipped');
                continue;
            }

            $productData = $this->tracking->getProductTrackingParams($product, 1, abs($qtyDiff));
            $this->logger->info('[cart tracking update] Final Price Item   : '.$product->getFinalPrice());
            $this->logger->info('[cart tracking update] Final Price Product: '.$productData['price']);
            $this->logger->info('[cart tracking update] Elaborated event '. $event .' for product "' . $productData['name'] . '" with qty ' . $productData['quantity']);

            $this->tracking->executeTrackingRequest([
                't' => 'event',
                'pr1id' => $productData['id'],
                'pr1nm' => $productData['name'],
                'pr1ca' => $productData['category'],
                'pr1br' => $productData['brand'],
                'pr1pr' => $productData['price'],
                'pr1qt' => $productData['quantity'],
                /*'list' => 'coveo:search:'.$this->tracking->getSearchId(),*/
                'pa' => $event,
                'ec' => 'cart',
                'ea' => $event,
            ]);
        }
    }
}
