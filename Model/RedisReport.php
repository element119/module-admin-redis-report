<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\AdminRedisReport\Model;

use Element119\AdminRedisReport\Api\Data\RedisReportInterface;
use Element119\AdminRedisReport\Model\ResourceModel\RedisReport as RedisReportResourceModel;
use Magento\Framework\Model\AbstractModel;

class RedisReport extends AbstractModel implements RedisReportInterface
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(RedisReportResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId(): int
    {
        return (int)$this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId): self
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getReportData(): array
    {
        return $this->getData(json_decode(self::REPORT_DATA));
    }

    /**
     * @inheritDoc
     */
    public function setReportData(array $reportData): RedisReportInterface
    {
        return $this->setData(self::REPORT_DATA, json_encode($reportData));
    }

    /**
     * @inheritDoc
     */
    public function getChartData(): array
    {
        return $this->getData(json_decode(self::CHART_DATA));
    }

    /**
     * @inheritDoc
     */
    public function setChartData(array $chartData): RedisReportInterface
    {
        return $this->setData(self::CHART_DATA, json_encode($chartData));
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): RedisReportInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $updatedAt): RedisReportInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
