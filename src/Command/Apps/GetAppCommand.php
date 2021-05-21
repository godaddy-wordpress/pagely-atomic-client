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

class GetAppCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AppsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AppsClient $apps, $name = 'apps:get')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Get specific app by ID')
            ->addArgument('appId', InputArgument::REQUIRED, 'App ID')
            ->addOption('ips', null, InputOption::VALUE_NONE, 'Return array of custom IPs & cnames')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $appId = $input->getArgument('appId');
        $token = $this->token->token;

        if ($input->getOption('ips')) {
            $r = $this->api->getAppCustomIps($token, $appId);
        } else {
            $r = $this->api->getById($token, $appId);
        }
        $output->writeln(json_encode(json_decode($r->getBody()->getContents()), JSON_PRETTY_PRINT));
        return 0;
    }
}
