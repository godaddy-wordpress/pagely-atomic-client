<?php

namespace Pagely\AtomicClient\Command\Apps;

use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Pagely\AtomicClient\API\Apps\AppsClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DomainRemoveCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AppsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AppsClient $apps, $name = 'apps:domain:remove')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Remove a domain name from an app')
            ->addArgument('appId', InputArgument::REQUIRED, 'App ID')
            ->addArgument('domainId', InputArgument::REQUIRED, 'Domain ID')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $appId = $input->getArgument('appId');
        $domain = $input->getArgument('domainId');
        $token = $this->token->token;

        $this->api->removeDomain($token, $appId, $domain);

        $output->writeln('Domain removed');
        return 0;
    }
}
