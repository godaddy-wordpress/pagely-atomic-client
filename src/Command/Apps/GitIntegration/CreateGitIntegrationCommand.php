<?php

namespace Pagely\AtomicClient\Command\Apps\GitIntegration;

use Pagely\Client\AuthApi;
use Pagely\Client\Command\Command;
use Pagely\Client\Command\OauthCommandTrait;
use Pagely\Client\AppsApi\AppsClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateGitIntegrationCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AppsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AppsClient $apps, $name = 'apps:git-integration:create')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Create new git integration')
            ->addArgument('appId', InputArgument::REQUIRED, 'App ID')
            ->addArgument('remote', InputArgument::REQUIRED, 'Remote Provider')
            ->addArgument('branch', InputArgument::REQUIRED, 'Remote Branch')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $token = $this->token->token;
        $output->writeln('Create integration initiated.');

        $this->api->createGitIntegration(
            $token,
            (int) $input->getArgument('appId'),
            strtolower($input->getArgument('remote')),
            $input->getArgument('branch')
        );

        $output->writeln('Create integration executed.');
        return 0;
    }
}
