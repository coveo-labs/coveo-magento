<?php
namespace Coveo\Search\Block\Suggestion;

use Coveo\Search\Api\Block\ScriptInterface;
use Coveo\Search\Api\Service\Config\SuggestionConfigInterface;
use Magento\Framework\View\Element\Template\Context;

class LibraryInit extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'coveo-suggestion-library-init';

    /**
     * @var SuggestionConfigInterface
     */
    protected $suggestionConfig;

    /**
     * LibraryInit constructor.
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
     * Get input selector
     *
     * @return string|null
     */
    public function getInputSelector()
    {
        return $this->suggestionConfig->getInputSelector();
    }

    /**
     * Get init parameters
     *
     * @return array
     */
    public function getInitParams()
    {
        return $this->suggestionConfig->getInitParams();
    }

    /**
     * Get on select value
     *
     * @return string
     */
    public function getOnSelectCallbackValue()
    {
        return $this->suggestionConfig->getOnSelectCallbackValue();
    }
}
