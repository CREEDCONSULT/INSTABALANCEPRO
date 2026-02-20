<?php

namespace App\Controllers\API;

use App\Controller;

/**
 * SearchController — AJAX search endpoints
 */
class SearchController extends Controller
{
    public function accounts()
    {
        $query = $this->get('q', '');
        $this->json([
            'results' => [],
            'query' => $query,
        ]);
    }
}

/**
 * ActivityController — Activity feed AJAX
 */
class ActivityController extends Controller
{
    public function feed()
    {
        $this->partial('partials/activity-feed', [
            'events' => [],
        ]);
    }
}

/**
 * ExportController — Data export endpoints
 */
class ExportController extends Controller
{
    public function csv()
    {
        if ($this->isPost()) {
            // TODO: Generate CSV, send as download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="export.csv"');
            echo "username,followers,following\n";
        }
    }

    public function json()
    {
        if ($this->isPost()) {
            // TODO: Generate JSON export
            $this->json(['exported_records' => 0]);
        }
    }
}
