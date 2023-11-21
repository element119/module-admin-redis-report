<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\AdminRedisReport\Api;

use Element119\AdminRedisReport\Api\Data\RedisReportInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface RedisReportRepositoryInterface
{
    public const MAIN_TABLE = 'e119_redis_report';

    /**
     * @param RedisReportInterface $redisReport
     * @return RedisReportInterface
     * @throws CouldNotSaveException
     */
    public function save(RedisReportInterface $redisReport): RedisReportInterface;

    /**
     * @param int $redisReportId
     * @return RedisReportInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $redisReportId): RedisReportInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults;

    /**
     * @param RedisReportInterface $redisReport
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(RedisReportInterface $redisReport): bool;

    /**
     * @param int $redisReportId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $redisReportId): bool;
}
