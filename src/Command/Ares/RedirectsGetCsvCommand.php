<?php

namespace Pagely\AtomicClient\Command\Ares;

use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pagely\AtomicClient\API\Ares\AresConfigsClient;

class RedirectsGetCsvCommand extends Command
{
    use OauthCommandTrait;
    /**
     * @var AresConfigsClient
     */
    protected $api;

    protected $authApi;

    public function __construct(AuthApi $authApi, AresConfigsClient $api, $name = 'ares:redirects:get-csv')
    {
        $this->authApi = $authApi;
        $this->api = $api;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Get Ares redirects as as CSV file for an app')
            ->addArgument('appId', InputArgument::REQUIRED, 'App ID')
            ->addArgument('path', InputArgument::OPTIONAL, 'Location where you want to save the file.')
            ->addArgument('filename', InputArgument::OPTIONAL, 'Filename to save CSV as.')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $appId = (int) $input->getArgument('appId');
        $filename = $input->getArgument('filename');
        $path = rtrim($input->getArgument('path'), '/');

        $now = (new \DateTimeImmutable())->format('Y-m-d_H:i:s');

        $r = $this->api->getRedirectsCSV($this->token->token, $appId);

        $csvData = $r->getBody()->getContents();

        if (!$filename) {
            $filename = "ares_redirects_{$appId}_{$now}.csv";
        }

        if (!$path) {
            $path = getenv('HOME');
        }

        $file = "{$path}/{$filename}";

        file_put_contents($file, $csvData);

        if (file_exists($file)) {
            $output->writeln("<info>Successfully saved file to {$file}</info>");
        } else {
            $output->writeln("<error>Unable to save file to {$file}</error>");
        }

        return 0;
    }
}
