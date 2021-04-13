<?php
namespace Coveo\Search\Block\Tracking;

use Coveo\Search\Api\Block\ScriptInterface;

class PageView extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'coveo-tracking-pageview';

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }

        /**
     * @inheritdoc
     */
    public function _toHtml()
    {
      
        if ($this->getRequest()->getFullActionName()=='catalog_product_view') {
            return '';
        }
        return parent::_toHtml();
    }

}
