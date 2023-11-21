<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\AdminRedisReport\Model\ResourceModel\RedisReport;

use Element119\AdminRedisReport\Model\RedisReport;
use Element119\AdminRedisReport\Model\ResourceModel\RedisReport as RedisReportResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /** @inheritDoc */
    protected $_idFieldName = 'entity_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(RedisReport::class, RedisReportResourceModel::class);
    }
}
