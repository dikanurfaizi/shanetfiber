<?php

namespace App\Services\Routerboard\Ppp;

use Exception;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;
use App\Http\Resources\Pppoe\SecretActiveResource;
use App\Helpers\LogHelper;
use RouterOS\Exceptions\StreamException;

class SecretService
{
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws BadCredentialsException
     * @throws QueryException
     * @throws ConfigException
     * @throws Exception
     */
    public function getMikrotik($mikrotik): ?Client
    {
        try {
            if (!$mikrotik) {
                throw new Exception('Mikrotik not found.');
            }

            // LogHelper::logInfo(
            //     null,
            //     [
            //         'host' => $mikrotik->host ?? 'N/A',
            //         'port' => $mikrotik->port ?? 'N/A',
            //     ],
            //     200, // Gateway Timeout
            //     "Try to connect"
            // );

            $config = (new Config())
                ->set('timeout', 2)
                ->set('host', $mikrotik->host)
                ->set('port', (int) $mikrotik->port)
                ->set('pass', $mikrotik->password)
                ->set('user', $mikrotik->username);

            // LogHelper::logInfo(
            //     null,
            //     [
            //         'host' => $mikrotik->host ?? 'N/A',
            //         'port' => $mikrotik->port ?? 'N/A',
            //         'config_ready' => true
            //     ],
            //     200,
            //     "Mikrotik config created successfully"
            // );

            return new Client($config);
        } catch (StreamException $e) { // Jika library mendukung exception spesifik
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'port' => $mikrotik->port ?? 'N/A',
                    'error' => 'Stream Timeout: ' . $e->getMessage()
                ],
                504, // Gateway Timeout
                "Failed to connect to Mikrotik due to stream timeout"
            );
            return null;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'port' => $mikrotik->port ?? 'N/A',
                    'error' => $e->getMessage()
                ],
                503, // Service Unavailable
                "Failed to connect to Mikrotik"
            );
            return null;
        }

        return null;
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */

    public function createSecret($mikrotik, $username, $password, $service, $profile, $comment)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            // Log sebelum membuat query
            LogHelper::logInfo(null, [
                'router' => $mikrotik->name ?? 'unknown',
                'username' => $username,
                'profile' => $profile,
                'service' => $service,
                'comment' => $comment
            ], 200, 'Preparing to create secret');

            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $username)
                ->equal('password', $password)
                ->equal('service', $service)
                ->equal('comment', $comment)
                ->equal('profile', $profile)
                ->equal('disabled', 'false');

            $response = $client->query($query)->read();

            LogHelper::logInfo(null, [
                'router' => $mikrotik->name,
                'username' => $username,
                'profile' => $profile,
                'service' => $service,
                'comment' => $comment,
                'response' => $response
            ], 200, 'Secret created successfully');

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'router' => $mikrotik->name ?? 'unknown',
                'username' => $username,
                'profile' => $profile,
                'service' => $service,
                'comment' => $comment,
                'error' => $e->getMessage()
            ], 500, 'Failed to create secret');

            return null;
        }
    }

    public function updateSecret($mikrotik, $username, $password, $service, $profile, $id)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('.id', $id);
            $editPppSecrets = $client->query($pppsecrets)->read();

            if (empty($editPppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($editPppSecrets as $editPppSecret) {
                $edit = (new Query('/ppp/secret/set'))
                    ->where('.id', $id)
                    ->equal('name', $username)
                    ->equal('password', $password)
                    ->equal('service', $service)
                    ->equal('profile', $profile);

                return $client->query($edit)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to update secret');
            return null;
        }
    }

    public function updateUserPasswordServiceSecret($mikrotik, $username, $password, $service, $id, $callerID)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('.id', $id);
            $editPppSecrets = $client->query($pppsecrets)->read();

            if (empty($editPppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($editPppSecrets as $editPppSecret) {
                $edit = (new Query('/ppp/secret/set'))
                    ->where('.id', $id)
                    ->equal('.id', $editPppSecret['.id'])
                    ->equal('name', $username)
                    ->equal('password', $password)
                    ->equal('service', $service)
                    ->equal('caller-id', $callerID);
                return $client->query($edit)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to update user password/service secret');
            return null;
        }
    }

    public function updateProfileSecret($mikrotik, $profile, $id)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('.id', $id);
            $editPppSecrets = $client->query($pppsecrets)->read();

            if (empty($editPppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($editPppSecrets as $editPppSecret) {
                $edit = (new Query('/ppp/secret/set'))
                    ->where('.id', $id)
                    ->equal('.id', $editPppSecret['.id'])
                    ->equal('profile', $profile);

                return $client->query($edit)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to update profile secret');
            return null;
        }
    }

    public function addCommentSecret($mikrotik, $comment, $secretId)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('.id', $secretId);
            $editPppSecrets = $client->query($pppsecrets)->read();

            if (empty($editPppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($editPppSecrets as $editPppSecret) {
                $edit = (new Query('/ppp/secret/set'))
                    ->where('.id', $secretId)
                    ->equal('.id', $editPppSecret['.id'])
                    ->equal('comment', $comment);

                return $client->query($edit)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to add comment to secret');
            return null;
        }
    }

    public function enableSecret($mikrotik, $username)
    {
        try {
            $toEnable = 'false';
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('name', $username);
            $enablePppSecrets = $client->query($pppsecrets)->read();

            if (empty($enablePppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($enablePppSecrets as $enablePppSecret) {
                $enable = (new Query('/ppp/secret/set'))
                    ->where('name', $username)
                    ->equal('.id', $enablePppSecret['.id'])
                    ->equal('disabled', $toEnable);
                $client->query($enable)->read();
            }
            return ($response = 'success');
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to enable secret');
            return 'error';
        }
    }

    public function enableOneSecret($mikrotik, $secretId)
    {
        try {
            $toEnable = 'false';
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('.id', $secretId);
            $enablePppSecrets = $client->query($pppsecrets)->read();

            if (empty($enablePppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($enablePppSecrets as $enablePppSecret) {
                $enable = (new Query('/ppp/secret/set'))
                    ->where('.id', $secretId)
                    ->equal('.id', $enablePppSecret['.id'])
                    ->equal('disabled', $toEnable);
                $client->query($enable)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to enable one secret');
        }
    }

    public function disableSecret($mikrotik, $username)
    {
        try {
            $toNoDisable = 'true';
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('name', $username);
            $disablePppSecrets = $client->query($pppsecrets)->read();

            if (empty($disablePppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($disablePppSecrets as $disablePppSecret) {
                $disable = (new Query('/ppp/secret/set'))
                    ->where('name', $username)
                    ->equal('.id', $disablePppSecret['.id'])
                    ->equal('disabled', $toNoDisable);
                $client->query($disable)->read();
            }
            return ($response = 'success');
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to disable secret');
            return ($response = 'error');
        }
    }

    public function disableOneSecret($mikrotik, $secretId)
    {
        try {
            $toNoDisable = 'true';
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('.id', $secretId);
            $disablePppSecrets = $client->query($pppsecrets)->read();

            if (empty($disablePppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($disablePppSecrets as $disablePppSecret) {
                $disable = (new Query('/ppp/secret/set'))
                    ->where('.id', $secretId)
                    ->equal('.id', $disablePppSecret['.id'])
                    ->equal('disabled', $toNoDisable);
                $client->query($disable)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to disable one secret');
        }
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */

    public function deleteSecretByName($mikrotik, $username): void
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecret = new Query('/ppp/secret/print');
            $pppsecret->where('name', $username);
            $deleteSecrets = $client->query($pppsecret)->read();

            if (empty($deleteSecrets)) {
                LogHelper::logError(
                    null,
                    [
                        'host' => $mikrotik->host ?? 'N/A',
                        'username' => $username,
                        'error' => 'Secret not found'
                    ],
                    404, // Not Found
                    "Failed to delete Mikrotik secret"
                );
                return;
            }

            foreach ($deleteSecrets as $secret) {
                $deleteQuery = (new Query('/ppp/secret/remove'))
                    ->where('name', $username)
                    ->equal('.id', $secret['.id']);
                $client->query($deleteQuery)->read();

                // LogHelper::logInfo(
                //     null,
                //     [
                //         'host' => $mikrotik->host ?? 'N/A',
                //         'username' => $username,
                //         'secret_id' => $secret['.id']
                //     ],
                //     200,
                //     "Successfully deleted Mikrotik secret"
                // );
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'username' => $username,
                    'error' => $e->getMessage()
                ],
                500, // Internal Server Error
                "Failed to delete Mikrotik secret"
            );
        }
    }

    public function deleteOneSecret($mikrotik, $secretId): void
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $deleteQuery = (new Query('/ppp/secret/remove'))
                ->where('.id', $secretId);
            $client->query($deleteQuery)->read();

            // LogHelper::logInfo(
            //     null,
            //     [
            //         'host' => $mikrotik->host ?? 'N/A',
            //         'secret_id' => $secretId
            //     ],
            //     200,
            //     "Successfully deleted Mikrotik secret"
            // );
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'secret_id' => $secretId,
                    'error' => $e->getMessage()
                ],
                500, // Internal Server Error
                "Failed to delete Mikrotik secret"
            );
        }
    }

    // public function deleteSecretByName($mikrotik, $username): void
    // {
    //     $client = $this->getMikrotik($mikrotik);
    //     $pppsecret = new Query('/ppp/secret/print');
    //     $pppsecret->where('name', $username);
    //     $deleteSecrets = $client->query($pppsecret)->read();
    //     foreach ($deleteSecrets as $secret) {
    //         $secret = (new Query('/ppp/secret/remove'))
    //             ->where('name', $username)
    //             ->equal('.id', $secret['.id']);
    //         $client->query($secret)->read();
    //     }
    // }

    // public function deleteOneSecret($mikrotik, $secretId)
    // {
    //     $client = $this->getMikrotik($mikrotik);
    //     $secret = (new Query('/ppp/secret/remove'))
    //         ->where('.id', $secretId)
    //         ->equal('.id', $secretId);
    //     $client->query($secret)->read();
    // }
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */

    public function getAllPppSecrets($mikrotik)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/secret/print');
            $result = $client->query($query)->read();

            // LogHelper::logInfo(
            //     null,
            //     ['host' => $mikrotik->host ?? 'N/A', 'count' => count($result)],
            //     200,
            //     "Successfully fetched all Mikrotik secrets"
            // );

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'error' => $e->getMessage()],
                500,
                "Failed to fetch all Mikrotik secrets"
            );
            return [];
        }
    }

    public function getSecretById($mikrotik, $id)
    {
        return $this->getSecret($mikrotik, ['.id' => $id], 'ID', $id);
    }

    public function getSecretByName($mikrotik, $username)
    {
        return $this->getSecret($mikrotik, ['name' => $username], 'Username', $username);
    }

    public function getSecretByProfile($mikrotik, $profile)
    {
        return $this->getSecret($mikrotik, ['profile' => $profile], 'Profile', $profile);
    }

    public function getSecretByNameProfile($mikrotik, $name, $profile)
    {
        return $this->getSecret($mikrotik, ['name' => $name, 'profile' => $profile], 'Name and Profile', "$name | $profile");
    }

    private function getSecret($mikrotik, $filters, $filterType, $filterValue)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/secret/print');
            foreach ($filters as $key => $value) {
                $query->where($key, $value);
            }

            $result = $client->query($query)->read();

            if (empty($result)) {
                LogHelper::logWarning(
                    null,
                    ['host' => $mikrotik->host ?? 'N/A', 'filter' => $filterValue],
                    200,
                    "No Mikrotik secrets found for $filterType"
                );
                return [];
            }

            // LogHelper::logInfo(
            //     null,
            //     ['host' => $mikrotik->host ?? 'N/A', 'filter' => $filterValue, 'count' => count($result)],
            //     200,
            //     "Successfully fetched Mikrotik secrets by $filterType"
            // );

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'filter' => $filterValue, 'error' => $e->getMessage()],
                500,
                "Failed to fetch Mikrotik secrets by $filterType"
            );
            return [];
        }
    }

    public function deleteActiveSecret($mikrotik, $username): void
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/active/print');
            $query->where('name', $username);
            $activeSecrets = $client->query($query)->read();

            if (empty($activeSecrets)) {
                LogHelper::logWarning(
                    null,
                    ['host' => $mikrotik->host ?? 'N/A', 'username' => $username],
                    200,
                    "No active secrets found to delete"
                );
                return;
            }

            foreach ($activeSecrets as $active) {
                $removeQuery = (new Query('/ppp/active/remove'))
                    ->where('name', $username)
                    ->equal('.id', $active['.id']);
                $client->query($removeQuery)->read();

                // LogHelper::logInfo(
                //     null,
                //     ['host' => $mikrotik->host ?? 'N/A', 'username' => $username, 'secret_id' => $active['.id']],
                //     200,
                //     "Successfully deleted active Mikrotik secret"
                // );
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'username' => $username, 'error' => $e->getMessage()],
                500,
                "Failed to delete active Mikrotik secret"
            );
        }
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */

    public function getPppActive($mikrotik)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/active/print');
            $result = $client->query($query)->read();

            // LogHelper::logInfo(
            //     null,
            //     ['host' => $mikrotik->host ?? 'N/A', 'count' => count($result)],
            //     200,
            //     "Successfully fetched all active Mikrotik PPP sessions"
            // );

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'error' => $e->getMessage()],
                500,
                "Failed to fetch active Mikrotik PPP sessions"
            );
            return [];
        }
    }

    // public function getPppActive($mikrotik)
    // {
    //     $client = $this->getMikrotik($mikrotik);
    //     $query = new Query('/ppp/active/print');

    //     return $client->query($query)->read();
    // }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */

    public function getOnePppSecretActive($mikrotik, $username)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = (new Query('/ppp/active/print'))->where('name', $username);
            $result = $client->query($query)->read();

            // LogHelper::logInfo(
            //     null,
            //     ['host' => $mikrotik->host ?? 'N/A', 'username' => $username, 'count' => count($result)],
            //     200,
            //     "Fetched active PPP secret by username"
            // );

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'username' => $username, 'error' => $e->getMessage()],
                500,
                "Failed to fetch active PPP secret by username"
            );
            return [];
        }
    }

    public function getPppSecretActiveByName($mikrotik)
    {
        try {
            $name = request()->username;
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = (new Query('/ppp/active/print'))->where('name', $name);
            $result = $client->query($query)->read();

            // LogHelper::logInfo(
            //     null,
            //     ['host' => $mikrotik->host ?? 'N/A', 'username' => $name, 'count' => count($result)],
            //     200,
            //     "Fetched active PPP secret by name"
            // );

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'username' => $name, 'error' => $e->getMessage()],
                500,
                "Failed to fetch active PPP secret by name"
            );
            return [];
        }
    }

    public function getPppSecretActiveByIp($mikrotik, $ipAddress)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = (new Query('/ppp/active/print'))->where('address', $ipAddress);
            $result = $client->query($query)->read();

            // LogHelper::logInfo(
            //     null,
            //     ['host' => $mikrotik->host ?? 'N/A', 'ip' => $ipAddress, 'count' => count($result)],
            //     200,
            //     "Fetched active PPP secret by IP"
            // );

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'ip' => $ipAddress, 'error' => $e->getMessage()],
                500,
                "Failed to fetch active PPP secret by IP"
            );
            return [];
        }
    }

    // PPP Profile
    public function getPppProfile($mikrotik)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/profile/print');
            $result = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'count' => count($result)],
                200,
                "Fetched all PPP profiles"
            );

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'error' => $e->getMessage()],
                500,
                "Failed to fetch PPP profiles"
            );
            return [];
        }
    }

    public function getPppProfileByName($mikrotik, $profileName)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = (new Query('/ppp/profile/print'))->where('name', $profileName);
            $result = $client->query($query)->read();

            // LogHelper::logInfo(
            //     null,
            //     ['host' => $mikrotik->host ?? 'N/A', 'profile' => $profileName, 'count' => count($result)],
            //     200,
            //     "Fetched PPP profile by name"
            // );

            return $result ?: 'error';
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'profile' => $profileName, 'error' => $e->getMessage()],
                500,
                "Failed to fetch PPP profile by name"
            );
            return 'error';
        }
    }

    public function updateRateLimitProfileSecret($mikrotik, $rateLimit, $profileName)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            $host = $mikrotik->host ?? 'Unknown Host';
            $name = $mikrotik->name ?? 'Unknown Name';
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            // 1. Fetch semua profile
            $profiles = $client->query(new Query('/ppp/profile/print'))->read();

            if (empty($profiles)) {
                throw new Exception("No profiles found in Mikrotik.");
            }

            // 2. Cari berdasarkan 'name'
            $targetProfile = collect($profiles)->firstWhere('name', $profileName);

            if (!$targetProfile) {
                LogHelper::logWarning(
                    null,
                    ['host' => $mikrotik->host ?? 'Unknown Host', 'profile_name' => $profileName],
                    404,
                    "PPP Profile not found during rate limit update"
                );
                throw new Exception("Profile '{$profileName}' not found in Mikrotik '{$name}' with host '{$host}'");
            }

            // 3. Update berdasarkan .id yang ditemukan
            $updateQuery = (new Query('/ppp/profile/set'))
                ->equal('.id', $targetProfile['.id'])  // << pakai .id hasil dari step 2
                ->equal('rate-limit', $rateLimit);

            $result = $client->query($updateQuery)->read();

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'profile_name' => $profileName, 'error' => $e->getMessage()],
                500,
                "Failed to update rate limit for PPP profile"
            );
            return [];
        }
    }

    public function createPppProfile($mikrotik, array $data)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/profile/add');

            foreach ($data as $key => $value) {
                // Lewati field kosong atau null, kecuali boolean false
                if ($value === null || $value === '') continue;

                $query->equal($key, $value);
            }

            $result = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'profile_data' => $data,
                    'result' => $result,
                ],
                200,
                "Created new PPP profile"
            );

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'profile_data' => $data,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to create new PPP profile"
            );
            return [];
        }
    }
}
