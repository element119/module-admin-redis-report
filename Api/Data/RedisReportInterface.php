<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\AdminRedisReport\Api\Data;

interface RedisReportInterface
{
    public const ENTITY_ID = 'entity_id';
    public const REPORT_DATA = 'report_data';
    public const CHART_DATA = 'chart_data';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getEntityId(): int;

    /**
     * @param int|string $entityId
     * @return self
     */
    public function setEntityId($entityId): self;

    /**
     * @return array
     */
    public function getReportData(): array;

    /**
     * @param array $reportData
     * @return self
     */
    public function setReportData(array $reportData): self;

    /**
     * @return array
     */
    public function getChartData(): array;

    /**
     * @param array $chartData
     * @return self
     */
    public function setChartData(array $chartData): self;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     * @return self
     */
    public function setCreatedAt(string $createdAt): self;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param string $updatedAt
     * @return self
     */
    public function setUpdatedAt(string $updatedAt): self;
}
