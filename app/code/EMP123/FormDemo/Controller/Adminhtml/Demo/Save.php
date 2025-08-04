<?php

namespace EMP123\FormDemo\Controller\Adminhtml\Demo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use EMP123\FormDemo\Model\FormDataFactory;

class Save extends Action
{
    const ADMIN_RESOURCE = 'EMP123_FormDemo::demo_grid_save';

    /**
     * @var FormDataFactory
     */
    protected $formDataFactory;

    /**
     * @param Context $context
     * @param FormDataFactory $formDataFactory
     */
    public function __construct(
        Context $context,
        FormDataFactory $formDataFactory
    ) {
        parent::__construct($context);
        $this->formDataFactory = $formDataFactory;
    }

    /**
     * Save Demo Grid
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $model = $this->formDataFactory->create();

            if (isset($data['entity_id'])) {
                $model->load($data['entity_id']);
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Record saved successfully.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
