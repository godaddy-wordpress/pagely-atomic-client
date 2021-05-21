<?php

namespace Pagely\AtomicClient\Command\Apps;

use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Pagely\AtomicClient\API\Apps\AppsClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DomainMakePrimaryCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AppsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AppsClient $apps, $name = 'apps:domain:make-primary')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Set the primary alias for an app')
            ->addArgument('appId', InputArgument::REQUIRED, 'App ID')
            ->addArgument('domainId', InputArgument::REQUIRED, 'Domain ID')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $appId = $input->getArgument('appId');
        $domainId = $input->getArgument('domainId');
        $token = $this->token->token;

        $r = $this->api->makePrimary($token, $appId, $domainId);

        $output->writeln(json_encode(json_decode($r->getBody()->getContents()), JSON_PRETTY_PRINT));
        return 0;
    }
}
