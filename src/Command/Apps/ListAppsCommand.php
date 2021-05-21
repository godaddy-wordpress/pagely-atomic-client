<?php

namespace Pagely\AtomicClient\Command\Apps;

use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Pagely\AtomicClient\API\Apps\AppsClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListAppsCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AppsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AppsClient $apps, $name = 'apps:ls')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('List Apps for an account')
            ->addArgument('account', InputArgument::REQUIRED, 'Account ID')
            ->addOption('includeAliases', 'A', InputOption::VALUE_NONE, 'Include embedded aliases')
            ->addOption('searchAlias', 'S', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Search strings')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $accountId = $input->getArgument('account');
        $token = $this->token->token;

        if ($input->getOption('includeAliases') || $input->getOption('searchAlias')) {
            $r = $this->api->findWithAlias(
                $token,
                $accountId,
                $input->getOption('includeAliases'),
                $input->getOption('searchAlias')
            );
        } else {
            $r = $this->api->find($token, $accountId);
        }
        $output->writeln(json_encode(json_decode($r->getBody()->getContents()), JSON_PRETTY_PRINT));
        return 0;
    }
}
