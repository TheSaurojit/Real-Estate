<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Generate a streaming CSV download with UTF-8 BOM for Microsoft Excel compatibility
     *
     * @param string $filename
     * @param array $headers
     * @param iterable $rows
     * @return StreamedResponse
     */
    public function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel to open symbols (like ₹ and accented letters) correctly
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write column headers
            fputcsv($handle, $headers);

            // Write data rows
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");
        $response->headers->set('Cache-Control', 'no-store, no-cache');

        return $response;
    }
}
