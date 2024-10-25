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

    public function listAccounts($accessToken, string $search = null, bool $includeNonSubs = false, $supportId = null)
    {
        $options = [
            'query' => [],
        ];
        if ($search) {
            $options['query']['search'] = $search;
        }
        if ($includeNonSubs) {
            $options['query']['includeNonSubs'] = 1;
        }
        if (!empty($supportId)) {
            $options['query']['supportId'] = $supportId;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get('accounts', $options);
    }

    public function listAccountsByIds($accessToken, array $accountIds, bool $minimal = false)
    {
        $options = [
            'query' => [
                'accountIds' => $accountIds,
                'minimal' => $minimal ? 'true' : 'false',
            ],
        ];

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get('accounts', $options);
    }

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

    public function getByPoolId($accessToken, $poolId)
    {
        $options = [
            'query' => [
                'poolId' => $poolId,
            ],
        ];

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get('accounts', $options);
    }

    public function getByBillingCustomerId(string $accessToken, string $id)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get("accounts/by-billing/{$id}");
    }

    public function getByUsername(string $accessToken, string $username)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get('accounts/by-username?username=' . urlencode($username));
    }

    public function getCapacity($accessToken, array $ids)
    {
        $options = [
            'json' => [
                'accountIds' => $ids,
            ],
        ];

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->post('accounts/capacity', $options);
    }

    public function getAccount($accessToken, $id, $minimal = false)
    {
        $query = [];
        if ($minimal) {
            $query['query']['minimal'] = 'true';
        }
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("accounts/{$id}", $query);
    }

    public function getAdmins($accessToken, $supportId = null)
    {
        $query = [];

        if (!empty($supportId)) {
            $query['query']['supportId'] = $supportId;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get('accounts/admins', $query);
    }

    public function getAdmin($accessToken, $id)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("accounts/admins/{$id}");
    }

    public function getGroupPubKeys($accessToken, $groupName)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("accounts/group/{$groupName}/keys");
    }

    public function getCollaboratorAccess($accessToken, $accountId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("accounts/{$accountId}/access");
    }

    public function removeCollaboratorsForApp($accessToken, $accountId, $appId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->delete("accounts/{$accountId}/collaborators/apps/{$appId}");
    }

    public function updateAccount($accessToken, $accountId, array $data)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->patch("accounts/{$accountId}", [
                'json' => $data,
            ]);
    }

    public function updateAdmin($accessToken, $id, array $data)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->patch("accounts/admins/{$id}", [
                'json' => $data,
            ]);
    }

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

    public function get2fa($accessToken, $accountId, $phone, $userType = 'account')
    {
        if ($userType === 'account') {
            return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
                ->get("accounts/{$accountId}/2fa", [
                    'query' => ['phone' => $phone],
                ]);
        }
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get("accounts/admins/{$accountId}/2fa", [
                'query' => ['phone' => $phone],
            ]);
    }

    public function getAccountOrAdminBySupportId($accessToken, $supportId)
    {
        try {
            $response = $this->getAdmins($accessToken, $supportId);
        } catch (ClientException $e) {
            if ($e->getCode() !== 404) {
                throw $e;
            }
        }
        if (empty($response)) {
            $response = $this->listAccounts($accessToken, null, false, $supportId);
        }

        return $response;
    }

    /**
     * @param array $data required: `confirm`, optional: `sendEmail`, `name`, `reason`
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function cancelAccount($accessToken, $accountId, array $data)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("accounts/{$accountId}/cancel-by-admin", [
                'json' => $data,
            ]);
    }

    public function detachAccount($accessToken, $accountId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("accounts/{$accountId}/detach");
    }


    public function createAdmin(
        string $token,
        string $username,
        string $name,
        string $email,
        string $phone,
        string $phoneCountry = '1',
        ?string $password = null
    ) {
        $json = [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'phoneCountry' => $phoneCountry,
        ];

        if ($password) {
            $json['password'] = $password;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($token))
            ->post('accounts/admins', [
                'json' => $json,
            ]);
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

    public function enable(string $accessToken, int $accountId, string $reason, string $name)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("accounts/{$accountId}/enable", [
                'json' => [
                    'reason' => $reason,
                    'name' => $name,
                ],
            ]);
    }
    public function disable(string $accessToken, int $accountId, string $reason, string $name)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("accounts/{$accountId}/disable", [
                'json' => [
                    'reason' => $reason,
                    'name' => $name,
                ],
            ]);
    }

    public function storeSignupInfo(string $accessToken, string $email, string $password)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post('accounts/signup-info', [
                'json' => [
                    'email' => $email,
                    'password' => $password,
                ],
            ]);
    }

    public function passwordReset(string $email, bool $admin = false)
    {
        $path = $admin ? 'accounts/admins/password-reset' : 'accounts/password-reset';
        return $this->guzzle()->post($path, [
            'json' => [
                'email' => $email,
            ],
        ]);
    }

    public function passwordResetFinish(string $password, string $token, bool $admin = false)
    {
        $path = $admin ? 'accounts/admins/password-reset' : 'accounts/password-reset';
        return $this->guzzle()->patch($path, [
            'json' => [
                'token' => $token,
                'password' => $password,
            ],
        ]);
    }

    public function listPreProvisionedAccountsForCell(string $token, string $cellId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($token))
            ->get("accounts/pre-provisioned/cellId/{$cellId}");
    }

    public function getIsGDPR($accessToken, $accountId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("accounts/{$accountId}/is-gdpr");
    }

    public function getSecretAnswer(string $token, int $accountId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($token))->get("accounts/{$accountId}/secret-answer");
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
