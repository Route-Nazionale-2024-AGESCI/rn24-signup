<?php

require_once 'validations.php';

function plugin_dir_path($file) {
    return dirname($file);
}

function tprint($func_name, $message) {
    echo sprintf("%s: %s\n", $func_name, $message);
}

function testit($func_name, $result) {
    try {
        $func_result = $func_name();
    }
    catch (Exception $e) {
        tprint($func_name, 'FALLITO');
        tprint($func_name, 'ECCEZZIONE');
        tprint($func_name, $e->getMessage());
    }

    if($func_result != $result) {
        tprint($func_name, 'FALLITO');
        tprint($func_name, sprintf('%s != %s', $func_result, $result));
        return;
    }

    tprint($func_name, 'PASSATO');
}


function validator_required() {
    $data = ['email' => 'asdf@asd.it'];

    $rules = [
        'email' => ['Required'],
        'cavallo' => ['Required'],
    ];

    try {
        validate($rules, $data);
    }
    catch(ValidationsError $e) {
        $errors = $e->getErrors();
        
        $exception = new MissingRequiredParam('cavallo');
        if($errors['cavallo'] != $exception->getValidationMessage())
            return 'Errore eccezzione';
    }
}


function validator_exist_in_groups() {
    $key = 'Ordinale';
    $value = 'H0470';
    $data = ['group' => $value];

    $rules = [
        'group' => ['ExistInGroups:'.$key],
    ];

    try {
        validate($rules, $data);
        $value .= '1';
        $data['group'] = $value;
        validate($rules, $data);
    }
    catch(ValidationsError $e) {
        $errors = $e->getErrors();

        $exception = new NotInGroups($key, $value);

        if($errors['group'] != $exception->getValidationMessage())
            return 'Errore eccezzione';
    }
}

function validator_email_valid() {
    $value = 'H0470@gatto.it';
    $data = ['email' => $value];

    $rules = [
        'email' => ['ValidEmail'],
    ];

    try {
        validate($rules, $data);
        $value = '1';
        $data['group'] = $value;
        validate($rules, $data);
    }
    catch(ValidationsError $e) {
        $errors = $e->getErrors();

        $exception = new EmailNotValid($key, $value);

        if($errors['group'] != $exception->getValidationMessage())
            return 'Errore eccezzione';
    }
}


$totest = [
    'validator_required' => null,
    'validator_exist_in_groups' => null,
    'validator_email_valid' => null,
];

foreach($totest as $func_name => $result)
    testit($func_name, $result);
