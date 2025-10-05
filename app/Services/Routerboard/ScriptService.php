<?php

namespace App\Services\Routerboard;

use Exception;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;
use App\Helpers\LogHelper;
use App\Helpers\SnmpHelper;

class ScriptService
{
    public function getAllScript($routerboard, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = new Query('/system/script/print');
            $scripts = $client->query($query)->read();

            if (empty($scripts)) {
                LogHelper::logWarning(
                    null,
                    [],
                    404,
                    "No scripts found",
                    $traceId
                );
                return [];
            }

            return $scripts;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get all scripts",
                $traceId
            );
            throw $e;
        }
    }

    public function getScriptByName($routerboard, $scheduleName, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = (new Query('/system/script/print'))
                ->where('name', $scheduleName);
            $script = $client->query($query)->read();

            if (empty($script)) {
                LogHelper::logWarning(
                    null,
                    ['script_name' => $scheduleName],
                    404,
                    "Script not found by name",
                    $traceId
                );
                return [];
            }

            return $script;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'script_name' => $scheduleName,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get script by name",
                $traceId
            );
            throw $e;
        }
    }

    public function getScriptById($routerboard, $id, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = (new Query('/system/script/print'))
                ->where('.id', $id);
            $script = $client->query($query)->read();

            if (empty($script)) {
                LogHelper::logWarning(
                    null,
                    ['script_id' => $id],
                    404,
                    "Script not found by ID",
                    $traceId
                );
                return [];
            }

            return $script;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'script_id' => $id,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to get script by ID",
                $traceId
            );
            throw $e;
        }
    }

    public function addScript($routerboard, $nameScript, $sourceScript, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = (new Query('/system/script/add'))
                ->equal('name', $nameScript)
                ->equal('source', $sourceScript)
                ->equal('comment', 'create_by_customer_management');

            $response = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'script_name' => $nameScript,
                    'source_length' => strlen($sourceScript),
                    'response' => $response
                ],
                200,
                "Script added successfully",
                $traceId
            );

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'script_name' => $nameScript,
                    'source_length' => strlen($sourceScript),
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to add script",
                $traceId
            );
            throw $e;
        }
    }

    public function addAutoisolirDueDate($routerboard, $rosVersion, $nameScript, $dueDate, $unpayment, $profile, $address_list, string $traceId)
    {
        try {
            if ($rosVersion == '1') {
                $sourceScript = '
                /ip firewall address-list remove [/ip firewall address-list find list="' . $address_list . '"]
                :local day [:pick [/system clock get date] 4 6]
                :if (day >= "' . $dueDate . '") do={
                :local userppp
                :foreach v in=[/ppp secret find where comment="' . $unpayment . '"] do={
                :set userppp ( userppp [/ppp secret get $v name])
                /ppp active remove [find name=$userppp]
                /ppp secret set profile=' . $profile . ' [find name=$userppp];
                }

                :local userstatic
                foreach v in=[/queue simple find where comment="' . $unpayment . '"] do={
                :set userstatic ( userstatic [/queue simple get $v target])
                /ip firewall address-list add list=' . $address_list . ' address=$userstatic
                }
                }';
                $comment = 'create_by_customer_management_mikrotik_<7.10';
            } else {
                $sourceScript = '
                /ip firewall address-list remove [/ip firewall address-list find list="' . $address_list . '"]
                :local day [:pick [/system clock get date] 8 10]
                :if (day >= "' . $dueDate . '") do={
                :local userppp
                :foreach v in=[/ppp/secret find comment="' . $unpayment . '"] do={
                :set userppp ( userppp [/ppp secret get $v name])
                /ppp active remove [find name=$userppp]
                /ppp secret set profile=' . $profile . ' [find name=$userppp];
                }

                :local userstatic
                foreach v in=[/queue/simple find comment="' . $unpayment . '"] do={
                :set userstatic ( userstatic [/queue simple get $v target])
                /ip firewall address-list add list=' . $address_list . ' address=$userstatic
                }}';
                $comment = 'create_by_customer_management_mikrotik_>=7.10';
            }

            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = (new Query('/system/script/add'))
                ->equal('name', $nameScript)
                ->equal('source', $sourceScript)
                ->equal('comment', $comment);

            $response = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'script_name' => $nameScript,
                    'ros_version' => $rosVersion,
                    'due_date' => $dueDate,
                    'profile' => $profile,
                    'response' => $response
                ],
                200,
                "Autoisolir due date script added successfully",
                $traceId
            );

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'script_name' => $nameScript,
                    'ros_version' => $rosVersion,
                    'due_date' => $dueDate,
                    'profile' => $profile,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to add autoisolir due date script",
                $traceId
            );
            throw $e;
        }
    }

    public function addAutoisolirActivationDate($routerboard, $rosVersion, $nameScript, $unpayment, $profile, $address_list, string $traceId)
    {
        try {
            if ($rosVersion == '1') {
                $sourceScript = '
                :local date [/system clock get date]
                :local yyyy  [:pick $date 7 11]
                :local MM ([:find "janfebmaraprmayjunjulaugsepoctnovdec" [:pick $date 0 3] ] / 3 + 1)
                :if ($MM < 10) do={:set MM "0$MM"}
                :local dd    [:pick $date 4 6]
                /ip firewall address-list remove [/ip firewall address-list find list="' . $address_list . '"]

                :for e from 1 to $dd do={
                :if ($e < 10) do={:set e "0$e"}
                :local duedate    "' . $unpayment . '_$e_$MM_$yyyy"
                :local userppp
                :foreach v in=[/ppp secret find where comment=$duedate] do={
                :set userppp ( userppp [/ppp secret get $v name])
                /ppp active remove [find name=$userppp]
                /ppp secret set profile=' . $profile . ' [find name=$userppp];
                }

                :local userstatic
                foreach v in=[/queue simple find where comment="$duedate"] do={
                :set userstatic ( userstatic [/queue simple get $v target])
                /ip firewall address-list add list=' . $address_list . ' address=$userstatic
                }}';
                $comment = 'create_by_customer_management_mikrotik_<7.10';
            } else {
                $sourceScript = '
                :local date [/system clock get date]
                :local yyyy  [:pick $date 0 4]
                :local MM    [:pick $date 5 7]
                :local dd    [:pick $date 8 10]
                /ip firewall address-list remove [/ip firewall address-list find list="' . $address_list . '"]

                :for e from 1 to $dd do={
                :if ($e < 10) do={:set e "0$e"}
                :local duedate    "' . $unpayment . '_$e_$MM_$yyyy"
                :local userppp
                foreach v in=[/ppp/secret find comment="$duedate"] do={
                :set userppp ( userppp [/ppp secret get $v name])
                /ppp active remove [find name=$userppp]
                /ppp secret set profile=' . $profile . ' [find name=$userppp];
                }

                :local userstatic
                foreach v in=[/queue/simple find comment="$duedate"] do={
                :set userstatic ( userstatic [/queue simple get $v target])
                /ip firewall address-list add list=' . $address_list . ' address=$userstatic
                }}';
                $comment = 'create_by_customer_management_mikrotik_>=7.10';
            }

            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $query = (new Query('/system/script/add'))
                ->equal('name', $nameScript)
                ->equal('source', $sourceScript)
                ->equal('comment', $comment);

            $response = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'script_name' => $nameScript,
                    'ros_version' => $rosVersion,
                    'profile' => $profile,
                    'response' => $response
                ],
                200,
                "Autoisolir activation date script added successfully",
                $traceId
            );

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'script_name' => $nameScript,
                    'ros_version' => $rosVersion,
                    'profile' => $profile,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to add autoisolir activation date script",
                $traceId
            );
            throw $e;
        }
    }

    public function setSourceScript($routerboard, $scriptId, $sourceScript, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $scripts = new Query('/system/script/print');
            $scripts->where('.id', $scriptId);
            $resultScripts = $client->query($scripts)->read();

            if (empty($resultScripts)) {
                LogHelper::logWarning(
                    null,
                    ['script_id' => $scriptId],
                    404,
                    "Script not found for update",
                    $traceId
                );
                return null;
            }

            foreach ($resultScripts as $script) {
                $query = (new Query('/system/script/set'))
                    ->where('.id', $scriptId)
                    ->equal('.id', $script['.id'])
                    ->equal('source', $sourceScript);

                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'script_id' => $scriptId,
                        'source_length' => strlen($sourceScript),
                        'response' => $response
                    ],
                    200,
                    "Script source updated successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'script_id' => $scriptId,
                    'source_length' => strlen($sourceScript),
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to update script source",
                $traceId
            );
            throw $e;
        }
    }

    public function setAutoisolirDueDate($routerboard, $rosVersion, $scriptId, $dueDate, $unpayment, $profile, $address_list, string $traceId)
    {
        try {
            if ($rosVersion == '1') {
                $sourceScript = '
                /ip firewall address-list remove [/ip firewall address-list find list="' . $address_list . '"]
                :local day [:pick [/system clock get date] 4 6]
                :if (day >= "' . $dueDate . '") do={
                :local userppp
                :foreach v in=[/ppp secret find where comment="' . $unpayment . '"] do={
                :set userppp ( userppp [/ppp secret get $v name])
                /ppp active remove [find name=$userppp]
                /ppp secret set profile=' . $profile . ' [find name=$userppp];
                }

                :local userstatic
                foreach v in=[/queue simple find where comment="' . $unpayment . '"] do={
                :set userstatic ( userstatic [/queue simple get $v target])
                /ip firewall address-list add list=' . $address_list . ' address=$userstatic
                }}';
            } else {
                $sourceScript = '
                /ip firewall address-list remove [/ip firewall address-list find list="' . $address_list . '"]
                :local day [:pick [/system clock get date] 8 10]
                :if (day >= "' . $dueDate . '") do={
                :local userppp
                :foreach v in=[/ppp/secret find comment="' . $unpayment . '"] do={
                :set userppp ( userppp [/ppp secret get $v name])
                /ppp active remove [find name=$userppp]
                /ppp secret set profile=' . $profile . ' [find name=$userppp];
                }

                :local userstatic
                foreach v in=[/queue/simple find comment="' . $unpayment . '"] do={
                :set userstatic ( userstatic [/queue simple get $v target])
                /ip firewall address-list add list=' . $address_list . ' address=$userstatic
                }}';
            }

            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $scripts = new Query('/system/script/print');
            $scripts->where('.id', $scriptId);
            $resultScripts = $client->query($scripts)->read();

            if (empty($resultScripts)) {
                LogHelper::logWarning(
                    null,
                    ['script_id' => $scriptId],
                    404,
                    "Script not found for autoisolir due date update",
                    $traceId
                );
                return null;
            }

            foreach ($resultScripts as $script) {
                $query = (new Query('/system/script/set'))
                    ->where('.id', $scriptId)
                    ->equal('.id', $script['.id'])
                    ->equal('source', $sourceScript);

                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'script_id' => $scriptId,
                        'ros_version' => $rosVersion,
                        'due_date' => $dueDate,
                        'profile' => $profile,
                        'response' => $response
                    ],
                    200,
                    "Autoisolir due date script updated successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'script_id' => $scriptId,
                    'ros_version' => $rosVersion,
                    'due_date' => $dueDate,
                    'profile' => $profile,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to update autoisolir due date script",
                $traceId
            );
            throw $e;
        }
    }

    public function setAutoisolirActivationDate($routerboard, $rosVersion, $scriptId, $unpayment, $profile, $address_list, string $traceId)
    {
        try {
            if ($rosVersion == '1') {
                $sourceScript = '
                :local date [/system clock get date]
                :local yyyy  [:pick $date 7 11]
                :local MM ([:find "janfebmaraprmayjunjulaugsepoctnovdec" [:pick $date 0 3] ] / 3 + 1)
                :if ($MM < 10) do={:set MM "0$MM"}
                :local dd    [:pick $date 4 6]
                /ip firewall address-list remove [/ip firewall address-list find list="' . $address_list . '"]

                :for e from 1 to $dd do={
                :if ($e < 10) do={:set e "0$e"}
                :local duedate    "' . $unpayment . '_$e_$MM_$yyyy"
                :local userppp
                :foreach v in=[/ppp secret find where comment=$duedate] do={
                :set userppp ( userppp [/ppp secret get $v name])
                /ppp active remove [find name=$userppp]
                /ppp secret set profile=' . $profile . ' [find name=$userppp];
                }

                :local userstatic
                foreach v in=[/queue simple find where comment="$duedate"] do={
                :set userstatic ( userstatic [/queue simple get $v target])
                /ip firewall address-list add list=' . $address_list . ' address=$userstatic
                }}';
            } else {
                $sourceScript = '
                :local date [/system clock get date]
                :local yyyy  [:pick $date 0 4]
                :local MM    [:pick $date 5 7]
                :local dd    [:pick $date 8 10]
                /ip firewall address-list remove [/ip firewall address-list find list="' . $address_list . '"]

                :for e from 1 to $dd do={
                :if ($e < 10) do={:set e "0$e"}
                :local duedate    "' . $unpayment . '_$e_$MM_$yyyy"
                :local userppp
                foreach v in=[/ppp/secret find comment="$duedate"] do={
                :set userppp ( userppp [/ppp secret get $v name])
                /ppp active remove [find name=$userppp]
                /ppp secret set profile=' . $profile . ' [find name=$userppp];
                }

                :local userstatic
                foreach v in=[/queue/simple find comment="$duedate"] do={
                :set userstatic ( userstatic [/queue simple get $v target])
                /ip firewall address-list add list=' . $address_list . ' address=$userstatic
                }}';
            }

            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $scripts = new Query('/system/script/print');
            $scripts->where('.id', $scriptId);
            $resultScripts = $client->query($scripts)->read();

            if (empty($resultScripts)) {
                LogHelper::logWarning(
                    null,
                    ['script_id' => $scriptId],
                    404,
                    "Script not found for autoisolir activation date update",
                    $traceId
                );
                return null;
            }

            foreach ($resultScripts as $script) {
                $query = (new Query('/system/script/set'))
                    ->where('.id', $scriptId)
                    ->equal('.id', $script['.id'])
                    ->equal('source', $sourceScript);

                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'script_id' => $scriptId,
                        'ros_version' => $rosVersion,
                        'profile' => $profile,
                        'response' => $response
                    ],
                    200,
                    "Autoisolir activation date script updated successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'script_id' => $scriptId,
                    'ros_version' => $rosVersion,
                    'profile' => $profile,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to update autoisolir activation date script",
                $traceId
            );
            throw $e;
        }
    }

    public function removeScript($routerboard, $scriptId, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($routerboard, $traceId);
            $scripts = new Query('/system/script/print');
            $scripts->where('.id', $scriptId);
            $resultScripts = $client->query($scripts)->read();

            if (empty($resultScripts)) {
                LogHelper::logWarning(
                    null,
                    ['script_id' => $scriptId],
                    404,
                    "Script not found for removal",
                    $traceId
                );
                return null;
            }

            foreach ($resultScripts as $script) {
                $query = (new Query('/system/script/remove'))
                    ->where('.id', $scriptId)
                    ->equal('.id', $script['.id']);

                $response = $client->query($query)->read();

                LogHelper::logInfo(
                    null,
                    [
                        'script_id' => $scriptId,
                        'response' => $response
                    ],
                    200,
                    "Script removed successfully",
                    $traceId
                );

                return $response;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'script_id' => $scriptId,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to remove script",
                $traceId
            );
            throw $e;
        }
    }
}
