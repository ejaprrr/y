<?php
function validate_username($username) {
    if (empty($username)) {
        return "Uživatelské jméno je povinné.";
    }
    if (strlen($username) < 3 || strlen($username) > 24) {
        return "Uživatelské jméno musí mít 3 až 24 znaků.";
    }
    if (!preg_match('/^[a-z0-9_]+$/', $username)) {
        return "Uživatelské jméno může obsahovat pouze malá písmena, čísla a podtržítka.";
    }
    if ($username[0] === '_') {
        return "Uživatelské jméno nesmí začínat podtržítkem.";
    }
    return true;
}


function validate_password($password) {
    if (empty($password)) {
        return "Heslo je povinné.";
    }
    if (strlen($password) < 8) {
        return "Heslo musí mít alespoň 8 znaků.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Heslo musí obsahovat alespoň jedno malé písmeno.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Heslo musí obsahovat alespoň jedno velké písmeno.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Heslo musí obsahovat alespoň jedno číslo.";
    }
    if (!preg_match('/[\W_]/', $password)) {
        return "Heslo musí obsahovat alespoň jeden speciální znak.";
    }
    return true;
}

?>