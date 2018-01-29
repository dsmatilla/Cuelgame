<?php
include('../config.php');
include(mnminclude.'user.php');

echo "Starting rewards script...\n";

echo "Fetching pending links...\n";
$transactions = $db->get_results(
    "SELECT t1.link_author as link_author, t1.link_id as link_id, t2.address as address
     FROM links as t1, wallets as t2
     WHERE t1.link_author = t2.user_id AND 
     t1.link_status = 'published' AND 
     t1.link_id NOT IN (
        SELECT link_id FROM wallets_rewards
     )"
);

echo "Fetching pending payments...\n";
$topay = $db->get_results(
    "SELECT t1.link_author as link_author, t1.link_id as link_id, t2.address as address, count(*) * ".$globals['cc_reward']." as total
     FROM links as t1, wallets as t2
     WHERE t1.link_author = t2.user_id AND 
     t1.link_status = 'published' AND 
     t1.link_id NOT IN (
        SELECT link_id FROM wallets_rewards
     )
     GROUP BY t1.link_author
     "
);

echo "Inserting pending links in 'paid' table...\n";
foreach($transactions as $transaction) {
    $db->query(
      "INSERT INTO wallets_rewards (link_id, amount) VALUES ('".$transaction->link_id."', '".$globals['cc_reward']."')"
    );
}

echo "Processing payments...\n";
$cc = new Cuelgacoin($globals['cc_host'], $globals['cc_user'], $globals['cc_pass']);
foreach($topay as $payment) {
    $res = $cc->sendFrom("", $payment->address, $payment->total);
    echo "Sending ".$payment->total." CLG to ".$payment->address.". Result : ".$res->result."\n";
}
unset($cc);
echo "Payments done...\n";