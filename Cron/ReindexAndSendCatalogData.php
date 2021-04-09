<?php

declare(strict_types=1);

namespace Coveo\Search\Cron;

use Coveo\Search\Api\Service\Indexer\DataSenderInterface;
use Coveo\Search\Api\Service\Indexer\CatalogInterface;

class ReindexAndSendCatalogData
{
    /**
     * @param CatalogInterface $dataSender
     */
    private $catalog;

    /**
     * @param DataSenderInterface $dataSender
     */
    private $dataSender;

    /**
     * @param CatalogInterface $catalog
     * @param DataSenderInterface $dataSender
     */
    public function __construct(
        CatalogInterface $catalog,
        DataSenderInterface $dataSender
    ) {
        $this->catalog = $catalog;
        $this->dataSender = $dataSender;
    }

    public function execute()
    {
        $this->catalog->execute();
        $this->dataSender->sendCatalog();
    }
}
