<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\AdminRedisReport\Scope;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private const XML_PATH_LOG_CRON_ENABLED = 'system/redis_report/cron_enable';
    private const XML_PATH_LOG_TRUNCATE_DAYS = 'system/redis_report/log_truncate_days';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isRedisReportLoggingCronEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_LOG_CRON_ENABLED);
    }

    public function getRedisReportLogTruncateDays(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_LOG_TRUNCATE_DAYS);
    }
}
