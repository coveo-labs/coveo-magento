<?php declare(strict_types=1);

namespace Coveo\Search\Observer\Tracking;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Coveo\Search\Api\Service\TrackingInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;



class Logout implements ObserverInterface
{
    protected $cookieManager;
    protected $cookieMetadataFactory;


    public function __construct(
      CookieManagerInterface $cookieManager,
      CookieMetadataFactory $cookieMetadataFactory
  ) {
      $this->cookieManager = $cookieManager;
      $this->cookieMetadataFactory = $cookieMetadataFactory;
  }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
      //Remove UID cookie
      $this->cookieManager->setPublicCookie(
        'coveo_UID',
        '',
        $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDurationOneYear()
            ->setPath('/'));
      
    }

}
