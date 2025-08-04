<?php

namespace EMP123\FormDemo\Controller\Adminhtml\Demo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use EMP123\FormDemo\Model\ResourceModel\FormData\CollectionFactory;
use EMP123\FormDemo\Model\FormDataFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Controller\ResultFactory;

class MassEmail extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        
        $emailsSent = 0;
        
        foreach ($collection as $record) {
            try {
                $this->sendEmail($record);
                $emailsSent++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Error sending email to %1: %2', $record->getEmail(), $e->getMessage())
                );
            }
        }

        if ($emailsSent > 0) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 email(s) have been sent.', $emailsSent)
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Send email to customer
     *
     * @param \EMP123\FormDemo\Model\FormData $record
     * @throws \Exception
     */
    protected function sendEmail($record)
    {
        $templateVars = [
            'first_name' => $record->getFirstName(),
            'last_name' => $record->getLastName(),
            'email' => $record->getEmail(),
            'telephone' => $record->getTelephone(),
            'gender' => $record->getGender(),
            'date_of_birth' => $record->getDateOfBirth()
        ];

        $senderEmail = $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            ScopeInterface::SCOPE_STORE
        );
        
        $senderName = $this->scopeConfig->getValue(
            'trans_email/ident_general/name',
            ScopeInterface::SCOPE_STORE
        );

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('formdemo_admin_notification')
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ])
            ->setTemplateVars($templateVars)
            ->setFromByScope([
                'name' => $senderName,
                'email' => $senderEmail
            ])
            ->addTo($record->getEmail(), $record->getFirstName() . ' ' . $record->getLastName())
            ->getTransport();

        $transport->sendMessage();
    }

    /**
     * Check if user has permission to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('EMP123_FormDemo::demo_grid');
    }
}
