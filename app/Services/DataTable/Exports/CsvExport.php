<?php

declare(strict_types=1);

namespace App\Services\DataTable\Exports;

class CsvExport extends Export
{
    public function __construct(array $headers, array $rows)
    {
        parent::__construct($headers, $rows);
    }

    /**
     * @return string
     */
    protected function getExtension(): string
    {
        return 'csv';
    }

    public function getStream(): string
    {

        $stream = fopen('php://temp', 'r+');

        if (! empty($this->headers)) {
            fputcsv($stream, $this->headers);
        }

        foreach ($this->rows as $row) {
            fputcsv($stream, $row);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv ?: '';
    }
}
