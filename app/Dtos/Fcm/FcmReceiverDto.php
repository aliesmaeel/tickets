<?php

namespace App\Dtos\Fcm;

use App\Enums\UserType;

class FcmReceiverDto
{
    public function __construct(
        public int $id,
        public string $type,
    ) {}

    public static function make(int $id, string $type): self
    {
        if (! UserType::exists($type)) {
            throw new \InvalidArgumentException('Invalid type');
        }

        return new self($id, $type);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
        ];
    }
}
