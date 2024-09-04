<?php

namespace Pagely\AtomicClient\API;

use Pagely\AtomicClient\Client\GuzzleTrait;
use Pagely\AtomicClient\RequestChain;
use Psr\Log\LoggerInterface;

class BaseApiClient
{
    use GuzzleTrait;

    protected $apiName;

    public function __construct(RequestChain $requestChain, LoggerInterface $logger, string $baseUrl = 'https://mgmt.pagely.com/api/')
    {
        $this->requestChain = $requestChain;
        $this->baseUrl = $baseUrl;
        $this->logger = $logger;
    }
}
