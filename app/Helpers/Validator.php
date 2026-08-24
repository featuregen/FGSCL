<?php
/**
 * Input Validator
 * Validates and sanitizes user input
 */

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Create new validator instance
     */
    public static function make(array $data): self
    {
        return new self($data);
    }

    /**
     * Validate required field
     */
    public function required(string $field, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (!isset($this->data[$field]) || trim((string)$this->data[$field]) === '') {
            $this->errors[$field][] = "{$label} is required.";
        }
        return $this;
    }

    /**
     * Validate email
     */
    public function email(string $field, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field][] = "{$label} must be a valid email address.";
            }
        }
        return $this;
    }

    /**
     * Validate minimum length
     */
    public function minLength(string $field, int $min, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (isset($this->data[$field]) && strlen((string)$this->data[$field]) < $min) {
            $this->errors[$field][] = "{$label} must be at least {$min} characters.";
        }
        return $this;
    }

    /**
     * Validate maximum length
     */
    public function maxLength(string $field, int $max, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (isset($this->data[$field]) && strlen((string)$this->data[$field]) > $max) {
            $this->errors[$field][] = "{$label} must not exceed {$max} characters.";
        }
        return $this;
    }

    /**
     * Validate numeric
     */
    public function numeric(string $field, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (isset($this->data[$field]) && !empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = "{$label} must be a number.";
        }
        return $this;
    }

    /**
     * Validate field matches another field
     */
    public function matches(string $field, string $matchField, string $label = '', string $matchLabel = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $matchLabel = $matchLabel ?: ucfirst(str_replace('_', ' ', $matchField));
        if (isset($this->data[$field], $this->data[$matchField]) && $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field][] = "{$label} must match {$matchLabel}.";
        }
        return $this;
    }

    /**
     * Validate phone number
     */
    public function phone(string $field, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $cleaned = preg_replace('/[^0-9+]/', '', $this->data[$field]);
            if (strlen($cleaned) < 10 || strlen($cleaned) > 15) {
                $this->errors[$field][] = "{$label} must be a valid phone number.";
            }
        }
        return $this;
    }

    /**
     * Validate date format
     */
    public function date(string $field, string $format = 'Y-m-d', string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $d = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$d || $d->format($format) !== $this->data[$field]) {
                $this->errors[$field][] = "{$label} must be a valid date ({$format}).";
            }
        }
        return $this;
    }

    /**
     * Validate value is in allowed list
     */
    public function in(string $field, array $allowed, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        if (isset($this->data[$field]) && !empty($this->data[$field]) && !in_array($this->data[$field], $allowed)) {
            $this->errors[$field][] = "{$label} contains an invalid value.";
        }
        return $this;
    }

    /**
     * Validate unique in database table
     */
    public function unique(string $field, string $table, string $column = '', int $excludeId = 0, string $label = ''): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $column = $column ?: $field;
        
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $where = "{$column} = ?";
            $params = [$this->data[$field]];
            
            if ($excludeId > 0) {
                $where .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            if (Database::exists($table, $where, $params)) {
                $this->errors[$field][] = "{$label} already exists.";
            }
        }
        return $this;
    }

    /**
     * Custom validation rule
     */
    public function custom(string $field, callable $callback, string $message): self
    {
        if (isset($this->data[$field]) && !$callback($this->data[$field])) {
            $this->errors[$field][] = $message;
        }
        return $this;
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get all errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error for a field
     */
    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Get all errors as flat array
     */
    public function allErrors(): array
    {
        $flat = [];
        foreach ($this->errors as $fieldErrors) {
            $flat = array_merge($flat, $fieldErrors);
        }
        return $flat;
    }

    /**
     * Sanitize string input
     */
    public static function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize array of inputs
     */
    public static function sanitizeAll(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $clean[$key] = self::sanitize($value);
            } elseif (is_array($value)) {
                $clean[$key] = self::sanitizeAll($value);
            } else {
                $clean[$key] = $value;
            }
        }
        return $clean;
    }

    /**
     * Get sanitized input value
     */
    public static function input(string $key, mixed $default = ''): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        if (is_string($value)) {
            return self::sanitize($value);
        }
        return $value;
    }

    /**
     * Get all POST data sanitized
     */
    public static function postData(): array
    {
        return self::sanitizeAll($_POST);
    }
}
