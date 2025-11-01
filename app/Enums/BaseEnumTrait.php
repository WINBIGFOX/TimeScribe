<?php

declare(strict_types=1);

namespace App\Enums;

trait BaseEnumTrait
{
    public static function tryFrom(mixed $value): ?bool
    {
        return in_array($value, array_column(self::cases(), 'name')) ? true : null;
    }

    public static function toArray($property = 'label', ?array $excludeKeys = null): array
    {
        $collection = collect(self::cases());

        if ($excludeKeys) {
            $collection = $collection->reject(fn ($enum): bool => in_array($enum->name, $excludeKeys));
        }

        return $collection->mapWithKeys(
            fn ($enum): array => [$enum->value ?? $enum->name => $enum->$property()]
        )->toArray();
    }

    public function toKeyValue($property = 'label'): array
    {
        return [
            'key' => $this->value ?? $this->name,
            'value' => $this->$property(),
        ];
    }

    public static function values(): array
    {
        return array_map(fn ($enum) => $enum->value, self::cases());
    }
}
