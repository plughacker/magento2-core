<?php

namespace PlugHacker\PlugCore\Payment\Aggregates;

use PlugHacker\PlugAPILib\Models\CreateCustomerRequest;
use PlugHacker\PlugAPILib\Models\CreateDocumentRequest;
use PlugHacker\PlugCore\Kernel\Abstractions\AbstractEntity;
use PlugHacker\PlugCore\Kernel\Services\LocalizationService;
use PlugHacker\PlugCore\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use PlugHacker\PlugCore\Payment\ValueObjects\CustomerDocument;
use PlugHacker\PlugCore\Payment\ValueObjects\CustomerPhones;
use PlugHacker\PlugCore\Payment\ValueObjects\CustomerType;

final class Customer extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    /** @var string */
    private $name;

    /** @var string */
    private $email;

    /** @var string */
    private $phoneNumber;

    /** @var CustomerDocument */
    private $document;

    private Address $billingAddress;

    private Address $deliveryAddress;

    /** @var LocalizationService */
    protected $i18n;
    private string $registrationDate;

    public function __construct()
    {
        $this->i18n = new LocalizationService();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;

    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = substr($name, 0, 64);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Customer
     * @throws \Exception
     */
    public function setEmail($email)
    {
        $this->email = substr($email, 0, 64);

        if (empty($this->email)) {

            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                "email"
            );

            throw new \Exception($message, 400);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return CustomerDocument
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param CustomerDocument $document
     * @return Customer
     * @throws \Exception
     */
    public function setDocument(CustomerDocument $document)
    {
        $_document = substr($document->getNumber(), 0, 16);
        if (empty($_document)) {
            $inputName = $this->i18n->getDashboard('document');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        $this->document = $document;

        return $this;
    }

    /**
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param Address $address
     */
    public function setBillingAddress(Address $address)
    {
        $this->billingAddress = $address;
    }

    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * @param Address $address
     */
    public function setDeliveryAddress(Address $address)
    {
        $this->deliveryAddress = $address;
    }

    public function getRegistrationDate(): string
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(string $registrationDate): void
    {
        $this->registrationDate = $registrationDate;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): mixed
    {
        $obj = new \stdClass();

        $obj->name = $this->name;
        $obj->email = $this->email;
        $obj->phoneNumber = $this->phoneNumber;
        $obj->registrationDate = $this->registrationDate;
        $obj->document = $this->document;
        $obj->address = $this->billingAddress;
        $obj->plugId = $this->getPlugId();

        return $obj;
    }

    public function getAddressToSDK()
    {
        if ($this->getBillingAddress() !== null) {
         return $this->getBillingAddress()->convertToSDKRequest();
        }
        return null;
    }

    public function convertToSDKRequest()
    {
        $customerRequest = new CreateCustomerRequest();

        $customerRequest->name = $this->getName();
        $customerRequest->email = $this->getEmail();
        $customerRequest->document = $this->getDocument();
        $customerRequest->address = $this->getAddressToSDK();
        $customerRequest->phoneNumber = $this->getPhoneNumber();

        return $customerRequest;
    }
}
