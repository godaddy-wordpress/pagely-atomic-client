<?php

namespace Pagely\AtomicClient\Command\Apps\GitIntegration;

use Pagely\Client\AuthApi;
use Pagely\Client\Command\Command;
use Pagely\Client\Command\OauthCommandTrait;
use Pagely\Client\AppsApi\AppsClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteGitIntegrationCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AppsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AppsClient $apps, $name = 'apps:git-integration:delete')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Delete git integration')
            ->addArgument('appId', InputArgument::REQUIRED, 'App ID')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $token = $this->token->token;

        $this->api->deleteGitIntegration(
            $token,
            (int) $input->getArgument('appId')
        );

        $output->writeln('Delete integration executed.');
        return 0;
    }
}
