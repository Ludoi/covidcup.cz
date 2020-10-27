<?php
declare(strict_types=1);
/*
   Copyright (C) 2020 Luděk Bednarz

   Project: covidcup.cz
   Author:  Luděk Bednarz
*/


namespace App;


use DateTimeZone;
use DOMDocument;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

class WeatherLysa implements iWeather
{
    private int $pointid;

    public function setPointid(int $pointid): void
    {
        $this->pointid = $pointid;
    }

    public function getPointid(): int
    {
        return $this->pointid;
    }

    public function getWeather(Weather $weather): void
    {
        $html = $this->readURL('http://www.lysahora.cz/pocasi.phtml');
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html, LIBXML_NOERROR);

        $now = new DateTime();
        $last = $weather->findBy([ 'pointid' => $this->pointid ])->aggregation('MAX(measure_time)');

        $snow = 0;
        $divs = $doc->getElementsByTagName('div');
        foreach ($divs as $div) {
            if ($div->getAttribute('id') == 'pocasi') {
                foreach ($div->getElementsByTagName('p') as $p) {
                    if (Strings::contains($p->nodeValue, 'celková sněhová pokrývka')) {
                        list($snow) = Strings::match($p->nodeValue, '/: ([0-9]+)\w/');
                        $snow = (float) str_replace(':', '', $snow);
                    }
                }
            }
        }

        $tables = $doc->getElementsByTagName('table');

        $weather->getDatabase()->beginTransaction();

        foreach ($tables as $table) {
            if ($table->getAttribute('class') == 'tabTyp3') {
                foreach ($table->getElementsByTagName('tr') as $tr) {
                    $index = 0;
                    $datetime = '';
                    $temperature = 0;
                    $humidity = 0;
                    $pressure = 0;
                    $wind = 0;
                    $visibility = '';
                    $remark = '';
                    $found = false;
                    foreach ($tr->getElementsByTagName('td') as $td) {
                        $index++;
                        $found = true;
                        switch ($index) {
                            case 1:
                                $datestr = str_replace('v ', ', ', $td->nodeValue);
                                $datetime = new DateTime($datestr, new DateTimeZone('Europe/Prague'));
                                break;
                            case 2:
                                $temperature = (float) str_replace(',', '.', $td->nodeValue);
                                break;
                            case 3:
                                $humidity = (float) str_replace(',', '.', $td->nodeValue);
                                break;
                            case 4:
                                break;
                            case 5:
                                $wind = ((float) str_replace(',', '.', $td->nodeValue) + 0) * 3.6;
                                break;
                            case 6:
                                break;
                            case 7:
                                $pressure = (float) str_replace(',', '.', $td->nodeValue);
                                break;
                            case 8:
                                $visibility = Strings::trim($td->nodeValue);
                                break;
                            case 9:
                                $remark = Strings::trim($td->nodeValue);
                                if ($remark == '&nbsp') {
                                    $remark = '';
                                }
                                break;
                        }
                    }
                    if ($found && ((int)$now->format('U') > (int)$datetime->format('U'))) {
                        if (is_null($last) || ((int)$last->format('U') < (int)$datetime->format('U'))) {
                            $data = [
                                'pointid' => $this->pointid,
                                'measure_date' => $datetime,
                                'measure_time' => $datetime,
                                'temperature' => $temperature,
                                'humidity' => $humidity,
                                'wind' => $wind,
                                'pressure' => $pressure,
                                'visibility' => $visibility,
                                'remark' => $remark,
                                'snow' => $snow
                            ];
                            $weather->insert($data);
                        }
                    }
                }
            }
        }
        $weather->getDatabase()->commit();
    }

    private function readURL(string $url): string {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}