<?php

namespace EMP123\FormDemo\Controller\Adminhtml\Demo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use EMP123\FormDemo\Model\FormDataFactory;

class InlineEdit extends Action
{
    const ADMIN_RESOURCE = 'EMP123_FormDemo::demo_grid_save';

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var FormDataFactory
     */
    protected $formDataFactory;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param FormDataFactory $formDataFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        FormDataFactory $formDataFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->formDataFactory = $formDataFactory;
    }

    /**
     * Inline edit save
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $entityId) {
                    $model = $this->formDataFactory->create()->load($entityId);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$entityId]));
                        $model->save();
                    } catch (\Exception $e) {
                        $messages[] = '[ID: ' . $entityId . '] ' . __($e->getMessage());
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}
