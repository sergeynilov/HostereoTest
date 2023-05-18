<?php

namespace App\Library\Facades;

use Carbon\Carbon;

class DateConv
{
    protected static $timeFormat = 'H:i';
    protected static $dateNumbersFormat = 'Y-m-d';
    protected static $datetimeNumbersFormat = 'Y-m-d H:i';

    protected static $dateAstextFormat = 'j F, Y';
    protected static $datetimeAstextFormat = 'j F, Y g:i:s A';

    public static function getFormattedTime($time = ''): string
    {
        if (empty($time)) {
            return '';
        }
        $value = Carbon::parse($time);

        return $value->format(self::$timeFormat);
    }


    public static function getFormattedDateTime($datetime, $outputFormat = ''): string
    {
        if (empty($datetime)) {
            return '';
        }
        if ( ! self::isValidTimeStamp($datetime)) {

            if ($outputFormat === \App\Enums\DatetimeOutputFormat::dofAgoFormat) {
                return Carbon::createFromTimestamp(strtotime($datetime))->diffForHumans();
            }
            $datetimeFormat = self::getDateTimeFormat(\App\Enums\DatetimeOutputFormat::dofAsText);
            $formattedValue = Carbon::createFromTimestamp(strtotime($datetime))->format($datetimeFormat);

            return $formattedValue;
        }

        if (self::isValidTimeStamp($datetime)) {
            $datetimeFormat = self::getDateTimeFormat(\App\Enums\DatetimeOutputFormat::dofAsText);
            $formattedValue = Carbon::createFromTimestamp($datetime)->format($datetimeFormat);

            return $formattedValue;
        }
    }

    public static function isValidTimeStamp($timestamp): bool
    {
        if (!isset($timestamp)) {
            return false;
        }
        if (gettype($timestamp) === 'object') {
            $timestamp = $timestamp->toDateTimeString();
        }

        return ((string)(int)$timestamp === (string)$timestamp)
               && ($timestamp <= PHP_INT_MAX)
               && ($timestamp >= ~PHP_INT_MAX);
    }

    public static function getFormattedDate($date, $dateFormat = 'mysql', $outputFormat = ''): string
    {
        if (empty($date)) {
            return '';
        }
        $dateCarbonFormat = config('app.date_carbon_format');
        if ($dateFormat === 'mysql') {
            $dateFormat = self::getDateFormat(\App\Enums\DatetimeOutputFormat::dofAsText);
            $date       = Carbon::createFromTimestamp(strtotime($date))->format($dateFormat);

            return $date;
        }

        if (self::isValidTimeStamp($date)) {
            if (strtolower($outputFormat) === \App\Enums\DatetimeOutputFormat::dofAsText) {
                $dateCarbonFormat_as_text = config('app.date_carbon_format_as_text', '%d %B, %Y');

                return Carbon::createFromTimestamp(
                    $date,
                    Config::get('app.timezone')
                )->formatLocalized($dateCarbonFormat_as_text);
            }
            if (strtolower($outputFormat) === 'pickdate') {
                $dateCarbonFormat_as_pickdate = config('app.pickdate_format_submit');

                return Carbon::createFromTimestamp(
                    $date,
                    Config::get('app.timezone')
                )->format($dateCarbonFormat_as_pickdate);
            }

            return Carbon::createFromTimestamp(
                $date,
                Config::get('app.timezone')
            )->format($dateCarbonFormat);
        }
        $A = preg_split("/ /", $date);
        if (count($A) === 2) {
            $date = $A[0];
        }
        $a = Carbon::createFromFormat($dateCarbonFormat, $date);
        $b = $a->format(self::getDateFormat(\App\Enums\DatetimeOutputFormat::dofAsText));

        return $a->format(self::getDateFormat(\App\Enums\DatetimeOutputFormat::dofAsText));
    }

    public static function getDateFormat($format = ''): string
    {
        if (strtolower($format) === \App\Enums\DatetimeOutputFormat::dofAsNumbers) {
            return self::$dateNumbersFormat;
        }
        if (strtolower($format) === \App\Enums\DatetimeOutputFormat::dofAsText) {
            return self::$dateAstextFormat;
        }

        return self::$dateNumbersFormat;
    }


    public static function getDateTimeFormat($format = ''): string
    {
        if (strtolower($format) === \App\Enums\DatetimeOutputFormat::dofAsNumbers) {
            return self::$datetimeNumbersFormat;
        }
        if (strtolower($format) === \App\Enums\DatetimeOutputFormat::dofAsText) {
            return self::$datetimeAstextFormat;
        }

        return self::$datetimeNumbersFormat;
    }

}
