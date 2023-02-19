<?php
/**
 * @package   Bodak\CheckoutCustomForm
 * @author    Slawomir Bodak <slawek.bodak@gmail.com>
 * @copyright © 2017 Slawomir Bodak
 * @license   See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Bodak\CheckoutCustomForm\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Bodak\CheckoutCustomForm\Api\Data\CustomFieldsInterface;

/**
 * Class CustomFields
 *
 * @category Model/Data
 * @package  Bodak\CheckoutCustomForm\Model\Data
 */
class CustomFields extends AbstractExtensibleObject implements CustomFieldsInterface
{
    /**
     * Get ARRIVAL_PICK_UP_BY_ANOTHER_PERSON
     *
     * @return string|null
     */
    public function getArrivalPickUpByAnotherPerson()
    {
        return $this->_get(self::ARRIVAL_PICK_UP_BY_ANOTHER_PERSON);
    }

    /**
     * Get ARRIVAL_ANOTHER_PERSON_NAME
     *
     * @return string|null
     */
    public function getArrivalAnotherPersonName()
    {
        return $this->_get(self::ARRIVAL_ANOTHER_PERSON_NAME);
    }

    /**
     * Get ARRIVAL_ANOTHER_PERSON_PHONE
     *
     * @return string|null
     */
    public function getArrivalAnotherPersonPhone()
    {
        return $this->_get(self::ARRIVAL_ANOTHER_PERSON_PHONE);
    }

    /**
     * Get ARRIVAL_NUMBER_OF_CO_TRAVELLER
     *
     * @return string|null
     */
    public function getArrivalNumberOfCoTraveller()
    {
        return $this->_get(self::ARRIVAL_NUMBER_OF_CO_TRAVELLER);
    }

    /**
     * Get ARRIVAL_CO_TRAVELLER_FULL_NAME
     *
     * @return string|null
     */
    public function getArrivalCoTravellerFullName()
    {
        return $this->_get(self::ARRIVAL_CO_TRAVELLER_FULL_NAME);
    }

    /**
     * Get ARRIVAL_CO_TRAVELLER_DOB
     *
     * @return string|null
     */
    public function getArrivalCoTravellerDob()
    {
        return $this->_get(self::ARRIVAL_CO_TRAVELLER_DOB);
    }

    /**
     * Get ARRIVAL_PACKAGE_ACKNOWLEDGE
     *
     * @return string|null
     */
    public function getArrivalPackageAcknowledge()
    {
        return $this->_get(self::ARRIVAL_PACKAGE_ACKNOWLEDGE);
    }

    /**
     * Get ARRIVAL_FLIGHT_DATE
     *
     * @return string|null
     */
    public function getArrivalFlightDate()
    {
        return $this->_get(self::ARRIVAL_FLIGHT_DATE);
    }

    /**
     * Get ARRIVAL_FLIGHT_TIME
     *
     * @return string|null
     */
    public function getArrivalFlightTime()
    {
        return $this->_get(self::ARRIVAL_FLIGHT_TIME);
    }

    /**
     * Get ARRIVAL_FLIGHT_NUMBER
     *
     * @return string|null
     */
    public function getArrivalFlightNumber()
    {
        return $this->_get(self::ARRIVAL_FLIGHT_NUMBER);
    }

    /**
     * Get ARRIVAL_FLIGHT_NAME
     *
     * @return string|null
     */
    public function getArrivalFlightName()
    {
        return $this->_get(self::ARRIVAL_FLIGHT_NAME);
    }

    /**
     * Get ARRIVAL_COLLECTION_TIME
     *
     * @return string|null
     */
    public function getArrivalCollectionTime()
    {
        return $this->_get(self::ARRIVAL_COLLECTION_TIME);
    }

    /**
     * Get ARRIVAL_COLLECTION_POINT
     *
     * @return string|null
     */
    public function getArrivalCollectionPoint()
    {
        return $this->_get(self::ARRIVAL_COLLECTION_POINT);
    }

    /**
     * Get COLLECTION_DATE
     *
     * @return string|null
     */
    public function getCollectionDate()
    {
        return $this->_get(self::COLLECTION_DATE);
    }

    /**
     * Get ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND
     *
     * @return string|null
     */
    public function getArrivalDoYouHaveQuantityOnHand()
    {
        return $this->_get(self::ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND);
    }

    /**
     * Get ARRIVAL_QUANTITY_ON_HAND
     *
     * @return string|null
     */
    public function getArrivalQuantityOnHand()
    {
        return $this->_get(self::ARRIVAL_QUANTITY_ON_HAND);
    }

    /**
     * Get ARRIVAL_ALLOWANCE_LIMIT
     *
     * @return string|null
     */
    public function getArrivalAllowanceLimit()
    {
        return $this->_get(self::ARRIVAL_ALLOWANCE_LIMIT);
    }


    /**
     * Set ARRIVAL_PICK_UP_BY_ANOTHER_PERSON
     *
     * @param string|null $arrivalPickUpByAnotherPerson
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalPickUpByAnotherPerson(string $arrivalPickUpByAnotherPerson = null)
    {
        return $this->setData(self::ARRIVAL_PICK_UP_BY_ANOTHER_PERSON, $arrivalPickUpByAnotherPerson);
    }

    /**
     * Set ARRIVAL_ANOTHER_PERSON_NAME
     *
     * @param string|null $arrivalAnotherPersonName
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalAnotherPersonName(string $arrivalAnotherPersonName = null)
    {
        return $this->setData(self::ARRIVAL_ANOTHER_PERSON_NAME, $arrivalAnotherPersonName);
    }

    /**
     * Set ARRIVAL_ANOTHER_PERSON_PHONE
     *
     * @param string|null $arrivalAnotherPersonPhone
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalAnotherPersonPhone(string $arrivalAnotherPersonPhone = null)
    {
        return $this->setData(self::ARRIVAL_ANOTHER_PERSON_PHONE, $arrivalAnotherPersonPhone);
    }

    /**
     * Set ARRIVAL_NUMBER_OF_CO_TRAVELLER
     *
     * @param string|null $arrivalNumberOfCoTraveller
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalNumberOfCoTraveller(string $arrivalNumberOfCoTraveller = null)
    {
        return $this->setData(self::ARRIVAL_NUMBER_OF_CO_TRAVELLER, $arrivalNumberOfCoTraveller);
    }

    /**
     * Set ARRIVAL_CO_TRAVELLER_FULL_NAME
     *
     * @param string|null $arrivalCoTravellerFullName
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalCoTravellerFullName(string $arrivalCoTravellerFullName = null)
    {
        return $this->setData(self::ARRIVAL_CO_TRAVELLER_FULL_NAME, $arrivalCoTravellerFullName);
    }

    /**
     * Set ARRIVAL_CO_TRAVELLER_DOB
     *
     * @param string|null $arrivalCoTravellerDob
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalCoTravellerDob(string $arrivalCoTravellerDob = null)
    {
        return $this->setData(self::ARRIVAL_CO_TRAVELLER_DOB, $arrivalCoTravellerDob);
    }

    /**
     * Set ARRIVAL_PACKAGE_ACKNOWLEDGE
     *
     * @param string|null $arrivalPackageAcknowledge
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalPackageAcknowledge(string $arrivalPackageAcknowledge = null)
    {
        return $this->setData(self::ARRIVAL_PACKAGE_ACKNOWLEDGE, $arrivalPackageAcknowledge);
    }

    /**
     * Set ARRIVAL_FLIGHT_DATE
     *
     * @param string|null $arrivalFlightDate
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalFlightDate(string $arrivalFlightDate = null)
    {
        return $this->setData(self::ARRIVAL_FLIGHT_DATE, $arrivalFlightDate);
    }

    /**
     * Set ARRIVAL_FLIGHT_TIME
     *
     * @param string|null $arrivalFlightTime
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalFlightTime(string $arrivalFlightTime = null)
    {
        return $this->setData(self::ARRIVAL_FLIGHT_TIME, $arrivalFlightTime);
    }

    /**
     * Set ARRIVAL_FLIGHT_NUMBER
     *
     * @param string|null $arrivalFlightNumber
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalFlightNumber(string $arrivalFlightNumber = null)
    {
        return $this->setData(self::ARRIVAL_FLIGHT_NUMBER, $arrivalFlightNumber);
    }

    /**
     * Set ARRIVAL_FLIGHT_NAME
     *
     * @param string|null $arrivalFlightName
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalFlightName(string $arrivalFlightName = null)
    {
        return $this->setData(self::ARRIVAL_FLIGHT_NAME, $arrivalFlightName);
    }

    /**
     * Set ARRIVAL_COLLECTION_TIME
     *
     * @param string|null $arrivalCollectionTime
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalCollectionTime(string $arrivalCollectionTime = null)
    {
        return $this->setData(self::ARRIVAL_COLLECTION_TIME, $arrivalCollectionTime);
    }

    /**
     * Set ARRIVAL_COLLECTION_POINT
     *
     * @param string|null $arrivalCollectionPoint
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalCollectionPoint(string $arrivalCollectionPoint = null)
    {
        return $this->setData(self::ARRIVAL_COLLECTION_POINT, $arrivalCollectionPoint);
    }

    /**
     * Set COLLECTION_DATE
     *
     * @param string|null $arrivalCollectionPoint
     *
     * @return CustomFieldsInterface
     */
    public function setCollectionDate(string $arrivalCollectionPoint = null)
    {
        return $this->setData(self::COLLECTION_DATE, $arrivalCollectionPoint);
    }

    /**
     * Set ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND
     *
     * @param string|null $arrivalDoYouHaveQuantityOnHand
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalDoYouHaveQuantityOnHand(string $arrivalDoYouHaveQuantityOnHand = null)
    {
        return $this->setData(self::ARRIVAL_DO_YOU_HAVE_QUANTITY_ON_HAND, $arrivalDoYouHaveQuantityOnHand);
    }

    /**
     * Set ARRIVAL_QUANTITY_ON_HAND
     *
     * @param string|null $arrivalQuantityOnHand
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalQuantityOnHand(string $arrivalQuantityOnHand = null)
    {
        return $this->setData(self::ARRIVAL_QUANTITY_ON_HAND, $arrivalQuantityOnHand);
    }

    /**
     * Set ARRIVAL_ALLOWANCE_LIMIT
     *
     * @param string|null $arrivalAllowanceLimit
     *
     * @return CustomFieldsInterface
     */
    public function setArrivalAllowanceLimit(string $arrivalAllowanceLimit = null)
    {
        return $this->setData(self::ARRIVAL_ALLOWANCE_LIMIT, $arrivalAllowanceLimit);
    }
    
}
