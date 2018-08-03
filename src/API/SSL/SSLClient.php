<?php
namespace Pagely\AtomicClient\API\SSL;

use Pagely\AtomicClient\API\BaseApiClient;

class SSLClient extends BaseApiClient
{
    protected $apiName = 'ssl';

    public function getCert($accessToken, $id, $includeChain = false)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("ssl/certs/{$id}", ['query' => ['includeChain' => $includeChain]]);
    }

    public function listCerts($accessToken, $accountId, $appId, $includeChain = false)
    {
        return $this->listCertsAsync($accessToken, $accountId, $appId, $includeChain)->wait();
    }

    public function searchLinkedCerts($accessToken, $appIds)
    {
        $options = [
            'query' => [
                'appIds' => $appIds
            ]
        ];
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get('ssl/linked/search', $options);
    }

    public function listCertsAsync($accessToken, $accountId, $appId, $includeChain = false)
    {
        $options = [
            'query' => [],
        ];
        if ($accountId) {
            $options['query']['accountId'] = $accountId;
        }
        if ($appId) {
            $options['query']['appId'] = $appId;
        }
        if ($includeChain) {
            $options['query']['includeChain'] = (bool)$includeChain;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->getAsync('ssl/certs', $options);
    }

    public function listCertsForApps($accessToken, $appIds)
    {
        $options = [
            'query' => [
                'id' => $appIds,
            ],
        ];
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get('internal/ssl/certs/listByappIds', $options);
    }

    public function listCSRs($accessToken, $accountId, $linked = null)
    {
        $options = [
            'query' => [
                'accountId' => $accountId,
            ],
        ];
        if (null !== $linked) {
            $options['query']['linkedToCert'] = $linked ? 1 : 0;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get('ssl/csrs', $options);
    }

    public function createCSR($accessToken, $accountId, $commonName, $countryName)
    {
        $options = [
            'json' => [
                'accountId' => $accountId,
                'commonName' => $commonName,
                'countryName' => $countryName,
            ]
        ];

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->post('ssl/csrs', $options);
    }

    public function addCertificate($accessToken, $accountId, $certificateString, $certChainString = null)
    {
        $options = [
            'json' => [
                'accountId' => $accountId,
                'certificate' => $certificateString,
            ],
        ];

        if ($certChainString) {
            $options['json']['certificateChain'] = $certChainString;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->post('ssl/certs', $options);
    }

    public function linkCertificateToApp(
        $accessToken,
        $certId,
        $aliasId,
        $redirect = null,
        $hstsTimeout = null,
        $http2 = null,
        $tlsConfig = null,
        $importConfig = null,
        $minTlsVersion = null,
        $redirectConfig = null,
        $cipher = null
    )
    {
        $options = [
            'json' => [
                'aliasId' => $aliasId,
                'certId' => $certId,
            ],
        ];

        foreach ([
            'redirect' => $redirect,
            'hstsTimeout' => $hstsTimeout,
            'noHttp2' => $http2,
            'tlsConfig' => $tlsConfig,
            'importConfig' => $importConfig,
            'minTlsVersion' => $minTlsVersion,
            'redirectConfig' => $redirectConfig,
            'cipher' => $cipher,
        ] as $key => $value) {
            if (null !== $value) {
                $options['json'][$key] = $value;
            }
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->post('ssl/link', $options);
    }

    public function updateAppCertificateConfig(
        $accessToken,
        $certId,
        $aliasId,
        $redirect = null,
        $hstsTimeout = null,
        $http2 = null,
        $tlsConfig = null,
        $importConfig = null,
        $minTlsVersion = null,
        $redirectConfig = null,
        $cipher = null
    )
    {
        $options = [
            'json' => [
                'aliasId' => $aliasId,
                'certId' => $certId,
            ],
        ];

        foreach ([
            'redirect' => $redirect,
            'hstsTimeout' => $hstsTimeout,
            'http2' => $http2,
            'tlsConfig' => $tlsConfig,
            'importConfig' => $importConfig,
            'minTlsVersion' => $minTlsVersion,
            'redirectConfig' => $redirectConfig,
            'tlsCipher' => $cipher,
        ] as $key => $value) {
            if (null !== $value) {
                $options['json'][$key] = $value;
            }
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->patch('ssl/link', $options);
    }

    public function activateCertForApp($accessToken, $certId, $appId, $aliasId = null)
    {
        $options = [
            'json' => [
                'appId' => $appId,
                'certId' => $certId,
            ],
        ];
        if ($aliasId) {
            $options['json']['aliasId'] = $aliasId;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->patch('ssl/activate', $options);
    }

    public function import($accessToken, $accountId, $csr, $key, $cert, $caChain = null)
    {
        $options = [
            'json' => [
                'accountId' => $accountId,
                'key' => $key,
                'certificate' => $cert,
            ],
        ];

        if ($csr) {
            $options['json']['csr'] = $csr;
        }

        if ($caChain) {
            $options['json']['certificateChain'] = $caChain;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->post('ssl/import', $options);
    }

    public function getAppCertLinks($accessToken, int $certId, int $appId)
    {
        return $this->getAppCertLinksAsync($accessToken, $certId, $appId)->wait();
    }

    public function getAppCertLinksAsync($accessToken, int $certId, int $appId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->getAsync('ssl/link', [
            'query' => [
                'certId' => $certId,
                'appId' => $appId,
            ],
        ]);
    }

    public function letsencryptRegister($accessToken, $domain, $accountId, array $alternateNames = [])
    {
        $body = [
            'json' => [
                'domain' => $domain,
                'accountId' => $accountId,
            ],
        ];

        if (!empty($alternateNames)) {
            $body['json']['alternateNames'] = $alternateNames;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->post('ssl/letsencrypt/register', $body);
    }

    public function letsencryptToken($accessToken, $domain, $key)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get(
            'ssl/letsencrypt/token/'.$domain.'/'.$key
        );
    }

    public function letsencryptGetCertificate($accessToken, $domain, $key)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->post('ssl/letsencrypt/get-cert', [
            'json' => [
                'domain' => $domain,
                'key' => $key,
            ],
        ]);
    }

    public function letsencryptRenewAll($accessToken)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->post('ssl/letsencrypt/renew-all');
    }

    public function letsencryptRenew($accessToken, $appId, $aliasId, $certId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post('ssl/letsencrypt/renew', [
                'json' => [
                    'appId' => $appId,
                    'aliasId' => $aliasId,
                    'certId' => $certId,
                ],
            ]);
    }
}
