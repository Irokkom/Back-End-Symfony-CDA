<?php

namespace App\Dto;

class ModerateCommentRequest
{
    public function __construct(
        public readonly string $status
    ) {}
}
