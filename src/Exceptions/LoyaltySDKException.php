<?php

namespace LoyaltyLt\SDK\Exceptions;

use Exception;

class LoyaltySDKException extends Exception
{
    private string $errorCode;
    private ?int $httpStatus;

    public function __construct(string $message, string $code = 'UNKNOWN_ERROR', ?int $httpStatus = null)
    {
        parent::__construct($message);
        $this->errorCode = $code;
        $this->httpStatus = $httpStatus;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->errorCode,
            'http_status' => $this->httpStatus,
        ];
    }
}
