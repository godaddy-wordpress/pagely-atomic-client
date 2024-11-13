<?php

namespace Pagely\AtomicClient\Command\Ares;

use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Pagely\AtomicClient\API\Ares\AresConfigsClient;

class RedirectsUploadCommand extends Command
{
    use OauthCommandTrait;
    /**
     * @var AresConfigsClient
     */
    protected $api;

    protected $authApi;

    public function __construct(AuthApi $authApi, AresConfigsClient $api, $name = 'ares:redirects:upload')
    {
        $this->authApi = $authApi;
        $this->api = $api;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Upload redirects from a CSV file for an app')
            ->addArgument('appId', InputArgument::REQUIRED, 'App ID')
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file containing redirects')
            ->addOption('replace', null, InputOption::VALUE_NONE, 'Update existing / add new redirects')
            ->addOption('deleteAll', null, InputOption::VALUE_NONE, 'Delete all existing redirects and add new redirects')
            ->addOption('deploy', null, InputOption::VALUE_NONE, 'Deploy redirects')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $appId = (int) $input->getArgument('appId');
        $file = $input->getArgument('file');
        $replace = (bool) $input->getOption('replace');
        $delete = (bool) $input->getOption('deleteAll');

        if (!file_exists($file)) {
            $output->writeln("<error>CSV file does not exist at path {$file}</error>");
            return 0;
        }

        $file = file_get_contents($file);

        // parse the csv
        /** @var array $data */
        $data = array_map('str_getcsv', explode("\n", trim($file)));

        $columns = array_values($data[0]);

        if ($columns[0] !== 'from' && $columns[1] !== 'to') {
            //missng headings row
            $output->writeln("<error>CSV file must have headings for the first row</error>");
            return 0;
        }

        // add field names as keys
        foreach ($data as &$row) {
            foreach ($row as $key => $value) {
                $row[$columns[$key]] = $value;
                unset($row[$key]);
            }
        }

        // remove headings row
        unset($data[0]);
        $data = array_values($data);

        if (count($data) <= 1) {
            $output->writeln("<error>CSV file does not contain any redirects</error>");
            return 0;
        }

        $r = $this->api->bulkUploadRedirects(
            $this->token->token,
            $appId,
            $data,
            $replace,
            $delete
        );

        $body = $r->getBody()->getContents();
        $results = json_decode($body, true)['data'];

        $output->writeln("<info>Upload result:</info>");
        if ($results['deleted']['count'] > 0) {
            $output->writeln("Deleted: {$results['deleted']['count']}");
        }
        if ($results['created']['count'] > 0) {
            $output->writeln("Created: {$results['created']['count']}");
        }
        if ($results['updated']['count'] > 0) {
            $output->writeln("Updated: {$results['updated']['count']}");
        }
        if ($results['failed']['count'] > 0) {
            $output->writeln("<error>Failed: {$results['failed']['count']}</error>");
            $output->writeln("Failures:");
            $output->writeln(json_encode($results['failed']['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        if ($input->getOption('deploy')) {
            $this->api->deployAresConfigsForApp(
                $this->token->token,
                $appId,
            );

            $output->writeln("<info>Triggered redirects deploy</info>");
        }

        return 0;
    }
}
