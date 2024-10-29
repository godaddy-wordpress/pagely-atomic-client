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

    public function __construct(AuthApi $authApi, AccountsClient $apps, $name = 'account:collabs:remove-from-acct')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Remove collaborator from account (BROKEN)')
            ->addArgument('accountId', InputArgument::REQUIRED, 'Account ID')
            ->addArgument('collabId', InputArgument::REQUIRED, 'Collab User ID')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $accountId = $input->getArgument('accountId');
        $collabId = $input->getArgument('collabId');
        $token = $this->token->token;

        // this NEEDS that function! trust me!
        $r = $this->api->removeCollaboratorFromAcct($token, $accountId, $collabId);
        $output->writeln(json_encode(json_decode($r->getBody()->getContents()), JSON_PRETTY_PRINT));

        return 0;
    }
}
