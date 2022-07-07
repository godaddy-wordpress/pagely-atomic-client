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

class CreateAppCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AppsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AppsClient $apps, $name = 'apps:create')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Create new app')
            ->addArgument('accountId', InputArgument::REQUIRED, 'Account ID')
            ->addArgument('label', InputArgument::REQUIRED, 'App Label (name for the appr to appear as in Atomic)')
            ->addArgument('primaryDomain', InputArgument::REQUIRED, 'Primary Domain')
            ->addOption('multisite', 'm', InputOption::VALUE_REQUIRED, 'Enable multisite type (subdomain or subfolder)')
            ->addOption('use-label-in-sites-dir', null, InputOption::VALUE_NONE, 'Uses the label you have set as the directory name in the ~/sites/ directory when you login to the server.
            Without this option, the directory will be named the same as the primaryDomain which is the default behavior when creating a site via Atomic.')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $multi = $input->getOption('multisite');

        $token = $this->token->token;

        $r = $this->api->createApp(
            $token,
            $input->getArgument('accountId'),
            $input->getArgument('label'),
            $input->getArgument('primaryDomain'),
            !!$multi,
            $multi ?: null,
            $input->getOption('use-label-in-sites-dir')
        );

        $output->writeln(json_encode(json_decode($r->getBody()->getContents(), true), JSON_PRETTY_PRINT));
        return 0;
    }
}
