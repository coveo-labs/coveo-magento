<?php declare(strict_types=1);
namespace Coveo\Search\Model\Adminhtml\System\Config\Source;

class ApiVersions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray()
    {
        return [
            ['value' => '3', 'label' => 'v3'],
        ];
    }
}
