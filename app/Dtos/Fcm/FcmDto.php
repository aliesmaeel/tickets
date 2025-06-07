<?php

namespace App\Dtos\Fcm;

class FcmDto
{

    public function __construct(
        public array|FcmReceiverDto $receivers,
        public string $title,
        public string $body,
        public string $subtitle = '',
        public array $data = [],
    ) {}

    public static function make(array|FcmReceiverDto $receivers, string $title, string $body, string $subtitle = '', array $data = []): self
    {
        if (is_array($receivers)) {
            foreach ($receivers as $receiver) {
                if (! $receiver instanceof FcmReceiverDto) {
                    throw new \InvalidArgumentException('Invalid receiver');
                }
            }
        } else {
            $receivers = [$receivers];
        }

        if (empty($title)) {
            throw new \InvalidArgumentException('Title is required');
        }

        if (empty($body)) {
            throw new \InvalidArgumentException('Body is required');
        }

        return new self($receivers, $title, $body, $subtitle, $data);
    }

    public function toArray(): array
    {
        return [
            'receivers' => array_map(fn ($receiver) => $receiver->toArray(), $this->receivers),
            'title' => $this->title,
            'body' => $this->body,
            'subtitle' => $this->subtitle,
            'data' => $this->data,
        ];
    }
}

