# RN24 SIGNUP WordPress Plugin

## Utilizzo
### Shortcode
Il plugin fornisce uno shortcode da inserire nella pagina desiderata
`[rn24_signup_form]`

### Cosa da sapere
- Il form per la registrazione non viene mostrato agli utenti registrati
- Viene suggerito un indirizzo email a seconda del gruppo selezionato
- non possono registrarsi nuovamente se sono già registrati con quella email
- il plugin setta come username l'ordinale del gruppo
- viene salvato l'ordinale del gruppo nello user_meta `RN24_ORDINALE`.


## Dati
Il json dei gruppi è in `data/groups.json` generato con il sito https://products.aspose.app/cells/conversion/excel-to-json con il file fornito dalla segreteria

## Test
`php rn24-signup/test.php`
