<?php

namespace Pagely\AtomicClient\Command\Accounts;

use Pagely\AtomicClient\API\Accounts\AccountsClient;
use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddCollabToAcctCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AccountsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AccountsClient $accounts, $name = 'account:collabs:add')
    {
        $this->authClient = $authApi;
        $this->api = $accounts;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Add collaborator to account or app')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address')
            ->addArgument('accountId', InputArgument::REQUIRED, 'Account ID')
            ->addArgument('roleId', InputArgument::REQUIRED, 'Role')
            ->addOption('app', null, InputOption::VALUE_OPTIONAL, 'App ID (acct-level if omitted)', 0)
            ->addOption('displayname', null, InputOption::VALUE_OPTIONAL, 'Display Name', 0)
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $newAcctEmail = $input->getArgument('email');
        $newAcctName = $input->getOption('displayname');
        if ($newAcctName === 0) { $newAcctName = $input->getArgument('email'); }
        $newAcctId = $input->getArgument('accountId');
        $newAcctRole = $this->roleToInt($input->getArgument('roleId'));
        if ($newAcctRole === false) {
            $output->writeln ("Invalid role, must be one of 'app-only-minimal', 'app-only', 'billing', 'tech', 'sub-admin', 'super-admin', 'owner'");
            return Command::FAILURE;
        }
        $newAppId = $input->getOption('app');
        $token = $this->token->token;

        $r = $this->api->addCollaboratorToAcct($token,
            $newAcctEmail, $newAcctName, $newAcctId, $newAcctRole, $newAppId);
        $output->writeln(json_encode(json_decode($r->getBody()->getContents()), JSON_PRETTY_PRINT));

        return 0;
    }

    private function roleToInt(string $role) 
    {
        $role = strtolower($role);
        switch($role) {
            case "app-only-minimal":
            case "apponlyminimal":
            case "1":
                return 1;
            case "app-only":
            case "apponly":
            case "2":
                return 2;
            case "billing":
            case "4":
                return 4;
            case "tech":
            case "6":
                return 6;
            case "sub-admin":
            case "subadmin":
            case "8":
                return 8;
            case "super-admin":
            case "superadmin":
            case "9":
                return 9;
            case "owner":
            case "10":
                return 10;
            default:
                return false;
        }
        // return false;
    }
}
