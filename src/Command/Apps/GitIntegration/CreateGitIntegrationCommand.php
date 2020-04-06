<?php

namespace Pagely\AtomicClient\Command\Apps\GitIntegration;

use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\ApiErrorOutputTrait;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Pagely\AtomicClient\API\Apps\AppsClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateGitIntegrationCommand extends Command
{
    use OauthCommandTrait;
    use ApiErrorOutputTrait;

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

        try {
            $r = $this->api->createGitIntegration(
                $token,
                (int) $input->getArgument('appId'),
                strtolower($input->getArgument('remote')),
                $input->getArgument('branch')
            );
        } catch (\Throwable $e) {
            $this->getFormattedErrorMessages($input, $output, $e);
            return 1;
        }


        $output->writeln('Create integration executed.');
        return 0;
    }
}
