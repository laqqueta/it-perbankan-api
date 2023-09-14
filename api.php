<?php
require_once 'api/Account.php';
require_once 'api/Transactions.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', $_SERVER['REQUEST_URI']);

$transaction = new Transactions();
$account = new Account();

if ($method == 'GET') {
    if (count($uri) < 2) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error']);
        die();
    }

    $endpoint = $uri[count($uri) - 2];
    $account_id = $uri[count($uri) - 1];

    if ($endpoint == 'balance' && !empty($account_id)) {
        $account->getAccountBalance($account_id);
    } elseif ($endpoint == 'transactions' && !empty($account_id)) {
        $transaction->getTransactionHistory($account_id);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }
} elseif ($method == 'POST') {
    $endpoint = $uri[count($uri) - 1];

    if($endpoint == 'transfer') {
        $jsonData = json_decode(file_get_contents('php://input'));

        $fromAccount = $jsonData->fromAccount ?? ($_POST['fromAccount'] ?? '');
        $toAccount = $jsonData->toAccount ?? ($_POST['toAccount'] ?? '');
        $amount = $jsonData->amount ?? ($_POST['amount'] ?? '');

        if(!empty($fromAccount) && !empty($toAccount) && !empty($amount)) {
            $transaction->transferMoney($fromAccount, $toAccount, $amount);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}