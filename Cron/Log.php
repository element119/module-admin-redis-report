<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\AdminRedisReport\Cron;

use Element119\AdminRedisReport\Api\Data\RedisReportInterface;
use Element119\AdminRedisReport\Api\RedisReportRepositoryInterface;
use Element119\AdminRedisReport\Model\RedisInfo;
use Element119\AdminRedisReport\Model\RedisReportFactory;
use Magento\Framework\Exception\CouldNotSaveException;

class Log
{
    private RedisReportRepositoryInterface $redisReportRepository;
    private RedisInfo $redisInfo;
    private RedisReportFactory $redisReportFactory;

    public function __construct(
        RedisReportRepositoryInterface $redisReportRepository,
        RedisInfo $redisInfo,
        RedisReportFactory $redisReportFactory
    ) {
        $this->redisReportRepository = $redisReportRepository;
        $this->redisInfo = $redisInfo;
        $this->redisReportFactory = $redisReportFactory;
    }

    /**
     * Store Redis report data.
     *
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(): void
    {
        /** @var RedisReportInterface $redisReportModel */
        $redisReportModel = $this->redisReportFactory->create();
        $redisReportModel->setReportData($this->redisInfo->get());
        $this->redisReportRepository->save($redisReportModel);
    }
}
