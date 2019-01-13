<?php

namespace Stiction\SafeParams;

class SafeParamsParser
{
    const DELIMITER = '.';

    const TYPE_INT    = 'int';
    const TYPE_FLOAT  = 'float';
    const TYPE_BOOL   = 'bool';
    const TYPE_STRING = 'string';
    const TYPE_ARRAY  = 'array';
    const TYPE_UINT64 = 'uint64';

    const UINT64_MIN = '0';
    const UINT64_MAX = '18446744073709551615';

    const STRING_TRIM = 'trim';

    public function parse($data, $specs)
    {
        if (is_string($specs)) {
            return $this->parseSingle($data, $specs);
        }
        if (!is_array($specs)) {
            throw new \InvalidArgumentException('invalid specs');
        }
        if (!is_array($data)) {
            $data = [];
        }
        $result = [];
        foreach ($specs as $key => $spec) {
            if (!is_string($spec)) {
                throw new \InvalidArgumentException('invalid specs');
            }
            $result[$key] = $this->parseSingle($data[$key] ?? null, $spec);
        }
        return $result;
    }

    protected function parseSingle($data, string $spec)
    {
        $parts = explode(self::DELIMITER, $spec);
        if (!is_array($parts) || count($parts) < 1) {
            throw new \InvalidArgumentException("invalid spec $spec");
        }
        $type = array_shift($parts);
        if (!$this->isTypeValid($type)) {
            throw new \InvalidArgumentException("invalid type $type");
        }
        $meta = $parts;
        $method = 'parse'.ucfirst($type);
        return $this->$method($data, $meta);
    }

    protected function isTypeValid(string $type): bool
    {
        try {
            $defaultValue = $this->defaultValueForType($type);
            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    protected function defaultValueForType(string $type)
    {
        $defaultValues = [
            self::TYPE_INT => 0,
            self::TYPE_FLOAT => 0.0,
            self::TYPE_BOOL => false,
            self::TYPE_STRING => '',
            self::TYPE_ARRAY => [],
            self::TYPE_UINT64 => '0',
        ];
        if (isset($defaultValues[$type])) {
            return $defaultValues[$type];
        }
        throw new \InvalidArgumentException("invalid type $type");
    }

    protected function parseInt($data, array $meta): int
    {
        $this->noneMeta($meta);
        if (is_int($data)) {
            return $data;
        }
        if (is_float($data) || is_bool($data) || is_string($data)) {
            return (int)$data;
        }
        return $this->defaultValueForType(self::TYPE_INT);
    }

    protected function parseFloat($data, array $meta): float
    {
        $this->noneMeta($meta);
        if (is_float($data)) {
            return $data;
        }
        if (is_int($data) || is_bool($data) || is_string($data)) {
            return (float)$data;
        }
        return $this->defaultValueForType(self::TYPE_FLOAT);
    }

    protected function parseBool($data, array $meta): bool
    {
        $this->noneMeta($meta);
        if (is_bool($data)) {
            return $data;
        }
        return (bool)$data;
    }

    protected function parseString($data, array $meta): string
    {
        if (is_string($data)) {
            $str = $data;
        } elseif (is_array($data)) {
            $str = 'Array';
        } elseif (is_object($data) && !method_exists($data, '__toString')) {
            $str = $this->defaultValueForType(self::TYPE_STRING);
        } else {
            $str = (string)$data;
        }
        foreach ($meta as $item) {
            if ($item === self::STRING_TRIM) {
                $str = trim($str);
            } else {
                throw new \InvalidArgumentException('invalid spec meta');
            }
        }
        return $str;
    }

    protected function parseArray($data, array $meta): array
    {
        if (is_array($data)) {
            $arr = $data;
        } else {
            $arr = (array)$data;
        }
        if (count($meta) === 0) {
            return $arr;
        }
        $subType = $meta[0];
        if ($subType === self::TYPE_ARRAY) {
            throw new \InvalidArgumentException('do not support array.array spec');
        }
        $spec = implode(self::DELIMITER, $meta);
        foreach ($arr as $key => $value) {
            $arr[$key] = $this->parseSingle($value, $spec);
        }
        return $arr;
    }

    protected function parseUint64($data, array $meta): string
    {
        $this->noneMeta($meta);
        if (is_float($data)) {
            $str = sprintf('%f', $data);
        } else {
            $str = $this->parseSingle($data, self::TYPE_STRING.'.'.self::STRING_TRIM);
            $str = ltrim($str, '0');
        }
        $big = bcadd($str, '0', 0);
        if (bccomp(self::UINT64_MIN, $big, 0) > 0) {
            return self::UINT64_MIN;
        }
        if (bccomp(self::UINT64_MAX, $big, 0) < 0) {
            return self::UINT64_MAX;
        }
        return $big;
    }

    protected function noneMeta(array $meta)
    {
        if (count($meta) !== 0) {
            throw new \InvalidArgumentException('meta is not empty');
        }
    }
}
