<?php


class ValidationsError extends Exception {
    private array $errors;

    public function __construct(array $errors) {
        $this->errors = $errors;
        parent::__construct('', 0, null);
    }

    public function getErrors(): array {
        return $this->errors;
    }
}


class ValidationError extends Exception {
    protected string $param;

    public function __construct(string $param) {
        $this->param = $param;
        parent::__construct('', 0, null);
    }

    public function getParam(): string {
        return $this->param;
    }

    public function getValidationMessage(): string {
        throw Exception('not implemented');
    }

}


class MissingRequiredParam extends ValidationError {
    public function getValidationMessage(): string {
        return sprintf("%s è un campo obbligatorio", $this->param);
    }
}

class ValidationErrorWithValue extends ValidationError {
    protected string $value;
    public function __construct(string $param, $value) {
        $this->value = $value;
        parent::__construct($param);
    }
}


class NotInGroups extends ValidationErrorWithValue {
    public function getValidationMessage(): string {
        return sprintf("%s non è un valore valido", $this->value);
    }
}

class EmailExistsError extends ValidationErrorWithValue {
    public function getValidationMessage(): string {
        return sprintf("L'email %s è già registrata", $this->value);
    }
}

class UsernameExistsError extends ValidationErrorWithValue {
    public function getValidationMessage(): string {
        return sprintf("Il gruppo è già registrato", $this->value);
    }
}


class EmailNotValid extends ValidationErrorWithValue {
    public function getValidationMessage(): string {
        return sprintf("L'email %s non è valida", $this->value);
    }
}
