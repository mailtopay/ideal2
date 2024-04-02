<?php

//{
//  "PaymentProductUsed": "IDEAL",
//  "CommonPaymentData":
//  {
//      "GuaranteedAmount": "1.00",
//      "PaymentStatus": "SettlementCompleted",
//      "PaymentId": "182809",
//      "AspspPaymentId": "0001141393887468",
//      "AspspId": "10002",
//      "DebtorInformation":
//      {
//          "Name": "Edsger Wybe Dijkstra - Callback",
//          "Agent": "ABNANL2AXXX",
//          "Account": {
//              "SchemeName": "IBAN",
//              "Identification": "NL44RABO0123456789",
//              "Currency": "EUR"
//          },
//          "ContactDetails":
//          {
//              "FirstName": "Edsger",
//              "LastName": "Dijkstra",
//              "PhoneNumber": "+31612345678",
//              "Email": "edsger@domain.nl"
//          }
//      }
//  },
//  "UseWaitingScreen": false
//}

namespace POM\iDEAL\Worldline\Resources;

class PaymentStatus
{
    public function __construct(
        private string $guaranteedAmount,
        private string $paymentStatus,
        private int $paymentId,
        private string $name,
        private string $agent,
        private string $identification,
        private string $firstname,
        private string $lastname,
        private string $phonenumber,
        private string $email,
        private bool $useWaitingScreen,
    )
    {
    }

    /**
     * @return string
     */
    public function getGuaranteedAmount(): string
    {
        return $this->guaranteedAmount;
    }

    /**
     * @return string
     */
    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    /**
     * @return int
     */
    public function getPaymentId(): int
    {
        return $this->paymentId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAgent(): string
    {
        return $this->agent;
    }

    /**
     * @return string
     */
    public function getIdentification(): string
    {
        return $this->identification;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @return string
     */
    public function getPhonenumber(): string
    {
        return $this->phonenumber;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return bool
     */
    public function isUseWaitingScreen(): bool
    {
        return $this->useWaitingScreen;
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['CommonPaymentData']['GuaranteedAmount'],
            $data['CommonPaymentData']['PaymentStatus'],
            $data['CommonPaymentData']['PaymentId'],
            $data['CommonPaymentData']['DebtorInformation']['Name'] ?? NULL,
            $data['CommonPaymentData']['DebtorInformation']['Agent'] ?? NULL,
            $data['CommonPaymentData']['DebtorInformation']['Account']['Identification'] ?? NULL,
            $data['CommonPaymentData']['DebtorInformation']['ContactDetails']['FirstName'] ?? NULL,
            $data['CommonPaymentData']['DebtorInformation']['ContactDetails']['LastName'] ?? NULL,
            $data['CommonPaymentData']['DebtorInformation']['ContactDetails']['PhoneNumber'] ?? NULL,
            $data['CommonPaymentData']['DebtorInformation']['ContactDetails']['Email'] ?? NULL,
            $data['UseWaitingScreen'] ?? FALSE,
        );
    }

}