<?php
require_once 'api/Account.php';
require_once 'api/Transactions.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

/*
 * For running in localhost uncomment REQUEST_URI and
 * comment PATH_INFO
 * */
//$uri = explode('/', $_SERVER['REQUEST_URI']);

$uri = $_SERVER['PATH_INFO'];

$transaction = new Transactions();
$account = new Account();

if ($method == 'GET') {
    // For running in localhost uncomment this 2 lines
    //$endpoint = $uri[count($uri) - 2];
    //$account_id = $uri[count($uri) - 1];

    // For running in localhost comment from $endpoint ... toa ... $ccount_id
    $endpoint = $uri;
    if(!isset($_GET['account_id'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error']);
        die();
    }
    $account_id = $_GET['account_id'];

    /*
     * For running in localhost change:
     * /balance to balance
     * /transactions to transaction
     * */

    if ($endpoint == '/balance' && !empty($account_id)) {
        $account->getAccountBalance($account_id);
    } elseif ($endpoint == '/transactions' && !empty($account_id)) {
        $transaction->getTransactionHistory($account_id);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }
} elseif ($method == 'POST') {
    // For running in localhost uncomment this
    //$endpoint = $uri[count($uri) - 1];

    $endpoint = $uri;

    // For running in localhost change /transfer to transfer
    if($endpoint == '/transfer') {
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