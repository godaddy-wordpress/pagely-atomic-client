<?php

namespace Pagely\AtomicClient\Command\Accounts;

use Pagely\AtomicClient\API\Accounts\AccountsClient;
use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveCollabFromAcctCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AccountsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AccountsClient $apps, $name = 'account:collabs:remove')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Remove collaborator from account or app')
            ->addArgument('accountId', InputArgument::REQUIRED, 'Account ID')
            ->addArgument('collabId', InputArgument::REQUIRED, 'Collab User ID')
            ->addArgument('appId', InputArgument::OPTIONAL, 'App ID (acct-level if omitted)', 0)
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $accountId = $input->getArgument('accountId');
        $collabId = $input->getArgument('collabId');
        $appId = $input->getArgument('appId');
        $token = $this->token->token;

        $r = $this->api->removeCollaboratorFromAcct($token, $accountId, $collabId, $appId);
        $output->writeln(json_encode(json_decode($r->getBody()->getContents()), JSON_PRETTY_PRINT));

        return 0;
    }
}
