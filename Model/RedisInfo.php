<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\AdminRedisReport\Model;

use Cm_Cache_Backend_Redis;
use Element119\AdminRedisReport\Api\Data\RedisReportInterface;
use Element119\AdminRedisReport\Api\RedisReportRepositoryInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Cache\Backend\Redis;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Zend_Db_Statement_Exception;

class RedisInfo implements ArgumentInterface
{
    private CacheInterface $cache;
    private DeploymentConfig $deploymentConfig;
    private ResourceConnection $resourceConnection;

    /** @var Redis|Cm_Cache_Backend_Redis */
    private $redis;

    public function __construct(
        CacheInterface $cache,
        DeploymentConfig $deploymentConfig,
        ResourceConnection $resourceConnection
    ) {
        $this->cache = $cache;
        $this->deploymentConfig = $deploymentConfig;
        $this->resourceConnection = $resourceConnection;
        $this->redis = $this->cache->getFrontend()->getBackend();
    }

    public function get(): array
    {
        if (!($redisInfo = $this->getAllRedisInfo())) {
            return [];
        }

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
            'chart-data' => [
                'memory' => [
                    'Used Memory' => $redisInfo['used_memory'],
                    'Used Memory Peak' => $redisInfo['used_memory_peak'],
                    'Used Memory Peak Percentage' => $redisInfo['used_memory_peak_perc'],
                    'Used Memory Dataset Percentage' => $redisInfo['used_memory_dataset_perc'],
                    'Percentage of Keys Used by Magento' => $this->getMagentoKeyUsagePercentage($redisInfo),
                ],
                'activity' => [
                    'Keyspace Hits' => $redisInfo['keyspace_hits'],
                    'Keyspace Misses' => $redisInfo['keyspace_misses'],
                    'Hit Rate' => $this->redis->getHitMissPercentage(),
                    'Total Error Replies' => $redisInfo['total_error_replies'],
                    'Evicted Keys' => $redisInfo['evicted_keys'],
                    'Expired Keys' => $redisInfo['expired_keys'],
                    'Used CPU Time (Sys)' => round((float)$redisInfo['used_cpu_sys'], 2),
                    'Used CPU Time (User)' => round((float)$redisInfo['used_cpu_user'], 2),
                    'Total Reads Processed' => $redisInfo['total_reads_processed'],
                    'Total Writes Processed' => $redisInfo['total_writes_processed'],
                ],
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
     * Return historic Redis data, keyed by creation timestamp.
     *
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    public function getHistoricRedisReportData(): array
    {
        $reportData = [];
        $connection = $this->resourceConnection->getConnection();
        $redisReportSelect = $connection->select()->from(
            $this->resourceConnection->getTableName(RedisReportRepositoryInterface::MAIN_TABLE),
            [
                RedisReportInterface::REPORT_DATA,
                RedisReportInterface::CHART_DATA,
                RedisReportInterface::CREATED_AT,
            ]
        );

        foreach ($connection->query($redisReportSelect)->fetchAll() as $dbData) {
            $reportData[$dbData[RedisReportInterface::CREATED_AT]] = json_decode(
                $dbData[RedisReportInterface::REPORT_DATA], true
            );

            if ($dbData[RedisReportInterface::CHART_DATA]) {
                $chartData = json_decode($dbData[RedisReportInterface::CHART_DATA], true);
                $chartData[RedisReportInterface::CREATED_AT] = $dbData[RedisReportInterface::CREATED_AT];

                $reportData[$dbData[RedisReportInterface::CREATED_AT]][RedisReportInterface::CHART_DATA] = $chartData;
            }
        }

        return $reportData;
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
        return $this->redis instanceof Cm_Cache_Backend_Redis && method_exists($this->redis, 'getInfo')
            ? $this->redis->getInfo()
            : [];
    }
}
