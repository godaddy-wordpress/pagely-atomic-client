<?php

// TODO remove this it was a testing stub :V

namespace Pagely\AtomicClient\Command\Accounts;

use League\CLImate\TerminalObject\Dynamic\Input;
use Pagely\AtomicClient\API\Accounts\AccountsClient;
use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetCollabRoleCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AccountsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AccountsClient $apps, $name = 'account:collabs:get-role')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Get collaborator role on account or specific app')
            ->addArgument('accountId', InputArgument::REQUIRED, 'Account ID')
            ->addArgument('collabId', InputArgument::REQUIRED, 'Collab user ID')
            ->addArgument('appId', InputArgument::OPTIONAL, 'App ID', 0)
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $accountId = $input->getArgument('accountId');
        $collabId = $input->getArgument('collabId');
        $appId = $input->getArgument('appId');
        $token = $this->token->token;

        $r = $this->api->getCollabRole($token, $accountId, $collabId, $appId);
        print_r($r);

        return 0;
    }
}
