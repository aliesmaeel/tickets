<?php

namespace App\Traits;

trait HasLocalizedAttributes
{
    protected $forcedLocale = null;

    public function setLocale(?string $lang): static
    {
        $this->forcedLocale = $lang;
        return $this;
    }

    protected function getEffectiveLocale(): string
    {
        return $this->forcedLocale ?? app()->getLocale();
    }

    public function getLocalizedValue(string $attribute): ?string
    {
        $locale = $this->getEffectiveLocale();
        $value = json_decode($this->attributes[$attribute] ?? '{}', true);
        return $value[$locale] ?? $value['en'] ?? null;
    }

    public function getNameLocalizedAttribute(): ?string
    {
        return $this->getLocalizedValue('name');
    }

    public function getDescriptionLocalizedAttribute(): ?string
    {
        return $this->getLocalizedValue('description');
    }

    public function getTitleLocalizedAttribute(): ?string
    {
        return $this->getLocalizedValue('title');
    }

    public function getAddressLocalizedAttribute(): ?string
    {
        return $this->getLocalizedValue('address');
    }
}
