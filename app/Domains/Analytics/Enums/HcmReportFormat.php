<?php

namespace App\Domains\Analytics\Enums;

enum HcmReportFormat: string
{
    case JSON = 'json';
    case CSV = 'csv';
    case PDF = 'pdf';
    case XLSX = 'xlsx';
}
