<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\AdminRedisReport\Model\ResourceModel;

use Element119\AdminRedisReport\Api\Data\RedisReportInterface;
use Element119\AdminRedisReport\Api\RedisReportRepositoryInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RedisReport extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(RedisReportRepositoryInterface::MAIN_TABLE, RedisReportInterface::ENTITY_ID);
    }
}
