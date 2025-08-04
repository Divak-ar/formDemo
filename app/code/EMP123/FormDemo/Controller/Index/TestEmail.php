<?php

namespace EMP123\FormDemo\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class TestEmail extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

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
     * @param JsonFactory $resultJsonFactory
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            $templateVars = [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'telephone' => '1234567890',
                'gender' => 'male',
                'date_of_birth' => '1990-01-01'
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
                    'name' => $senderName ?: 'Form Demo',
                    'email' => $senderEmail ?: 'noreply@example.com'
                ])
                ->addTo($senderEmail ?: 'admin@example.com', 'Admin')
                ->getTransport();

            $transport->sendMessage();
            
            $result->setData([
                'success' => true,
                'message' => 'Test email sent successfully!'
            ]);
            
        } catch (\Exception $e) {
            $result->setData([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ]);
        }

        return $result;
    }
}
