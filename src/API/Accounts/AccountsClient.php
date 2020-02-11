<?php


namespace Pagely\AtomicClient\API\Accounts;


use Pagely\AtomicClient\API\BaseApiClient;

class AccountsClient extends BaseApiClient
{
    protected $apiName = 'accounts';

    public function addSshKey(string $accessToken, int $accountId, string $sshKey)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("accounts/{$accountId}/ssh/keys", [
                'json' => [
                    'key' => $sshKey
                ],
            ]);
    }
}
