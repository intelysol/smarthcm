<?php

namespace App\Domains\Expenses\Enums;

enum AccountingExportStatus: string
{
    case DRAFT = 'draft';
    case EXPORTED = 'exported';
    case POSTED = 'posted';
}
