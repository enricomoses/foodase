<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Validasi input data dengan aturan standar.
 * Contoh pemakaian:
 * $v = Validator::make($data, ['name' => 'required|min:2|max:50', 'email' => 'email']);
 * if ($v->fails()) { ... $v->errors(); }
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
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $customMessages;
    }

    /**
     * Buat instance Validator dan jalankan validasi.
     */
    public static function make(array $data, array $rules, array $customMessages = []): self
    {
        $instance = new self($data, $rules, $customMessages);
        $instance->validate();
        return $instance;
    }

    /**
     * Jalankan proses validasi untuk setiap field dan aturan.
     */
    public function validate(): bool
    {
        if ($this->hasValidated) {
            return empty($this->errors);
        }

        $this->hasValidated = true;
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $ruleList = is_array($rules) ? $rules : explode('|', (string) $rules);
            $value = $this->data[$field] ?? null;

            $hasRequired = in_array('required', $ruleList, true);
            $isEmpty = ($value === null || $value === '' || (is_array($value) && empty($value)));

            if ($isEmpty) {
                if ($hasRequired) {
                    $this->addError($field, 'required', "{$field} wajib diisi.");
                }
                // Jika kosong dan tidak required, lewati aturan lainnya
                continue;
            }

            foreach ($ruleList as $rule) {
                if ($rule === 'required') {
                    continue;
                }

                $this->applyRule($field, $value, $rule, $ruleList);
            }
        }

        return empty($this->errors);
    }

    /**
     * Cek apakah validasi gagal.
     */
    public function fails(): bool
    {
        $this->validate();
        return !empty($this->errors);
    }

    /**
     * Cek apakah validasi berhasil.
     */
    public function passes(): bool
    {
        return !$this->fails();
    }

    /**
     * Ambil seluruh pesan error yang dikelompokkan per field.
     * Format: ['field' => ['Pesan error 1', ...]]
     */
    public function errors(): array
    {
        $this->validate();
        return $this->errors;
    }

    /**
     * Ambil pesan error pertama untuk setiap field.
     * Format: ['field' => 'Pesan error pertama']
     */
    public function firstErrors(): array
    {
        $this->validate();
        $result = [];
        foreach ($this->errors as $field => $messages) {
            $result[$field] = $messages[0] ?? '';
        }
        return $result;
    }

    /**
     * Ambil pesan error pertama untuk field tertentu atau error pertama secara global.
     */
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

    /**
     * Ambil data yang berhasil divalidasi sesuai key pada rules.
     */
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
     * Terapkan aturan tertentu pada field.
     */
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

            case 'min':
                $min = (int) $params;
                if ($isNumericContext && is_numeric($value)) {
                    if ((float) $value < $min) {
                        $this->addError($field, 'min', "{$field} minimal bernilai {$min}.");
                    }
                } else {
                    $len = mb_strlen((string) $value);
                    if ($len < $min) {
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
                } else {
                    $len = mb_strlen((string) $value);
                    if ($len > $max) {
                        $this->addError($field, 'max', "{$field} maksimal {$max} karakter.");
                    }
                }
                break;

            case 'between':
                $parts = explode(',', $params);
                $min = isset($parts[0]) ? (float) $parts[0] : 0;
                $max = isset($parts[1]) ? (float) $parts[1] : 0;

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

    /**
     * Tambahkan pesan error ke koleksi error.
     */
    private function addError(string $field, string $rule, string $defaultMessage): void
    {
        $customKey = "{$field}.{$rule}";
        $message = $this->customMessages[$customKey]
            ?? $this->customMessages[$field]
            ?? $defaultMessage;

        $this->errors[$field][] = $message;
    }
}
