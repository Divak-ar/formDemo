<?php

namespace EMP123\FormDemo\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FormData extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'form_data_resource_model';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('form_data', 'entity_id');
        $this->_useIsObjectNew = true;
    }
}
