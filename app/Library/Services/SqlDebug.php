<?php

namespace App\Library\Services;

use App;
use App\Library\Services\Interfaces\SqlDebugServiceInterface;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Session;
use Illuminate\Support\Facades\File;

/*

Class to debug sql-requests with filtering by time or request and substring(table of fieldname).


To see only some requests filtered by table or field name add in TablesToFilter.json comma separated lines like :
`vote_items`, `users`, `active`

To see only sql requests filtered by time () in milliseconds ) write in debugQueryTimeInMs.json file valid integer value, like :
10

 */

class SqlDebug implements SqlDebugServiceInterface
{
    protected $debugQueryTimeInMs = -1;
    protected $tablesToFilterArray = [];
    protected $telescopeTablesList
        = [
            '`telescope_monitoring`',
            '`telescope_entries_tags`',
            '`telescope_entries`',
            '`sessions`'
        ];

    public function __construct()
    {
        $appEnvironment = App::environment();
        if ($appEnvironment === 'local' or $appEnvironment === 'testing') {
            $this->debugQueryTimeInMs = 0; // by default on local write ALL queries to file
        }

        try {
            // comma separated list of tables we want to see
            $tablesToFilter = trim(File::get(resource_path('TablesToFilter.json')));
            if ( ! empty($tablesToFilter)) {
                $this->tablesToFilterArray = Str::of($tablesToFilter)->explode(',');
            }

            // this file must keep value in milliseconds for filtering
            $debugQueryTimeInMs = File::get(resource_path('debugQueryTimeInMs.json'));
            if ( ! empty($debugQueryTimeInMs)) {
                $this->debugQueryTimeInMs = (int)$debugQueryTimeInMs;
            }
        } catch (FileNotFoundException $e) {
        }

    }

    public function replaceBindings($sql, $bindings)
    {
        if ( ! is_array($bindings)) {
            return $sql;
        }
        foreach ($bindings as $key => $binding) {
            $regex = is_numeric($key)
                ? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/"
                : "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";

            if ($binding === null) {
                $binding = 'null';
            } elseif ( ! is_int($binding) && ! is_float($binding)) {
                $binding = $this->quoteStringBinding($binding);
            }
            $sql = preg_replace($regex, $binding, $sql, 1);
        }

        return $sql;
    }

    public function writeSqlStatement(string $sqlStr, float $queryTime = null, ?array $bindings, bool $alwaysShowStatement)
    {
        $telescopeTableRequest = Str::contains($sqlStr, $this->telescopeTablesList);
        $skipThisRequest       = count($this->tablesToFilterArray) !== 0;
        $isSqlStrFound         = false;
        // we check that flag $alwaysShowStatement set to false - as some sql statements we need show ALWAYS
        if ( ! $alwaysShowStatement) {
            if (count($this->tablesToFilterArray) > 0) {
                foreach ($this->tablesToFilterArray as $tableToFilter) {
                    $isSqlStrFound = Str::contains(trim($sqlStr), trim($tableToFilter));
//                    $isSqlStrFound = strpos(trim($sqlStr), trim($tableToFilter));
                    if ($isSqlStrFound) {
                        break;
                    }
                }
                $skipThisRequest = ! $isSqlStrFound;
            }
        }
        if ($alwaysShowStatement) {
            $skipThisRequest = false;
        }

        if ( ! $telescopeTableRequest and ! $skipThisRequest) { // skip all telescopeTables Requests
            $sqlStr = $this->replaceBindings($sqlStr, $bindings);
            if ($this->debugQueryTimeInMs === 0) { // write ALL queries to file
                $this->writeSqlToLog($sqlStr, 'Time(in ms) ' . $queryTime . ' : ' . PHP_EOL);
                $this->writeSqlToLog('');
                $this->writeSqlToLog('');
            }

            // write ONLY queries execution time $queryTime takes more debugQueryTimeInMs)
            if ($this->debugQueryTimeInMs > 0) {
                if ( ! empty($queryTime) and $this->debugQueryTimeInMs <= $queryTime) { //
                    $this->writeSqlToLog('PASSED debugQueryTimeInMs ( ' . $this->debugQueryTimeInMs .
                                         ' ) parameter : ');
                    $this->writeSqlToLog($sqlStr, 'Time(in ms) ' . $queryTime . $this->getFilteredStringsInfo($alwaysShowStatement) . ' : ' . PHP_EOL);
                    $this->writeSqlToLog('');
                    $this->writeSqlToLog('');
                }
            }
        }
    } // public static function runSendUserCurrencySubscriptions(bool $from_cli = false)

    /**
     * Format the given bindings to strings.
     *
     * @param \Illuminate\Database\Events\QueryExecuted $event
     *
     * @return array
     */
    protected function formatBindings($event)
    {
        return $event->connection->prepareBindings($event->bindings);
    }


    /**
     * Add quotes to string bindings.
     *
     * @param string $binding
     *
     * @return string
     */
    protected function quoteStringBinding($binding)
    {
        $binding = \strtr($binding, [
            chr(26) => '\\Z',
            chr(8)  => '\\b',
            '"'     => '\"',
            "'"     => "\'",
            '\\'    => '\\\\',
        ]);

        return "'" . $binding . "'";
    }

    protected function getFilteredStringsInfo($alwaysShowStatement): string
    {
        if($alwaysShowStatement) return '';
        $retStr = '';
        if(!empty($this->tablesToFilterArray)) {
            foreach( $this->tablesToFilterArray as $tableToFilter ) {
                $retStr .= trim($tableToFilter) . ', ';
            }
        }

        return (!empty($retStr) ? '  ( filteres : ' : '') .  $this->trimRightSubString($retStr, ', ') . (!empty($retStr) ? ' )' : '') ;
    }

    protected function trimRightSubString(string $s, string $substr): string
    {
        preg_match('/(.*?)(' . preg_quote($substr, "/") . ')$/si', $s, $matches);
        if (! empty($matches[1])) {
            return $matches[1];
        }

        return $s;
    }

    protected function formatSql(string $sql): string
    {
        $spaceChar = '  ';
        $boldStart = '';
        $boldEnd   = '';
        $breakLine = PHP_EOL;
        $sql       = ' ' . $sql . ' ';
        $leftCond  = '~\b(?<![%\'])';
        $rightCond = '(?![%\'])\b~i';

        $sql = preg_replace(
            $leftCond . "insert[\s]+into" . $rightCond,
            $spaceChar . $spaceChar . $boldStart . 'INSERT INTO' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'insert' . $rightCond,
            $spaceChar . $boldStart . 'INSERT' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'delete' . $rightCond,
            $spaceChar . $boldStart . 'DELETE' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'values' . $rightCond,
            $breakLine . $spaceChar . $spaceChar . $boldStart . 'VALUES' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'update' . $rightCond,
            $spaceChar . $boldStart . 'UPDATE' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . "inner[\s]+join" . $rightCond,
            $breakLine . $spaceChar . $spaceChar . $boldStart . 'INNER JOIN' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . "straight[\s]+join" . $rightCond,
            $breakLine . $spaceChar . $spaceChar . $boldStart . 'STRAIGHT_JOIN' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . "left[\s]+join" . $rightCond,
            $breakLine . $spaceChar . $spaceChar . $boldStart . 'LEFT JOIN' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'select' . $rightCond,
            $spaceChar . $boldStart . 'SELECT' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'from' . $rightCond,
            $breakLine . $spaceChar . $spaceChar . $boldStart . 'FROM' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'where' . $rightCond,
            $breakLine . $spaceChar . $spaceChar . $boldStart . 'WHERE' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'group by' . $rightCond,
            $breakLine . $spaceChar . $spaceChar . 'GROUP BY' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'having' . $rightCond,
            $breakLine . $spaceChar . $boldStart . 'HAVING' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . "order[\s]+by" . $rightCond,
            $breakLine . $spaceChar . $spaceChar . $boldStart . 'ORDER BY' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'and' . $rightCond,
            $spaceChar . $spaceChar . $boldStart . 'AND' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'or' . $rightCond,
            $spaceChar . $spaceChar . $boldStart . 'OR' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'as' . $rightCond,
            $spaceChar . $spaceChar . $boldStart . 'AS' . $boldEnd,
            $sql
        );
        $sql = preg_replace(
            $leftCond . 'exists' . $rightCond,
            $breakLine . $spaceChar . $spaceChar . $boldStart . 'EXISTS' . $boldEnd,
            $sql
        );

        return $sql;
    }

    protected function writeSqlToLog($contents, string $description = '', string $fileName = ''): bool
    {
        $debug = config('app.debug');
        if ( ! $debug) {
            return false;
        }

        if (empty($description)) {
            $description = '';
        }
        try {
            if (empty($fileName)) {
                $fileName = storage_path() . '/logs/sql-tracing-' . Str::slug(config('app.name', 'ZYW')) . '.txt';
            }
            $fd = fopen($fileName, 'a+');
            if (is_array($contents)) {
                $contents = print_r($contents, true);
            }
            fwrite($fd, $description . $this->formatSql($contents) . chr(13));
            fclose($fd);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
