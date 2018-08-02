<?php

namespace Pagely\AtomicClient\Command\SSL;

use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Pagely\AtomicClient\API\SSL\SSLClient;

abstract class AbstractSSLCmd extends Command
{
    use OauthCommandTrait;

    protected $commandName;

    /**
     * @var SSLClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, SSLClient $api)
    {
        $this->authClient = $authApi;
        $this->api = $api;
        parent::__construct($this->commandName);
    }
}
