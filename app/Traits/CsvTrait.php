<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;

trait CsvTrait
{
    /**
     * Read CSV file and return as an array.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param bool $withHeader Whether first row is header
     * @return array
     */
    public function readCsv(UploadedFile $file, bool $withHeader = false): array
    {
        $data = [];
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = null;
            $isFirstRow = true;

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if ($withHeader && $isFirstRow) {
                    $header = $row;
                    $isFirstRow = false;
                    continue;
                }

                if ($withHeader && $header) {
                    $data[] = array_combine($header, $row);
                } else {
                    $data[] = $row;
                }

                $isFirstRow = false;
            }
            fclose($handle);
        }
        return $data;
    }
}
