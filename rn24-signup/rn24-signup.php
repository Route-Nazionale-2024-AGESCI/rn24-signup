<?php

/*
 * Plugin Name: RN24 singup
 * Author: matte1
 * Description: Handle group registration
 * Version: 0.1.2
 * Requires PHP: 8.0
 */

require_once 'ajax.php';
require_once 'utils.php';
require_once 'exceptions.php';
require_once 'validations.php';

if (!session_id()) {
    session_start();
}

const REGIONI = [
    "Abruzzo",
    "Basilicata",
    "Calabria",
    "Campania",
    "Emilia Romagna",
    "Friuli Venezia Giulia",
    "Lazio",
    "Liguria",
    "Lombardia",
    "Marche",
    "Molise",
    "Piemonte",
    "Puglia",
    "Sardegna",
    "Sicilia",
    "Toscana",
    "Trentino Alto Adige",
    "Umbria",
    "Valle d'Aosta",
    "Veneto",
];

function rn24_setup_scripts() {
    wp_register_style('rn24-signup-select2-style', rn24_get_plugin_url('assets/vendor/select2/css/select2.min.css'));
    wp_enqueue_style('rn24-signup-select2-style');
    wp_enqueue_script('rn24-signup-select2-script', rn24_get_plugin_url('assets/vendor/select2/js/select2.min.js'), array( 'jquery' ) );

    //wp_register_style('rn24-signup-bootstrap-style', rn24_get_plugin_url('assets/vendor/bootstrap/css/bootstrap.min.css'));
    //wp_enqueue_style('rn24-signup-bootstrap-style');
    //wp_enqueue_script('rn24-signup-bootstrap-script', rn24_get_plugin_url('assets/vendor/bootstrap/js/bootstrap.min.js'), array( 'jquery' ) );
    
    wp_enqueue_script('rn24-signup-script', rn24_get_plugin_url('assets/js/form.js'), array( 'rn24-signup-select2-script' ) );

    wp_localize_script('rn24-signup-script', 'rn24_ajax_object',
		array( 
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'action' => 'rn24_select_groups',
		)
	);
}

add_action('wp_enqueue_scripts', 'rn24_setup_scripts');


function _get_form() {
    $regioni_opts = '';

    foreach(REGIONI as $regione)
        $regioni_opts .= sprintf('<option value="%s">%s</option>', $regione, $regione);

    $errors = get_and_remove('rn24_signup_errors', $_SESSION, []);

    $old = get_and_remove('rn24_signup_old', $_SESSION, []);

    $signupform = '';

    $error_messages = [];

    if($errors && count($errors) > 0) {
        $hide = '';
        $signupform .= <<<ALERT
            <div class="alert alert-danger" role="alert">
                Sono presenti degli errori
            </div>
        ALERT;

        foreach($errors as $param => $message) {
            $error_messages[$param] = "<small class=\"form-text text-danger\">$message</small>";
        }

        $disabled = '';
    }
    else {
        $hide = 'display: none;';
        $disabled = 'disabled';
    }
        


    if($group_value = rn24_get('group', $old))
        $group_value = sprintf(
            '<option value="%s" SELECTED>%s</option>',
            $group_value,
            get_group_denominazione_from_ordinale($group_value, $group_value)
        );
    else
        $group_value = '';

    if($region_value = rn24_get('region', $old))
        $region_value = sprintf('<option value="%s" SELECTED>%s</option>', $region_value, $region_value);
    else
        $region_value = '';

    $email_value = rn24_get('email', $old);

    $region_error = rn24_get('region', $error_messages, '');
    $group_error = rn24_get('group', $error_messages, '');
    $email_error = rn24_get('email', $error_messages, '');
    $signupform .= <<<SIGNUPFORM
        <form method="POST" autocomplete="off" action="" class="rn24SignupForm">
            <div class="form-group">
                <label for="regione">Regione</label>
                <select id="regione" name="region" class="sl2 regione w-100" >
                    <option value="-">Seleziona la tua regione</option>
                    $region_value
                    $regioni_opts
                </select>
                $region_error
            </div>
            <div class="form-group hideShowParent group" style="$hide">
                <label for="gruppo">Gruppo</label>
                <select id="gruppo" name="group" class="sl2 group w-100" style="width: 100%">
                $group_value
                </select>
                $group_error
            </div>
            <div class="form-group hideShowParent email" style="$hide">
                <label for="email">Email</label>
                <input name="email" type="email" class="form-control w-100" id="email" aria-describedby="emailHelp" placeholder="Inserisci email" value="$email_value" disabled />
                $email_error
            </div>
            <button type="submit" name="rn24-signup-submit" class="btn btn-primary" $disabled>Registrati</button>
        </form>
    SIGNUPFORM;

    return $signupform;
}

function _get_success_message() {
    return <<<SUCCESSMESSAGE
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Registrazione avvenuta con successo!</h4>
            <p>Complimenti, la registrazione è completa.</p>
            <hr>
            <p class="mb-0">Verifica la casella email che hai indicato per accedere al portale.</p>
        </div>
    SUCCESSMESSAGE;
}

function _get_error_message() {
    return <<<SUCCESSMESSAGE
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Errore durante la Registrazione</h4>
            <p>Siamo spiacenti, la registrazione non è completa.</p>
            <hr>
            <p class="mb-0">Contattaci per assistenza.</p>
        </div>
    SUCCESSMESSAGE;
}

function rn24_show_signup_form($atts) {
    if(is_user_logged_in())
        return "Solo gli utenti non registrati possono vedere questa pagina";

    if(isset($_GET['r24_success']))
        return _get_success_message();

    if(isset($_GET['r24_error']))
        return _get_error_message();

    return _get_form();
}

add_shortcode('rn24_signup_form', 'rn24_show_signup_form');


function rn24_handle_form(){
    if(!isset($_POST['rn24-signup-submit'] ) )
        return;
    
    $redirect_url = sprintf(
        '%s%s', get_site_url(), $_SERVER['REQUEST_URI']
    );
    
    $rules = [
        'email' => ['Required', 'ValidEmail', 'EmailExists'],
        'group' => ['Required', 'ExistInGroups:Ordinale', 'UsernameExists'],
    ];

    $_POST['email'] = get_group_email_from_ordinale($_POST['group']);
    $_SESSION['rn24_signup_old'] = $_POST;

    try {
        $cleaned_data = validate($rules, $_POST);

        create_user_and_send_password_email(
            $cleaned_data['email'],
            $cleaned_data['group'],
        );
    }
    catch(ValidationsError $e) {
        $errors = $e->getErrors();
        
        $_SESSION['rn24_signup_errors'] = $errors;
        return;
    }
    catch(Exception $e) {
        wp_redirect($redirect_url.'?r24_error');
        exit();
    }

    wp_redirect($redirect_url.'?r24_success');
    exit();
}

add_action( 'init', 'rn24_handle_form' );