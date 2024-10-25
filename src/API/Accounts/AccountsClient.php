<?php

namespace Pagely\AtomicClient\API\Accounts;

use Pagely\AtomicClient\API\BaseApiClient;

class AccountsClient extends BaseApiClient
{
    protected $apiName = 'accounts';

    public const TYPE_ADMIN = 'admin';
    public const TYPE_EVENT = 'event';
    public const TYPE_CONFIG = 'config';
    public const TYPE_ISSUE = 'issue';
    public const TYPE_CREDENTIALS = 'credentials';
    public const TYPE_CONTACT = 'contact';
    public const TYPE_REPORT = 'report';

    // old stuff 

    public function addSshKey(string $accessToken, int $accountId, string $sshKey)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("accounts/{$accountId}/ssh/keys", [
                'json' => [
                    'key' => $sshKey
                ],
            ]);
    }

    // everything from here down was copied over from mgmt, need to pare it to only the required bits


    // definitely in use :)
    public function listAccountCollaborators($accessToken, $accountId, string $access = 'from')
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get(
                "accounts/{$accountId}/collaborators",
                [
                    'query' => [
                        'access' => $access,
                    ],
                ]
            );
    }

    // ditto
    public function removeCollaboratorsForApp($accessToken, $accountId, $appId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->delete("accounts/{$accountId}/collaborators/apps/{$appId}");
    }

    // doesn't work - the client token doesn't have privs
    public function getAdmins($accessToken, $supportId = null)
    {
        $query = [];

        if (!empty($supportId)) {
            $query['query']['supportId'] = $supportId;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get('accounts/admins', $query);
    }

    // fun!
    /**
     * @param string $accessToken
     * @param int|string $targetAccountId
     * @param ?int $sourceAccountId
     * @param null $role
     * @param int $appId
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function removeAccess(string $accessToken, $targetAccountId, $sourceAccountId = null, $role = null, $appId = 0)
    {
        if ($sourceAccountId) {
            return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
                ->delete("accounts/{$targetAccountId}/collaborators/{$sourceAccountId}/{$role}/{$appId}");
        }

        // returned, so at thsi point we know no sourceAccountId was given.

        if ($appId) {
            return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
                ->delete("accounts/{$targetAccountId}/collaborators/apps/{$appId}");
        }

        throw new \Exception('Invalid arguments - must include source & role [& app], or app by itself');
    }


    public function getApiKeys(string $token, array $accountIds)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($token))
            ->get(
                'accounts/apikeys',
                [
                'query' => [
                    'accountIds' => $accountIds,
                ],
            ]
            );
    }

    public function fetchIds(string $token, bool $includeNonBilling = false)
    {
        $options = [];
        if ($includeNonBilling) {
            $options['query'] = ['nonbilling' => 'true'];
        }

        return $this->guzzle($this->getBearerTokenMiddleware($token))
            ->get('accounts/ids', $options);
    }

    public function updateAccountLimits(string $token, int $accountId, array $limits = [])
    {
        return $this->guzzle($this->getBearerTokenMiddleware($token))
            ->patch(sprintf('accounts/%d/limits', $accountId), [
                'json' => $limits,
            ]);
    }

    public function setCellId(string $token, int $accountId, string $cellId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($token))
            ->patch("accounts/{$accountId}/cell-id", [
                'json' => ['cellId' => $cellId],
            ]);
    }


    public function authenticateUser(string $accessToken, string $username, string $password)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post('accounts/authenticate', [
                'json' => [
                    'username' => $username,
                    'password' => $password,
                ],
            ]);
    }

    public function listSshPublicKeys(
        string $accessToken,
        string $accountId,
        ?string $orchestration = null,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get("accounts/{$accountId}/ssh/keys", [
                'json' => array_filter(
                    compact(
                        'orchestration',
                    )
                ),
            ]);
    }

    public function createSshPublicKey(
        string $accessToken,
        string $accountId,
        string $key,
        ?string $orchestration = null,
        ?string $sshUsername = null,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("accounts/{$accountId}/ssh/keys", [
                'json' => array_filter(compact(
                    'key',
                    'orchestration',
                    'sshUsername',
                )),
            ]);
    }

    public function deleteSshPublicKey(
        string $accessToken,
        string $accountId,
        string $sshKeyId,
        ?string $orchestration = null,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->delete("accounts/{$accountId}/ssh/keys/{$sshKeyId}", [
                'json' => array_filter(compact(
                    'orchestration',
                )),
            ]);
    }

    public function createSshUser(
        string $accessToken,
        string $sshUsername,
        string $ownerId,
        string $ownerType,
        string $sshUserType,
        bool $sftpOnly = false,
        array $appIds = [],
        bool $replace = false,
        bool $disabled = false,
        bool $generatePassword = false,
        ?string $password = null,
        ?\DateTimeInterface $expiry = null
    ) {
        $expiry = $expiry ? $expiry->format(\DateTimeInterface::ATOM) : null;

        $nonNullParams = array_filter(compact(
            'sshUsername',
            'ownerType',
            'sshUserType',
            'sftpOnly',
            'appIds',
            'replace',
            'disabled',
            'generatePassword',
            'password',
            'expiry',
        ), function ($value) {
            return $value !== null;
        });

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("accounts/{$ownerId}/ssh/user", [
                'json' => $nonNullParams,
            ]);
    }

    public function getSshUser(
        string $accessToken,
        string $sshUsername,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get("accounts/ssh/users/{$sshUsername}");
    }

    public function updateSshUser(
        string $accessToken,
        string $ownerId,
        #[\SensitiveParameter]
        ?string $password = null,
        ?bool $sftpOnly = null,
        ?bool $disabled = null,
        ?\DateTimeInterface $expiry = null
    ) {
        $expiry = $expiry ? $expiry->format(\DateTimeInterface::ATOM) : null;

        $nonNullParams = array_filter(compact(
            'password',
            'sftpOnly',
            'disabled',
            'expiry',
        ), function ($value) {
            return $value !== null;
        });

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->patch("accounts/{$ownerId}/ssh/user", [
                'json' => $nonNullParams,
            ]);
    }

    public function createSshApps(
        string $accessToken,
        string $sshUsername,
        array $appIds,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post('accounts/ssh/apps/create', [
                'json' => [
                    'sshUsername' => $sshUsername,
                    'appIds' => $appIds,
                ],
            ]);
    }

    public function listApps(
        string $accessToken,
        string $sshUsername,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get("accounts/ssh/users/{$sshUsername}/apps");
    }

    public function deleteSshUser(
        string $accessToken,
        string $accountId,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->delete("accounts/{$accountId}/ssh/user");
    }

    public function deleteSshApps(
        string $accessToken,
        array $appIds,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post('accounts/ssh/apps', [
                'json' => [
                    'appIds' => $appIds,
                ],
            ]);
    }


}
