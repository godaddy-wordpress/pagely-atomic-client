<?php

namespace Pagely\AtomicClient\Command\Apps;

use GuzzleHttp\Exception\ClientException;
use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Pagely\AtomicClient\API\Apps\AppsClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppWebserverCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AppsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AppsClient $apps, $name = 'apps:set-server-type')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Set webserver type for app')
            ->addArgument('appId', InputArgument::REQUIRED, 'App ID')
            ->addArgument('type', InputArgument::REQUIRED, 'nginx or apache')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $appId = $input->getArgument('appId');
        $serverType = $input->getArgument('type');
        $token = $this->token->token;

        try {
            switch ($serverType) {
                case 'apache':
                    $result = $this->api->setWebserverTypeApache($token, $appId);
                    break;
                case 'nginx':
                    $result = $this->api->setWebserverTypeNginx($token, $appId);
                    break;
                default:
                    $output->writeln("<error>Unknown server type: {$serverType}</error> - must be apache or nginx");
                    return 1;
            }
        } catch (ClientException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            $output->writeln($e->getResponse()->getBody()->getContents());
            return 1;
        }

        $output->writeln(
            json_encode(
                json_decode(
                    $result->getBody()->getContents()
                ),
                JSON_PRETTY_PRINT
            )
        );
        return 0;
    }
}
