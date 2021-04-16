<?php declare(strict_types=1);

namespace Coveo\Search\Observer\Tracking;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Coveo\Search\Api\Service\TrackingInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


class Login implements ObserverInterface
{
    protected $cookieManager;
    protected $tracking;
    protected $cookieMetadataFactory;

    public function __construct(
      CookieManagerInterface $cookieManager,
      TrackingInterface $tracking,
      CookieMetadataFactory $cookieMetadataFactory
  ) {
      $this->cookieManager = $cookieManager;
      $this->tracking = $tracking;
      $this->cookieMetadataFactory = $cookieMetadataFactory;
  }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->cookieManager->setPublicCookie(
          'coveo_UID',
          $this->tracking->getSession()->getCustomerId(),
          $this->cookieMetadataFactory
              ->createPublicCookieMetadata()
              ->setDurationOneYear()
              ->setPath('/'));
    }

}
