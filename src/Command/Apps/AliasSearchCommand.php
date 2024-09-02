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

class AliasSearchCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AppsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AppsClient $apps, $name = 'apps:alias:search')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Find aliases')
            ->addArgument('accountId', InputArgument::OPTIONAL, 'Account ID')
            ->addOption('appId', 'A', InputOption::VALUE_REQUIRED, 'App ID')
            ->addOption('search', 'S', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Mysql search string for domain (fqdn)')
            ->addOption('name', 'N', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Alias fqdn to match')
            ->addOption('nopressdns', null, InputOption::VALUE_NONE, 'Exclude pressdns.com aliases')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $accountId = $input->getArgument('accountId');
        $search = $input->getOption('search');
        $names = $input->getOption('name');
        $appId = $input->getOption('appId');
        $noPressDns = $input->getOption('nopressdns');
        $token = $this->token->token;

        $r = $this->api->findAliases($token, $accountId, $search, $names, $appId, $noPressDns);
        $output->writeln(json_encode(json_decode($r->getBody()->getContents(), true), JSON_PRETTY_PRINT));
        return 0;
    }
}
