<?php

namespace EMP123\FormDemo\Model;

use Magento\Framework\Model\AbstractModel;
use EMP123\FormDemo\Api\Data\FormDataInterface;

class FormData extends AbstractModel implements FormDataInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'form_data_model';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('EMP123\FormDemo\Model\ResourceModel\FormData');
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->getData(self::FIRST_NAME);
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->getData(self::LAST_NAME);
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Get date of birth
     *
     * @return string
     */
    public function getDateOfBirth()
    {
        return $this->getData(self::DATE_OF_BIRTH);
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->getData(self::GENDER);
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->getData(self::TELEPHONE);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return FormDataInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set first name
     *
     * @param string $firstName
     * @return FormDataInterface
     */
    public function setFirstName($firstName)
    {
        return $this->setData(self::FIRST_NAME, $firstName);
    }

    /**
     * Set last name
     *
     * @param string $lastName
     * @return FormDataInterface
     */
    public function setLastName($lastName)
    {
        return $this->setData(self::LAST_NAME, $lastName);
    }

    /**
     * Set email
     *
     * @param string $email
     * @return FormDataInterface
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Set date of birth
     *
     * @param string $dateOfBirth
     * @return FormDataInterface
     */
    public function setDateOfBirth($dateOfBirth)
    {
        return $this->setData(self::DATE_OF_BIRTH, $dateOfBirth);
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return FormDataInterface
     */
    public function setGender($gender)
    {
        return $this->setData(self::GENDER, $gender);
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return FormDataInterface
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return FormDataInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return FormDataInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
