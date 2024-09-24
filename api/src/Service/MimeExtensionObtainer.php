<?php

namespace App\Service;

use Mimey\MimeTypes;

class MimeExtensionObtainer
{
    protected const CUSTOM_MAPPING = [
        'application/jsonl' => 'jsonl',
    ];

    protected MimeTypes $mimeTypes;

    public function __construct() {
        $this->mimeTypes = new MimeTypes();
    }

    public function getExtension(string $mimeType): string {
        if (array_key_exists($mimeType, self::CUSTOM_MAPPING)) {
            return self::CUSTOM_MAPPING[$mimeType];
        }

        return $this->mimeTypes->getExtension($mimeType);
    }
}
