<?php
defined('mnminclude') or die();

$db_wallet = $db->get_results(
    "SELECT address, balance FROM wallets WHERE user_id=".$current_user->user_id
);

if($db_wallet->address == "") {
    if($current_user->user_id == $user->id) {
        $cc = new Cuelgacoin($globals['cc_host'], $globals['cc_user'], $globals['cc_pass']);
        $res = $cc->getAccountAddress($globals['cc_prefix'] . $current_user->user_id);
        $wallet = $res->result;
        $balance = 0.00000000;
        $db->query(
            "INSERT INTO wallets (user_id, address, balance) VALUES ('" . $current_user->user_id . "','" . $wallet . "','" . $balance . "')"
        );
    } else {
        $wallet = "N/A";
        $balance = 0;
    }
} else {
    $wallet = $db_wallet->address;
    $balance = $db_wallet->balance;
}

return Haanga::Load('currency/wallet.html', compact(
    'wallet', 'balance'
));


