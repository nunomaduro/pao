<?php

declare(strict_types=1);

/** @codeCoverageIgnoreStart */

namespace Pao;

use AgentDetector\AgentDetector;

/** @var array<int, string>|null $argv */
$argv = $_SERVER['argv'] ?? null;

if (! is_array($argv) || $argv === []) {
    return;
}

$agent = AgentDetector::detect();

if (! $agent->isAgent) {
    return;
}

Execution::start($agent, $argv);
