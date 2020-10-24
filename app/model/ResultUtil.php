<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use DateInterval;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

class ResultUtil
{

    static public function sort(array &$table, array $keys)
    {
        if (sizeof($table) > 0) {
            $indexes = array();
            foreach ($table as $index => $row) {
                foreach ($keys as $index2 => $row2) {
                    $indexes[$index2][$index] = $row[$index2];
                }
            }

            if (count($keys) == 1) {
                foreach ($keys as $index2 => $row2) {
                    $keys1 = $indexes[$index2];
                    $sort1 = $row2;
                }
                array_multisort($keys1, $sort1, $table);
            } elseif (count($keys) == 2) {
                $counter = 0;
                foreach ($keys as $index2 => $row2) {
                    $counter++;
                    if ($counter == 1) {
                        $keys1 = $indexes[$index2];
                        $sort1 = $row2;
                    } else {
                        $keys2 = $indexes[$index2];
                        $sort2 = $row2;
                    }
                }
                array_multisort($keys1, $sort1, $keys2, $sort2, $table);
            } elseif (count($keys) == 3) {
                $counter = 0;
                foreach ($keys as $index2 => $row2) {
                    $counter++;
                    if ($counter == 1) {
                        $keys1 = $indexes[$index2];
                        $sort1 = $row2;
                    } elseif ($counter == 2) {
                        $keys2 = $indexes[$index2];
                        $sort2 = $row2;
                    } else {
                        $keys3 = $indexes[$index2];
                        $sort3 = $row2;
                    }
                }
                array_multisort($keys1, $sort1, $keys2, $sort2, $keys3, $sort3, $table);
            }
        }
    }

    static public function countPos(array &$table, string $valueField, string $orderField)
    {
        ResultUtil::sort($table, array($valueField => SORT_DESC));
        $pos = 0;
        $value = 0;
        $counter = 0;
        foreach ($table as &$row) {
            $counter++;
            if ($row[$valueField] <> $value) {
                $pos = $counter;
                $value = $row[$valueField];
            }
            $row[$orderField] = $pos;
        }
    }

    static public function speed(DateInterval $time, float $length): ?float
    {
        $timeFrac = ((int)$time->h * 3600 + (int)$time->i * 60 + (int)$time->s) / 3600;
        if ($timeFrac == 0)
            return null;
        return $length / $timeFrac;
    }

    static public function climb(DateInterval $time, int $height): ?float
    {
        $timeFrac = ((int)$time->h * 3600 + (int)$time->i * 60 + (int)$time->s) / 3600;
        if ($timeFrac == 0)
            return null;
        return $height / $timeFrac;
    }

    static public function speedSec(int $time, float $length): ?float
    {
        $timeFrac = (float)($time / 3600);
        if ($timeFrac == 0)
            return null;
        return $length / $timeFrac;
    }

    static public function climbSec(int $time, int $height): ?float
    {
        $timeFrac = (float)($time / 3600);
        if ($timeFrac == 0)
            return null;
        return $height / $timeFrac;
    }

    static public function isTimeEmpty(?DateTime $datetime): bool
    {
        return is_null($datetime) || ($datetime->format('H:i:s') == '00:00:00');
    }

    static public function addTimes(DateTime $time1, DateTime $time2): DateTime
    {
        $add = new DateInterval($time2->format('\P\TH\Hi\Ms\S'));
        $time = clone $time1;
        $time->add($add);
        return $time;
    }

    static public function subtractTimes(DateTime $time1, DateTime $time2): DateTime
    {
        $sub = new DateInterval($time2->format('\P\TH\Hi\Ms\S'));
        $time = clone $time1;
        $time->sub($sub);
        return $time;
    }

    static public function averageTime(DateTime $time1, DateTime $time2): DateTime
    {
        $time1U = $time1->format('U');
        $time2U = $time2->format('U');
        $timeU = intval(abs(((int)$time1U - (int)$time2U) / 2));
        if ($time1U < $time2U) {
            $time = clone $time1;
        } else {
            $time = clone $time2;
        }
        $add = new DateInterval('PT' . $timeU . 'S');
        $time->add($add);
        return $time;
    }

    static public function timeSeconds(?DateInterval $time): ?int
    {
        if (!is_null($time)) {
            if (get_class($time) == 'DateInterval') {
                $formatted = $time->format('%H:%I:%S');
            } else {
                $formatted = $time->format('H:i:s');
            }
            list($hours, $minutes, $seconds) = explode(':', $formatted);
            return 3600 * (int)$hours + 60 * (int)$minutes + (int)$seconds;
        } else {
            return null;
        }
    }

    static public function secondsTime(int $seconds): DateInterval
    {
        $s = $seconds % 60;
        $h = floor(($seconds - $s) / 3600);
        $m = floor(($seconds - $h * 3600) / 60);

        $date = new DateInterval("PT{$h}H{$m}M{$s}S");
        return $date;
    }

    static public function timeFraction(DateTime $timeBest, DateTime $time): ?float
    {
        $secsBest = self::timeSeconds($timeBest);
        $secs = self::timeSeconds($time);
        if ($secs <> 0) {
            return ($secsBest / $secs);
        } else {
            return null;
        }
    }

    static public function birthday(int $year): DateTime
    {
        return new DateTime($year . '-01-01');
    }

    static public function normalizeName(string $name): string
    {
        $newName = Strings::trim(Strings::normalize($name));
        $parts = explode(' ', $newName);
        $newName = '';
        foreach ($parts as $part) {
            if ($part <> '') {
                $newName .= Strings::firstUpper(Strings::trim($part)) . ' ';
            }
        }
        $newName = Strings::trim($newName);
        return $newName;
    }

}