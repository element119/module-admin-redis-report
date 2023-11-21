<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\AdminRedisReport\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Cache\Backend\Redis;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class RedisInfo implements ArgumentInterface
{
    private CacheInterface $cache;
    private Redis $redis;
    private DeploymentConfig $deploymentConfig;

    public function __construct(
        CacheInterface $cache,
        DeploymentConfig $deploymentConfig
    ) {
        $this->cache = $cache;
        $this->deploymentConfig = $deploymentConfig;
        $this->redis = $this->cache->getFrontend()->getBackend();
    }

    public function get(): array
    {
        $redisInfo = $this->getAllRedisInfo();

        return [
            'version' => $redisInfo['redis_version'],
            'databases' => $this->getDatabaseKeyspaceInfo($redisInfo),
            'memory' => [
                'Used Memory' => $redisInfo['used_memory_human'],
                'Used Memory Peak' => $redisInfo['used_memory_peak_human'],
                'Used Memory Peak Percentage' => $redisInfo['used_memory_peak_perc'],
                'Used Memory Dataset Percentage' => $redisInfo['used_memory_dataset_perc'],
                'Percentage of Keys Used by Magento' => $this->getMagentoKeyUsagePercentage($redisInfo) . '%',
                'Total System Memory' => $redisInfo['total_system_memory_human'],
                'Max. Memory' => $redisInfo['maxmemory_human'],
                'Max. Memory Policy' => $redisInfo['maxmemory_policy'],
            ],
            'connections' => [
                'Cluster Enabled' => $redisInfo['cluster_enabled'],
                'Max. Clients' => $redisInfo['maxclients'],
                'Connected Clients' => $redisInfo['connected_clients'],
                'Cluster Connections' => $redisInfo['cluster_connections'],
                'Rejected Connections' => $redisInfo['rejected_connections'],
            ],
            'activity' => [
                'Uptime in Days' => $redisInfo['uptime_in_days'],
                'Uptime in Seconds' => $redisInfo['uptime_in_seconds'],
                'Keyspace Hits' => $redisInfo['keyspace_hits'],
                'Keyspace Misses' => $redisInfo['keyspace_misses'],
                'Hit Rate' => $this->redis->getHitMissPercentage() . '%',
                'Total Error Replies' => $redisInfo['total_error_replies'],
                'Evicted Keys' => $redisInfo['evicted_keys'],
                'Expired Keys' => $redisInfo['expired_keys'],
                'Used CPU Time (Sys)' => round((float)$redisInfo['used_cpu_sys'], 2) . ' seconds',
                'Used CPU Time (User)' => round((float)$redisInfo['used_cpu_user'], 2) . ' seconds',
                'RDB Last Save Time' => date('r', (int)$redisInfo['rdb_last_save_time']),
                'RDB Changes Since Last Save' => $redisInfo['rdb_changes_since_last_save'],
                'Total Reads Processed' => $redisInfo['total_reads_processed'],
                'Total Writes Processed' => $redisInfo['total_writes_processed'],
            ],
        ];
    }

    public function getDatabaseKeyspaceInfo(array $redisInfo): array
    {
        $redisDatabaseInfo = [];
        $dbKeys = preg_grep('/^db(\d*)/m', array_keys($redisInfo)); // db0, db1 ... db(n-1), db(n)

        foreach ($dbKeys as $dbKey) {
            // keyspace info e.g. key count, expiring key count, and average ttl
            $dbInfo = explode(',', $redisInfo[$dbKey]);

            foreach ($dbInfo as $info) {
                $data = explode('=', $info);

                array_key_exists($dbKey, $redisDatabaseInfo)
                    ? $redisDatabaseInfo[$dbKey] += [$data[0] => (int)$data[1]] // append
                    : $redisDatabaseInfo[$dbKey] = [$data[0] => (int)$data[1]]; // initialise
            }

            $avgTtlKey = array_key_last($redisDatabaseInfo[$dbKey]);
            $redisDatabaseInfo[$dbKey][$avgTtlKey] = ($redisDatabaseInfo[$dbKey][$avgTtlKey] / 1000) . ' seconds';
        }

        return $redisDatabaseInfo;
    }

    public function getMagentoKeyUsagePercentage(array $redisInfo): float
    {
        $cacheConfig = $this->deploymentConfig->get('cache');

        if (!isset($cacheConfig['frontend']['default']['id_prefix'])) {
            return -1.0;
        }

        $allTags = $this->redis->getTags() + $this->redis->getIds();
        $redisKeyPrefix = $cacheConfig['frontend']['default']['id_prefix'];
        $tagsWithPrefix = preg_grep('/^' . preg_quote($redisKeyPrefix) . '/m', $allTags);

        return round((count($tagsWithPrefix) / count($allTags)) * 100, 2);
    }

    /**
     * Get all Redis information.
     *
     * This function exists solely to ease extensibility.
     *
     * @link https://redis.io/commands/info/
     *
     * @return array
     */
    public function getAllRedisInfo(): array
    {
        return $this->redis->getInfo();
    }
}
