<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\AdminRedisReport\Cron;

use Element119\AdminRedisReport\Api\Data\RedisReportInterface;
use Element119\AdminRedisReport\Api\RedisReportRepositoryInterface;
use Element119\AdminRedisReport\Scope\Config;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime;

class Clean
{
    private RedisReportRepositoryInterface $redisReportRepository;
    private Config $moduleConfig;
    private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;
    private DateTimeFactory $dateTimeFactory;

    public function __construct(
        RedisReportRepositoryInterface $redisReportRepository,
        Config $moduleConfig,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->redisReportRepository = $redisReportRepository;
        $this->moduleConfig = $moduleConfig;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Delete old Redis report data.
     *
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(): void
    {
        if (!($days = $this->moduleConfig->getRedisReportLogTruncateDays())) {
            return;
        }

        $lastException = null;

        /** @var DateTime $cutoff */
        $cutoff = $this->dateTimeFactory->create(date(DateTime::DATETIME_PHP_FORMAT, strtotime("-$days day")));

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->addFilter(
            RedisReportInterface::CREATED_AT,
            $cutoff,
            'lt'
        )->create();

        /** @var RedisReportInterface[] $redisReportsToDelete */
        $redisReportsToDelete = $this->redisReportRepository->getList($searchCriteria)->getItems();

        foreach ($redisReportsToDelete as $redisReport) {
            try {
                $this->redisReportRepository->delete($redisReport);
            } catch (CouldNotDeleteException $e) {
                $lastException = $e; // store exception as not to stop execution
            }
        }

        if ($lastException instanceof Exception) {
            throw $lastException; // re-throw exception to make noise
        }
    }
}
