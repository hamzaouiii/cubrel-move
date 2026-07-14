<?php

namespace App\Services\Import;

class CsvDelimiterDetector
{
    // counts how many "," and ";" are in the first line and based on that decides what is the Delimiter
    public static function detect(string $firstLine): string
    {
        $commaFields = count(str_getcsv($firstLine, ',', '"', '\\'));
        $semicolonFields = count(str_getcsv($firstLine, ';', '"', '\\'));

        return $semicolonFields > $commaFields ? ';' : ',';
    }
}
