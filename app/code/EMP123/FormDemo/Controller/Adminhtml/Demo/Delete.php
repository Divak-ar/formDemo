<?php

namespace EMP123\FormDemo\Controller\Adminhtml\Demo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use EMP123\FormDemo\Model\FormDataFactory;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'EMP123_FormDemo::demo_grid_delete';

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
     * Delete Demo Grid
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id');

        if ($id) {
            try {
                $model = $this->formDataFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('Record deleted successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
