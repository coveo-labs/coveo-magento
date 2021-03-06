<?php
namespace Coveo\Search\Block\Suggestion;

use Coveo\Search\Api\Block\ScriptInterface;
use Coveo\Search\Api\Service\Config\SuggestionConfigInterface;
use Magento\Framework\View\Element\Template\Context;

class Library extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'coveo-suggestion-library';

    /**
     * @var SuggestionConfigInterface
     */
    protected $suggestionConfig;

    /**
     * Library constructor.
     *
     * @param Context $context
     * @param SuggestionConfigInterface $suggestionConfig
     */
    public function __construct(
        Context $context,
        SuggestionConfigInterface $suggestionConfig
    ) {
        parent::__construct($context);
        $this->suggestionConfig = $suggestionConfig;
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }

    /**
     * Get library endpoint
     *
     * @return string|null
     */
    public function getLibraryEndpoint()
    {
        return $this->suggestionConfig->getLibraryEndpoint();
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        if ($this->suggestionConfig->isLibraryIncluded() === false) {
            return '';
        }
        return parent::_toHtml();
    }
}
