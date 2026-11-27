<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Validasi input data terpusat dengan aturan standar.
 * Mendukung: required, int, min, max, between, in, regex, email, array, date, boolean, password_strong.
 */
final class Validator
{
    private array $data;
    private array $rules;
    private array $customMessages;
    private array $errors = [];
    private bool $hasValidated = false;

    public function __construct(array $data, array $rules, array $customMessages = [])
    {
        $this->data           = $data;
        $this->rules          = $rules;
        $this->customMessages = $customMessages;
    }

    public static function make(array $data, array $rules, array $customMessages = []): self
    {
        $instance = new self($data, $rules, $customMessages);
        $instance->validate();
        return $instance;
    }

    public function validate(): bool
    {
        if ($this->hasValidated) {
            return empty($this->errors);
        }

        $this->hasValidated = true;
        $this->errors       = [];

        foreach ($this->rules as $field => $rules) {
            $ruleList    = is_array($rules) ? $rules : explode('|', (string) $rules);
            $value       = $this->data[$field] ?? null;
            $hasRequired = in_array('required', $ruleList, true);
            $isEmpty     = ($value === null || $value === '' || (is_array($value) && empty($value)));

            if ($isEmpty) {
                if ($hasRequired) {
                    $this->addError($field, 'required', "{$field} wajib diisi.");
                }
                continue;
            }

            foreach ($ruleList as $rule) {
                if ($rule === 'required' || $rule === '') {
                    continue;
                }
                $this->applyRule($field, $value, (string) $rule, $ruleList);
            }
        }

        return empty($this->errors);
    }

    public function fails(): bool
    {
        $this->validate();
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return !$this->fails();
    }

    public function errors(): array
    {
        $this->validate();
        return $this->errors;
    }

    public function firstErrors(): array
    {
        $this->validate();
        $result = [];
        foreach ($this->errors as $field => $messages) {
            $result[$field] = $messages[0] ?? '';
        }
        return $result;
    }

    public function first(?string $field = null): ?string
    {
        $this->validate();
        if ($field !== null) {
            return $this->errors[$field][0] ?? null;
        }
        foreach ($this->errors as $messages) {
            if (!empty($messages)) {
                return $messages[0];
            }
        }
        return null;
    }

    public function validated(): array
    {
        $result = [];
        foreach (array_keys($this->rules) as $field) {
            if (array_key_exists($field, $this->data)) {
                $result[$field] = $this->data[$field];
            }
        }
        return $result;
    }

    /**
     * Validasi kriteria password kuat secara terpusat (dipakai oleh Validator & UserService).
     *
     * @return string[]
     */
    public static function checkStrongPassword(string $password): array
    {
        $errors = [];
        if (strlen($password) < 8) {
            $errors[] = 'Password minimal 8 karakter.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password harus mengandung minimal 1 huruf besar.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password harus mengandung minimal 1 angka.';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Password harus mengandung minimal 1 karakter khusus.';
        }
        return $errors;
    }

    private function applyRule(string $field, mixed $value, string $rule, array $allRules): void
    {
        $params = '';
        if (str_contains($rule, ':')) {
            [$ruleName, $params] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
        }

        $isNumericContext = in_array('int', $allRules, true) || is_int($value);

        switch ($ruleName) {
            case 'int':
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, 'int', "{$field} harus berupa bilangan bulat.");
                }
                break;

            case 'array':
                if (!is_array($value)) {
                    $this->addError($field, 'array', "{$field} harus berupa daftar data.");
                }
                break;

            case 'boolean':
                if (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) {
                    $this->addError($field, 'boolean', "{$field} harus berupa nilai benar/salah.");
                }
                break;

            case 'date':
                $str = (string) $value;
                if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $str, $m) || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
                    $this->addError($field, 'date', "Format tanggal {$field} tidak valid (YYYY-MM-DD).");
                }
                break;

            case 'password_strong':
                $pwErrors = self::checkStrongPassword((string) $value);
                if (!empty($pwErrors)) {
                    $this->addError($field, 'password_strong', implode(' ', $pwErrors));
                }
                break;

            case 'min':
                $min = (int) $params;
                if ($isNumericContext && is_numeric($value)) {
                    if ((float) $value < $min) {
                        $this->addError($field, 'min', "{$field} minimal bernilai {$min}.");
                    }
                } elseif (is_array($value)) {
                    if (count($value) < $min) {
                        $this->addError($field, 'min', "{$field} minimal berisi {$min} item.");
                    }
                } else {
                    if (mb_strlen((string) $value) < $min) {
                        $this->addError($field, 'min', "{$field} minimal {$min} karakter.");
                    }
                }
                break;

            case 'max':
                $max = (int) $params;
                if ($isNumericContext && is_numeric($value)) {
                    if ((float) $value > $max) {
                        $this->addError($field, 'max', "{$field} maksimal bernilai {$max}.");
                    }
                } elseif (is_array($value)) {
                    if (count($value) > $max) {
                        $this->addError($field, 'max', "{$field} maksimal berisi {$max} item.");
                    }
                } else {
                    if (mb_strlen((string) $value) > $max) {
                        $this->addError($field, 'max', "{$field} maksimal {$max} karakter.");
                    }
                }
                break;

            case 'between':
                $parts = explode(',', $params);
                $min   = isset($parts[0]) ? (float) $parts[0] : 0;
                $max   = isset($parts[1]) ? (float) $parts[1] : 0;
                if ($isNumericContext && is_numeric($value)) {
                    $valNum = (float) $value;
                    if ($valNum < $min || $valNum > $max) {
                        $this->addError($field, 'between', "{$field} harus bernilai antara {$min} dan {$max}.");
                    }
                } else {
                    $len = mb_strlen((string) $value);
                    if ($len < $min || $len > $max) {
                        $this->addError($field, 'between', "{$field} harus terdiri dari {$min} sampai {$max} karakter.");
                    }
                }
                break;

            case 'in':
                $allowed = explode(',', $params);
                if (!in_array((string) $value, $allowed, true)) {
                    $this->addError($field, 'in', "Pilihan {$field} tidak valid.");
                }
                break;

            case 'regex':
                $pattern = $params;
                if (!preg_match('/^(\/|#|~).+(\/|#|~)[a-z]*$/i', $pattern)) {
                    $pattern = '/' . str_replace('/', '\/', $pattern) . '/';
                }
                if (@preg_match($pattern, (string) $value) !== 1) {
                    $this->addError($field, 'regex', "Format {$field} tidak valid.");
                }
                break;

            case 'email':
                if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    $this->addError($field, 'email', "Format email pada {$field} tidak valid.");
                }
                break;
        }
    }

    private function addError(string $field, string $rule, string $defaultMessage): void
    {
        $customKey = "{$field}.{$rule}";
        $message   = $this->customMessages[$customKey]
            ?? $this->customMessages[$field]
            ?? $defaultMessage;

        $this->errors[$field][] = $message;
    }
}
