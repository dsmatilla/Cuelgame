<?php
defined('mnminclude') or die();

$db_wallet = $db->get_results(
    "SELECT address FROM wallets WHERE user_id=".$user->id
);

if($db_wallet[0]->address == "") {
    if($current_user->user_id == $user->id) {
        $cc = new Cuelgacoin($globals['cc_host'], $globals['cc_user'], $globals['cc_pass']);
        $res = $cc->getAccountAddress($globals['cc_prefix'] . $current_user->user_id);
        unset($cc);
        $wallet = $res->result;
        if($wallet)
            $db->query(
                "INSERT INTO wallets (user_id, address) VALUES ('" . $current_user->user_id . "','" . $wallet . "')"
            );
    } else {
        $wallet = "N/A";
    }
} else {
    $wallet = $db_wallet[0]->address;
}

if($current_user->user_id == $user->id) {
    $cc = new Cuelgacoin($globals['cc_host'], $globals['cc_user'], $globals['cc_pass']);
    $res = $cc->getBalance($globals['cc_prefix'] . $current_user->user_id);
    unset($cc);
    if(is_numeric($res->result)) {
        $balance = $res->result;
    } else {
        $balance = "Error";
    }
} else {
    $balance = "Privado";
}

$to_address = $_POST['to_address'];
$to_amount = $_POST['to_amount'];
if($_POST['boton'] == "Enviar" OR $_POST['boton'] == "Confirmar") {
    if($_POST['to_amount']=="" OR $_POST['to_address']=="") {
        $error = "Tienes que introducir todos los campos para realizar una transacción";
    } elseif(!is_numeric($_POST['to_amount'])) {
        $error = "Cantidad errónea. Revisa los datos introducidos.";
    } else {
        $cc = new Cuelgacoin($globals['cc_host'], $globals['cc_user'], $globals['cc_pass']);
        $res = $cc->validateAddress($_POST['to_address']);
        if(!$res->result->isvalid) {
            $error = "La dirección introducida no es una cartera CLG válida. Revisa los datos.";
        } else {
            if($_POST['boton'] == "Enviar") {
                $confirm = true;
            } elseif($_POST['boton'] == "Confirmar") {
                if($_POST['ts'] < date("U") - 30) {
                    $error = "La transacción ha caducado. Vuelve a empezar.";
                } else {
                    $res = $cc->sendFrom($globals['cc_prefix'].$current_user->user_id, $_POST['to_address'], $_POST['to_amount']);
                    if($res->error) {
                        $error = "Error enviando la transacción : (".$res->error->code.") ".$res->error->message;
                    } else {
                        $success = "Transacción insertada con éxito con ID : ".$res->result;
                        $_POST['to_address']="";
                        $_POST['to_amount']="";
                    }
                }
            }
        }
        unset($cc);
    }
} else {

}

$ts = date("U");

return Haanga::Load('currency/wallet.html', compact(
    'wallet', 'balance', 'current_user', 'user', 'error', 'success', 'to_address', 'to_amount', 'confirm', 'ts'
));