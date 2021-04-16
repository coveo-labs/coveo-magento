<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Coveo\Search\Plugin\CustomerData;

use \Magento\Framework\DataObject;
use Coveo\Search\Api\Service\SessionInterface;
use Magento\Customer\CustomerData\Customer\Interceptor as CustomerInterceptor;
use Coveo\Search\Api\Service\TrackingInterface;


class AddDataForCustomerSection
{
    /**
     * @var SessionInterface
     */
    protected $session;
    protected $tracking;

    /**
     * AddDataForCustomerSection constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(
        SessionInterface $session,
        TrackingInterface $track
    ) {
        $this->session = $session;
        $this->tracking = $track;
    }

    /**
     * Add data to customer
     *
     * @param CustomerInterceptor $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(CustomerInterceptor $subject, $result)
    {
        $result['customerId'] = $this->tracking->getCustomerId();
        $result['storeId'] = $this->getStoreId();
        return $result;
    }

    /**
     * Get Magento customer ID
     *
     * @return integer
     */
    public function getCustomerId()
    {
        return $this->tracking->getCustomerId();
        //return $this->session->getCustomerId();
    }

    /**
     * Get Magento storeId
     *
     * @return string
     */
    public function getStoreId()
    {
        return $this->session->getStoreId();
    }
}
