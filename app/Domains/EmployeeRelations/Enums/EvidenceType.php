<?php

namespace App\Domains\EmployeeRelations\Enums;

enum EvidenceType: string
{
    case DOCUMENT = 'document';
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case EMAIL = 'email';
    case MESSAGE = 'message';
    case SYSTEM_RECORD = 'system_record';
    case STATEMENT = 'statement';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::DOCUMENT => 'Document',
            self::IMAGE => 'Image / Photo',
            self::VIDEO => 'Video Recording',
            self::AUDIO => 'Audio Recording',
            self::EMAIL => 'Email Correspondence',
            self::MESSAGE => 'Chat / Instant Message',
            self::SYSTEM_RECORD => 'System Log / Record',
            self::STATEMENT => 'Written Statement',
            self::OTHER => 'Other Evidence',
        };
    }
}
