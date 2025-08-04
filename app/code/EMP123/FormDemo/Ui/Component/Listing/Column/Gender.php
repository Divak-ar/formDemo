<?php

namespace EMP123\FormDemo\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;

class Gender implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'male', 'label' => __('Male')],
            ['value' => 'female', 'label' => __('Female')],
            ['value' => 'other', 'label' => __('Other')]
        ];
    }
}
