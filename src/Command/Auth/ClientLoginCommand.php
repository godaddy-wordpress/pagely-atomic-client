<?php
namespace Pagely\AtomicClient\Command\Auth;

use Pagely\AtomicClient\Command\Command;
use GuzzleHttp\Exception\BadResponseException;
use Pagely\AtomicClient\API\AuthApi;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Pagely\AtomicClient\OauthToken;

class ClientLoginCommand extends Command
{
    protected $api;
    public function __construct(AuthApi $api)
    {
        parent::__construct('auth:client-login');
        $this->api = $api;
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Login using client id & secret and save access token')
            ->addArgument('clientId', InputArgument::REQUIRED)
            ->addArgument('clientSecret', InputArgument::REQUIRED)
            ->addOption('scope', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Scope keys', [])
            ->addOption('show', 's', InputOption::VALUE_NONE, 'Just show the token, do not save it!')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($output->isDebug())
        {
            $this->api->debug = true;
        }

        try {
            $r = $this->api->clientLogin($input->getArgument('clientId'), $input->getArgument('clientSecret'), $input->getOption('scope'));
        } catch (BadResponseException $e) {
            $output->writeln('<error>Invalid Login</error>');
            return 1;
        }

        if ($input->getOption('show')) {
            $data = json_decode($r->getBody()->getContents());
            $output->writeln(json_encode($data, JSON_PRETTY_PRINT));
        } else {
            $token = new OauthToken();
            $token->saveRaw($r->getBody()->getContents(), 'client');
        }
        return 0;
    }

}
