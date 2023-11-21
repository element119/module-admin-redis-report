<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\AdminRedisReport\Model;

use Element119\AdminRedisReport\Api\Data\RedisReportInterface;
use Element119\AdminRedisReport\Api\Data\RedisReportInterfaceFactory;
use Element119\AdminRedisReport\Api\Data\RedisReportSearchResultsInterfaceFactory;
use Element119\AdminRedisReport\Api\RedisReportRepositoryInterface;
use Element119\AdminRedisReport\Model\ResourceModel\RedisReport as RedisReportResource;
use Element119\AdminRedisReport\Model\ResourceModel\RedisReport\CollectionFactory as RedisReportCollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class RedisReportRepository implements RedisReportRepositoryInterface
{
    private RedisReportCollectionFactory $redisReportCollectionFactory;
    private RedisReportInterfaceFactory $redisReportFactory;
    private RedisReportResource $resource;
    private RedisReportSearchResultsInterfaceFactory $searchResultsFactory;
    private CollectionProcessorInterface $collectionProcessor;

    public function __construct(
        RedisReportCollectionFactory $redisReportCollectionFactory,
        RedisReportInterfaceFactory $redisReportFactory,
        RedisReportResource $resource,
        RedisReportSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->redisReportCollectionFactory = $redisReportCollectionFactory;
        $this->redisReportFactory = $redisReportFactory;
        $this->resource = $resource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(RedisReportInterface $redisReport): RedisReportInterface
    {
        try {
            $this->resource->save($redisReport);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save the report: %1', $e->getMessage()));
        }

        return $redisReport;
    }

    /**
     * @inheritDoc
     */
    public function getById($redisReportId): RedisReportInterface
    {
        $redisReport = $this->redisReportFactory->create();
        $this->resource->load($redisReport, $redisReportId);

        if (!$redisReport->getId()) {
            throw new NoSuchEntityException(__('Redis report with entity ID "%1" does not exist.', $redisReportId));
        }

        return $redisReport;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults
    {
        $collection = $this->redisReportCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $items = [];

        foreach ($collection as $redisReport) {
            $items[] = $redisReport;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(RedisReportInterface $redisReport): bool
    {
        try {
            /** @var RedisReportInterface $redisReportModel */
            $redisReportModel = $this->redisReportFactory->create();

            $this->resource->load($redisReportModel, $redisReport->getEntityId());
            $this->resource->delete($redisReportModel);
        } catch (Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the Redis report with ID: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($redisReportId): bool
    {
        return $this->delete($this->getById($redisReportId));
    }
}
