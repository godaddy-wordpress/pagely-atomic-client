<?php

namespace Pagely\AtomicClient\API;

use Pagely\AtomicClient\Client\GuzzleTrait;
use Pagely\AtomicClient\RequestChain;
use Psr\Log\LoggerInterface;

class BaseApiClient
{
    use GuzzleTrait;

    public function __construct(RequestChain $requestChain, $baseUrl = 'https://mgmt.pagely.com/api/', LoggerInterface $logger)
    {
        $this->requestChain = $requestChain;
        $this->baseUrl = $baseUrl;
        $this->logger = $logger;
    }
}
