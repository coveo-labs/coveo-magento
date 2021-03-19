<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service\Indexer;

interface OperationInterface
{
    /**
     * @var array $ids
     * @return void
     */
    public function execute($ids = null);
}
