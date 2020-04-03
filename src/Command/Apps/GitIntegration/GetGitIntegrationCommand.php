<?php

namespace Pagely\AtomicClient\Command\Apps\GitIntegration;

use Pagely\Client\AuthApi;
use Pagely\Client\Command\Command;
use Pagely\Client\Command\OauthCommandTrait;
use Pagely\Client\AppsApi\AppsClient;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetGitIntegrationCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AppsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AppsClient $apps, $name = 'apps:git-integration:get')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Get a git integration for a specific app')
            ->addArgument('appId', InputArgument::REQUIRED, 'App ID')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $token = $this->token->token;

        $r = $this->api->getGitIntegration(
            $token,
            (int) $input->getArgument('appId')
        );

        $body = json_decode($r->getBody()->getContents(), true);

        $rows = [];
        foreach($body['data'] as $key => $value) {
            $rows[] = [$key, $value];
        }

        $table = new Table($output);
        $table
            ->setColumnMaxWidth(1, 100)
            ->setRows($rows);
        $table->render();
        return 0;
    }
}
