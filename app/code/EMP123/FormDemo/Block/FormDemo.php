<?php

namespace EMP123\FormDemo\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class FormDemo extends Template
{
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('formdemo/index/submit');
    }

    /**
     * Get gender options
     *
     * @return array
     */
    public function getGenderOptions()
    {
        return [
            'male' => __('Male'),
            'female' => __('Female'),
            'other' => __('Other')
        ];
    }
}
