<?php

namespace EMP123\FormDemo\Model\ResourceModel\FormData;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use EMP123\FormDemo\Model\FormData as Model;
use EMP123\FormDemo\Model\ResourceModel\FormData as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'form_data_collection';

    /**
     * Initialize collection model
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
