<?php

namespace Pagely\AtomicClient\Command\Accounts;

use GuzzleHttp\Exception\ClientException;
use Pagely\AtomicClient\API\Accounts\AccountsClient;
use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddSshKeyCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AccountsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AccountsClient $apps, $name = 'account:ssh:add')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Add a ssh key for an account - feed ssh key in via stdin')
            ->addUsage('Feed ssh key to command via stdin. Example: cat /path/to/my.key | ./bin/atomic account:ssh:add 37')
            ->addArgument('accountId', InputArgument::REQUIRED, 'Account ID')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $accountId = (int) $input->getArgument('accountId');
        $token = $this->token->token;
        $key = '';
        while (!feof(STDIN)) {
            $key .= fread(STDIN, 1024);
        }

        if (empty(trim($key))) {
            $output->writeln('No key given');
            return 1;
        }

        try {
            $this->api->addSshKey($token, $accountId, $key);
        } catch (ClientException $e) {
            $output->writeln('<error>Could not upload ssh key.</error>');
            $output->writeln(
                json_encode(
                    json_decode($e->getResponse()->getBody()->getContents(), true),
                    JSON_PRETTY_PRINT
                )
            );
            return 1;
        }

        $output->writeln('Key uploaded. It may take a few minutes before it is usable on the server');
        return 0;
    }
}
