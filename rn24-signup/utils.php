<?php

require_once 'exceptions.php';

function get_and_remove(string|int $key, array &$array, $default=null) {
    $value = $array[$key] ?? $default;

    unset($array[$key]);

    return $value;
}

function rn24_get_plugin_url(string $file) {
    return plugins_url($file, __FILE__);
}


function check_if_exists(array $array, string|int $searchKey, string|int $searchValue) {
    foreach ($array as $data) {
        if ($data[$searchKey] === $searchValue) {
            return true; // Return the key of the first occurrence
        }
    }
    return false; // Return null if the value is not found
}


function rn24_get_groups() {
    $groups_json = file_get_contents(plugin_dir_path(__FILE__).'/data/groups.json');

    return json_decode($groups_json, true);
}


function get_group_denominazione_from_ordinale(string $ordinale, $default=''): string|null {
    foreach (rn24_get_groups() as $data) {
        if ($data['Ordinale'] === $ordinale) {
            return $data['Denominazione Gruppo'];
        }
    }

    return $default;
}

function get_group_email_from_ordinale(string $ordinale, $default=''): string|null {
    foreach (rn24_get_groups() as $data) {
        if ($data['Ordinale'] === $ordinale) {
            return $data['GruppoEmail'];
        }
    }

    return $default;
}

function rn24_get(string $key, array $array, $default=null) {
    return $array[$key] ?? $default;
}


function create_user_and_send_password_email($email, $group) {
    // Generate a random password
    $password = wp_generate_password();

    $username = $group;
    // Create the new user
    $user_id = wp_create_user($username, $password, $email);

    // Check if user creation was successful
    if (is_wp_error($user_id)) {
        throw new Exception('Utente non creato');
    }

    $group_name = get_group_denominazione_from_ordinale($group, $group);

    wp_update_user([
        'ID' => $user_id, 
        'first_name' => $group_name,
        'user_nicename' => $group_name
    ]);
    add_user_meta($user_id, 'RN24_ORDINALE', $group);

    // Send password reset email to the user
    try {
        $result = wp_mail($email, 'Accedi a RN24', 'username: ' . $email . "\nPassword: " . $password);  
    } catch (Exception $e) {
        var_dump($e);
        //TODO gestione errore invio
    }
}
