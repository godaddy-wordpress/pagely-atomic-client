<?php

namespace Pagely\AtomicClient\Command;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Pagely\AtomicClient\API\AuthApi;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Pagely\AtomicClient\OauthToken;
use Symfony\Component\Console\Style\SymfonyStyle;

trait ApiErrorOutputTrait
{
    public function getFormattedErrorMessages(InputInterface $input, OutputInterface $output, \Throwable $e): void
    {

        $io = new SymfonyStyle($input, $output);


        if (is_subclass_of($e, RequestException::class)) {
            $response = $e->getResponse();
            $write[] = sprintf(
                '%s %s',
                $response->getStatusCode(),
                $response->getReasonPhrase()
            );
            /** @var StreamInterface $msg */
            $contents = $response->getBody()->getContents();
            $msg = json_decode($contents, true);

            if (!empty($msg['body']) && is_array($msg['body'])) {
                foreach ($msg['body'] as $name => $errorContent) {
                    $write[] = "`$name`: " . implode(', ', $errorContent['messages']);
                }
            } elseif (!empty($msg['message'])) {
                // in case json decode returns an empty or mangled value
                $write[] = $msg['message'];
            } else {
                $write[] = $e->getMessage();
            }
        } else {
            $write[] = "Error code {$e->getCode()} occurred.";
            $write[] = $e->getMessage();
        }

        $io->error($write);
    }
}
