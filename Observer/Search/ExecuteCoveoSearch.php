<?php declare(strict_types=1);

namespace Coveo\Search\Observer\Search;

use Coveo\Search\Api\Service\Search\UrlRewriteSwitcherInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ActionFlag;
use Coveo\Search\Api\Service\ConfigInterface;
use Coveo\Search\Api\Service\LoggerInterface;
use Coveo\Search\Api\Service\SearchInterface;
use Coveo\Search\Api\Service\SessionInterface;
use Coveo\Search\SDK\Search\Result;

class ExecuteCoveoSearch implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SearchInterface
     */
    protected $search;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @var UrlRewriteSwitcherInterface
     */
    protected $urlRewriteSwitcher;

    /**
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     * @param SearchInterface $search
     * @param ActionFlag $actionFlag
     * @param UrlRewriteSwitcherInterface $urlRewriteSwitcher
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigInterface $config,
        SearchInterface $search,
        ActionFlag $actionFlag,
        UrlRewriteSwitcherInterface $urlRewriteSwitcher
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->search = $search;
        $this->actionFlag = $actionFlag;
        $this->urlRewriteSwitcher = $urlRewriteSwitcher;
    }

    /**
     * Execute Coveo search
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isSearchEnabled() !== true) {
            $this->logger->debug('[catalog search result observer] Coveo search is disabled, skip logic');
            return;
        }

        $this->logger->debug('[catalog search result observer] Executing search..');

        //Check if it is from a QuerySuggest
        $fromSuggest = false;
        if (isset($_REQUEST['FromSuggest']) && $_REQUEST['FromSuggest'] == true) {
          $fromSuggest = true;
          $this->search->setFromQS();
          $this->logger->debug('[catalog search result observer] Executing search from Querysuggest..');
        }
        // Do search
        /** @var Result $result */
        $result = $this->search->execute();

        if (!$result->isValid()) {
            $this->logger->debug('[catalog search result observer] Search is not valid, skip logic');
            return;
        }

        // Check for redirect
        $redirect = $result->getRedirect();
        if (!$redirect) {
            $this->logger->debug('[catalog search result observer] Search has no redirect, skip logic');
            return;
        }

        $this->logger->debug("[catalog search result observer] Performing redirect to '$redirect'");

        // Check auto store redirect
        $redirect = $this->urlRewriteSwitcher->elaborate($redirect);

        //We need to sent a Tracking request
        $tracking = $this->search->getTracking();
        $hub = $this->search->getSearchConfig()->getHub();
        $tab = $this->search->getSearchConfig()->getTab();
        $custom = ['query'=>$result->getOriginalSearchString(),'context_store_id'=> $this->config->getStoreCode(),
        'context_website' => $this->config->getLanguage()];
        $tracking->executeTrackingSearchRequest([
          "actionCause"=> "queryPipelineTriggers",
          "actionType"=> "triggers",
          "originLevel1"=> $hub,
          "originLevel2"=> $tab,
          'queryText' => $result->getOriginalSearchString(),
          "responseTime" => $result->getTotalTime(),
          'searchQueryUid' => $result->getSearchId(),
          'customData' => $custom,
          'queryPipeline' => isset($this->_pipeline)?$this->_pipeline:'',
        ]);

        $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        /** @var \Magento\CatalogSearch\Controller\Result\Index\Interceptor $controllerAction */
        $controllerAction = $observer->getControllerAction();
        $response = $controllerAction->getResponse();
        $response->setRedirect($redirect);
    }
}
