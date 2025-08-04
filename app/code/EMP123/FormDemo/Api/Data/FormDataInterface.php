<?php

namespace EMP123\FormDemo\Api\Data;

interface FormDataInterface
{
    const ENTITY_ID = 'entity_id';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const EMAIL = 'email';
    const DATE_OF_BIRTH = 'date_of_birth';
    const GENDER = 'gender';
    const TELEPHONE = 'telephone';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get ID
     *
     * @return int
     */
    public function getId();

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName();

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Get date of birth
     *
     * @return string
     */
    public function getDateOfBirth();

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender();

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone();

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set ID
     *
     * @param int $id
     * @return FormDataInterface
     */
    public function setId($id);

    /**
     * Set first name
     *
     * @param string $firstName
     * @return FormDataInterface
     */
    public function setFirstName($firstName);

    /**
     * Set last name
     *
     * @param string $lastName
     * @return FormDataInterface
     */
    public function setLastName($lastName);

    /**
     * Set email
     *
     * @param string $email
     * @return FormDataInterface
     */
    public function setEmail($email);

    /**
     * Set date of birth
     *
     * @param string $dateOfBirth
     * @return FormDataInterface
     */
    public function setDateOfBirth($dateOfBirth);

    /**
     * Set gender
     *
     * @param string $gender
     * @return FormDataInterface
     */
    public function setGender($gender);

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return FormDataInterface
     */
    public function setTelephone($telephone);

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return FormDataInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return FormDataInterface
     */
    public function setUpdatedAt($updatedAt);
}
