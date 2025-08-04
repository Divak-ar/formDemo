<?php

namespace EMP123\FormDemo\Block\Adminhtml\Demo\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SendEmailButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Send Email'),
            'class' => 'save secondary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'formdemo_demo_form.formdemo_demo_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'send_email' => true
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'sort_order' => 40,
        ];
    }
}
