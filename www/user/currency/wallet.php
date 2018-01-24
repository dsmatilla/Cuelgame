<?php
defined('mnminclude') or die();

$db_wallet = $db->get_results(
    "SELECT address FROM wallets WHERE user_id=".$current_user->user_id
);

if($db_wallet->address == "") {
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
    $wallet = $db_wallet->address;
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


return Haanga::Load('currency/wallet.html', compact(
    'wallet', 'balance'
));