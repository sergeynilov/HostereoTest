<?php

namespace App\Library\Services\Interfaces;

interface SqlDebugServiceInterface
{

    public function writeSqlStatement(string $sqlStr, float $queryTime, ?array $bindings, bool $alwaysShowStatement);

}
