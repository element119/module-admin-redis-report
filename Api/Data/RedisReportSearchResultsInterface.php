<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\AdminRedisReport\Api\Data;

use Element119\AdminRedisReport\Api\Data\RedisReportInterface;
use Magento\Framework\Api\SearchResultsInterface;

interface RedisReportSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return RedisReportInterface[]
     */
    public function getItems(): array;

    /**
     * @param RedisReportInterface[] $items
     * @return self
     */
    public function setItems(array $items): self;
}
