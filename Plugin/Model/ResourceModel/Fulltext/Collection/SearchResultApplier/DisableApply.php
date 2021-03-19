<?php declare(strict_types=1);

namespace Coveo\Search\Plugin\Model\ResourceModel\Fulltext\Collection\SearchResultApplier;

use Coveo\Search\Api\Service\ConfigInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplier;
use Magento\Framework\App\RequestInterface;

class DisableApply
{
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param ConfigInterface $config
     * @param RequestInterface $request
     */
    public function __construct(
        ConfigInterface $config,
        RequestInterface $request
    ) {
        $this->config = $config;
        $this->request = $request;
    }

    public function aroundApply(SearchResultApplier $subject, \Closure $next)
    {
        if ($this->isCoveoSearchDisabled() || $this->isNotSearchPage()) {
            $next();
        }
    }

    private function isCoveoSearchDisabled()
    {
        return $this->config->isSearchEnabled() !== true;
    }

    private function isNotSearchPage()
    {
        return $this->request->getFullActionName() !== 'catalogsearch_result_index';
    }
}
