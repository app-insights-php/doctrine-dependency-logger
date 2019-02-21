<?php

declare (strict_types=1);

namespace AppInsightsPHP\Doctrine\DBAL\Logging;

use AppInsightsPHP\Client\Client;
use Doctrine\DBAL\Logging\SQLLogger;

final class DependencyLogger implements SQLLogger
{
    private $telemetryClient;
    private $sqlQuery;

    public function __construct(Client $telemetryClient)
    {
        $this->telemetryClient = $telemetryClient;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->sqlQuery = [
            'sql' => $sql,
            'startTime' => time(),
            'startTimeMs' => (int) round(microtime(true) * 1000, 1)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        $this->telemetryClient->trackDependency(
            'Doctrine DBAL',
            'SQL',
            $this->sqlQuery['sql'],
            $this->sqlQuery['startTime'],
            (int) round(microtime(true) * 1000, 1) - $this->sqlQuery['startTimeMs'],
            true,
            null
        );

        unset($this->sqlQuery);
    }
}