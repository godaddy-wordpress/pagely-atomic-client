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

class DeleteGitIntegrationCommand extends Command
{
    use OauthCommandTrait;
    use ApiErrorOutputTrait;

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

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $token = $this->token->token;

        try {
            $this->api->deleteGitIntegration(
                $token,
                (int)$input->getArgument('appId')
            );
        } catch (\Throwable $e) {
            $this->getFormattedErrorMessages($input, $output, $e);
            return 1;
        }

        $output->writeln('Delete integration executed.');
        return 0;
    }
}
