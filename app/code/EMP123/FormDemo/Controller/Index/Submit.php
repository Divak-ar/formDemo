<?php

namespace EMP123\FormDemo\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use EMP123\FormDemo\Model\FormDataFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Submit extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var FormDataFactory
     */
    protected $formDataFactory;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param FormDataFactory $formDataFactory
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        FormDataFactory $formDataFactory,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formDataFactory = $formDataFactory;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Submit form data
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        if (!$this->getRequest()->isPost()) {
            return $resultJson->setData(['success' => false, 'message' => 'Invalid request method.']);
        }

        $data = $this->getRequest()->getPostValue();
        
        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'email', 'date_of_birth', 'gender', 'telephone'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $resultJson->setData(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.']);
            }
        }

        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $resultJson->setData(['success' => false, 'message' => 'Please enter a valid email address.']);
        }

        try {
            // Save data to database
            $formData = $this->formDataFactory->create();
            $formData->setFirstName($data['first_name']);
            $formData->setLastName($data['last_name']);
            $formData->setEmail($data['email']);
            $formData->setDateOfBirth($data['date_of_birth']);
            $formData->setGender($data['gender']);
            $formData->setTelephone($data['telephone']);
            $formData->save();

            // Send email
            $this->sendEmail($data);

            return $resultJson->setData(['success' => true, 'message' => 'Form submitted successfully!']);
        } catch (\Exception $e) {
            return $resultJson->setData(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    /**
     * Send email to customer
     *
     * @param array $data
     * @throws LocalizedException
     */
    protected function sendEmail($data)
    {
        $this->inlineTranslation->suspend();
        
        try {
            $templateVars = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'telephone' => $data['telephone']
            ];

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('formdemo_email_template')
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId()])
                ->setTemplateVars($templateVars)
                ->setFromByScope('general')
                ->addTo($data['email'], $data['first_name'] . ' ' . $data['last_name'])
                ->getTransport();

            $transport->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
