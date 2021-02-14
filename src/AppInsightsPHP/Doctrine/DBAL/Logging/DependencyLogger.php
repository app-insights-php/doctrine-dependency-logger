<?php

declare(strict_types=1);

namespace AppInsightsPHP\Doctrine\DBAL\Logging;

use AppInsightsPHP\Client\Client;
use Doctrine\DBAL\Logging\SQLLogger;

final class DependencyLogger implements SQLLogger
{
    public const DEFAULT_NAME = 'Doctrine DBAL';

    public const DEFAULT_TYPE = 'SQL';

    private $telemetryClient;

    private $sqlQuery;

    private $dependencyName;

    private $dependencyType;

    public function __construct(Client $telemetryClient, string $dependencyName = self::DEFAULT_NAME, string $dependencyType = self::DEFAULT_TYPE)
    {
        $this->telemetryClient = $telemetryClient;
        $this->dependencyName = $dependencyName;
        $this->dependencyType = $dependencyType;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null) : void
    {
        $this->sqlQuery = [
            'sql' => $sql,
            'startTime' => \time(),
            'startTimeMs' => (int) \round(\microtime(true) * 1000, 1),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery() : void
    {
        $this->telemetryClient->trackDependency(
            $this->dependencyName,
            $this->dependencyType,
            $this->sqlQuery['sql'],
            $this->sqlQuery['startTime'],
            (int) \round(\microtime(true) * 1000, 1) - $this->sqlQuery['startTimeMs'],
            true,
            null
        );

        $this->sqlQuery = null;
    }
}
