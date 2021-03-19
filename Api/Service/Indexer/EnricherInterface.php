<?php declare(strict_types=1);

namespace Coveo\Search\Api\Service\Indexer;

interface EnricherInterface
{
    /**
     * @var array $data
     * 
     * @return array
     */
    public function execute($data,$attributeCollection);
    
    /**
     * @return array
     */
    public function getEnrichedKeys();
}