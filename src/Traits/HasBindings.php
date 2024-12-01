<?php

declare(strict_types=1);

namespace Patoui\LaravelClickhouse\Traits;

trait HasBindings
{
    public ?string $binding_key = null;
    public ?array $binding_keys = null;

    public function resetBindingKeys(): void
    {
        $this->binding_key = null;
        $this->binding_keys = null;
    }

    protected function nextBindingKey(mixed $value): string
    {
        $hash = md5(json_encode($value));

        if (! isset($this->binding_keys)) {
            $this->binding_keys = [];
        }

        if (isset($this->binding_keys[$hash])) {
            return $this->binding_keys[$hash];
        }

        $this->binding_keys[$hash] = $this->nextKey();

        return $this->binding_keys[$hash];
    }

    protected function nextKey(): string
    {
        if ($this->binding_key === null) {
            $this->binding_key = 'a';
        } else {
            $this->binding_key++;
        }

        return $this->binding_key;
    }

    protected function flattenWithKeys(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenWithKeys($value));
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
