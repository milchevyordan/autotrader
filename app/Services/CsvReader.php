<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

class CsvReader extends Service
{
    protected string $filePath;

    protected array $data = [];

    protected array $headers = [];

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function read(array $columnNames, string $delimiter): void
    {
        if (($handle = fopen($this->filePath, 'r')) !== false) {
            // Read headers
            if (($headers = fgetcsv($handle, 0, $delimiter)) !== false) {
                // Replace headers with provided column names if given
                if (! empty($columnNames)) {
                    $this->headers = $columnNames;
                } else {
                    $this->headers = $headers;
                }

                // Read data
                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                    // Ensure row matches the column count
                    $headersNumber = count($this->headers);
                    $columnsNumber = count($row);

                    if ($headersNumber !== $columnsNumber) {
                        throw new InvalidArgumentException("Headers number doesnt match the columns number. The number of passed headers is:  [{$headersNumber}] and the passed columns number is [{$columnsNumber}] ");
                    }

                    $this->data[] = array_combine($this->headers, $row);
                }

                fclose($handle);
            }
        }
    }

    public function getData(): array
    {
        return $this->data;
    }
}
