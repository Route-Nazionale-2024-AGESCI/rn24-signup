<?php

require_once 'exceptions.php';
require_once 'utils.php';


class Validator {
    protected string $param;

    public function __construct(string $param) {
        $this->param = $param;
    }

    public function validate($value) {
        throw Exception('not implemented');
    }
}

class Required extends Validator {
    public function validate($value) {
        if(!$value)
            throw new MissingRequiredParam($this->param);
    }
}


class EmailExists extends Validator {
    public function validate($value) {
        if(email_exists($value))
            throw new EmailExistsError($this->param, $value);
    }
}

class ValidEmail extends Validator {
    public function validate($value) {
        if(!filter_var($value, FILTER_VALIDATE_EMAIL))
            throw new EmailNotValid($this->param, $value);
    }
}

class ExistInGroups extends Validator {
    public function __construct(string $param, string $key) {
        $this->param = $param;
        $this->key = $key;

        parent::__construct($param);
    }

    public function validate($value) {
        $groups = rn24_get_groups();
        
        if(!check_if_exists($groups, $this->key, $value))
            throw new NotInGroups($this->param, $value);
    }
}


function validate(array $rules_data, array $data): array {
    $cleaned = [];
    $errors = [];
    // param => [valid1, valid2]

    foreach($rules_data as $param => $rules) {
        try {
            foreach($rules as $rule_string) {
                $validator_data = explode(':', $rule_string);
                $validator_name = $validator_data[0];

                if(!class_exists($validator_name))
                    throw Exception("$validator_name not exists");
                
                if(count($validator_data) > 1)
                    $validator = new $validator_name($param, $validator_data[1]);
                else
                    $validator = new $validator_name($param);

                $value = $data[$param] ?? null;

                $validator->validate($value);
                $cleaned[$param] = $value;
            }
        }
        catch(ValidationError $e) {
            $errors[$e->getParam()] = $e->getValidationMessage();
        }
    }

    if(count($errors) > 0)
        throw new ValidationsError($errors);

    return $cleaned;
}