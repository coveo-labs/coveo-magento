<?php declare(strict_types=1);

namespace Coveo\Search\Model\Service\Config;

use Coveo\Search\Api\Service\Config\SkinConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SkinConfig implements SkinConfigInterface
{
    const XML_PATH_SKIN_CUSTOM_CSS_ENABLE = 'coveo/skin_configuration/custom_css_enable';
    const XML_PATH_SKIN_CUSTOM_CSS = 'coveo/skin_configuration/custom_css';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function getCustomCss()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SKIN_CUSTOM_CSS);
    }

    /**
     * @inheritdoc
     */
    public function isCustomCssEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SKIN_CUSTOM_CSS_ENABLE);
    }

}
