<?php

declare(strict_types=1);

namespace App\Helpers;

class Message
{
    private string $type;

    private string $title;

    private ?string $description;

    public function __construct(string $type, string $title, ?string $description = null)
    {
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
    }

    public static function success(string $title, ?string $description = null): self
    {
        return new self('success', $title, $description);
    }

    public static function error(string $title, ?string $description = null): self
    {
        return new self('error', $title, $description);
    }

    public static function warning(string $title, ?string $description = null): self
    {
        return new self('warning', $title, $description);
    }

    public static function info(string $title, ?string $description = null): self
    {
        return new self('info', $title, $description);
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
