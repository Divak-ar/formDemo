<?php

namespace EMP123\FormDemo\Controller\Adminhtml\Demo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use EMP123\FormDemo\Model\FormDataFactory;
use Magento\Framework\Registry;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'EMP123_FormDemo::demo_grid_save';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var FormDataFactory
     */
    protected $formDataFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param FormDataFactory $formDataFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        FormDataFactory $formDataFactory,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->formDataFactory = $formDataFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Edit Demo Grid
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->formDataFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This record no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('form_data', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('EMP123_FormDemo::demo_grid');
        $resultPage->getConfig()->getTitle()->prepend(__('Demo Grid'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getFirstName() . ' ' . $model->getLastName() : __('New Record'));

        return $resultPage;
    }
}
