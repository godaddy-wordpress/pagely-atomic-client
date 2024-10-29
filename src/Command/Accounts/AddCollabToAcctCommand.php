<?php

namespace Pagely\AtomicClient\Command\Accounts;

use Pagely\AtomicClient\API\Accounts\AccountsClient;
use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddCollabToAcctCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AccountsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AccountsClient $apps, $name = 'account:collabs:add-to-account')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Add collaborator to account')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address')
            ->addArgument('accountId', InputArgument::REQUIRED, 'Account ID')
            ->addArgument('roleId', InputArgument::REQUIRED, 'Role ID')            
            ->addArgument('name', InputArgument::OPTIONAL, 'Display Name', 0)
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $newAcctEmail = $input->getArgument('email');
        $newAcctName = $input->getArgument('name');
        if ($newAcctName === 0) { $newAcctName = $input->getArgument('email'); }
        $newAcctAppId = $input->getArgument('accountId');
        $newAcctRole = $input->getArgument('roleId');
        $token = $this->token->token;

        $r = $this->api->addCollaboratorToAcct($token,
            $newAcctEmail, $newAcctName, $newAcctRole, $newAcctAppId);
        $output->writeln(json_encode(json_decode($r->getBody()->getContents()), JSON_PRETTY_PRINT));

        return 0;
    }
}
