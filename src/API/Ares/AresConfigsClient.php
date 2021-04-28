<?php

namespace Pagely\AtomicClient\API\Ares;

use Pagely\AtomicClient\API\BaseApiClient;

class AresConfigsClient extends BaseApiClient
{
    protected $apiName = 'ares';
    
    public function getRedirectsCSV(string $accessToken, int $appId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get("serverconfig/ares/redirects/{$appId}/csv");
    }

    public function bulkUploadRedirects(
        string $accessToken,
        int $appId,
        array $redirects,
        bool $replace = false,
        bool $deleteAll = false
    ) {
        $data = [
            'redirects' => $redirects,
            'replace' => $replace,
            'deleteAll' => $deleteAll,
        ];

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("serverconfig/ares/redirects/{$appId}/batch", [
                'json' => $data,
            ]);
    }

    public function deployAresConfigsForApp(string $accessToken, int $appId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("serverconfig/ares/deploy/{$appId}");
    }
}
