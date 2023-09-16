<?php
header('Content-Type: application/json');

require_once 'api/Account.php';
require_once 'api/Transactions.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', $_SERVER['REQUEST_URI']);
$uri_processed = false;

$transaction = new Transactions();
$account = new Account();

switch ($method) {
    case 'GET':
        if (count($uri) <= 3) {
            break;
        }

        $endpoint = $uri[count($uri) - 2];
        $account_id = $uri[count($uri) - 1];

        if ($endpoint == 'balance' && !empty($account_id)) {
            $uri_processed = true;
            http_response_code(200);
            echo json_encode(array(
                'status' => '200',
                'endpoing' => 'balance',
            ), JSON_PRETTY_PRINT);
            //$account->getAccountBalance($account_id);
        } elseif ($endpoint == 'transactions' && !empty($account_id)) {
            $uri_processed = true;
            //$transaction->getTransactionHistory($account_id);
            http_response_code(200);
            echo json_encode(array(
                'status' => '200',
                'endpoing' => 'transactions',
            ), JSON_PRETTY_PRINT);
        }
        break;
    case 'POST':
        $endpoint = $uri[count($uri) - 1];
        if($endpoint == 'transfer') {
            $jsonData = json_decode(file_get_contents('php://input'));

            $fromAccount = $jsonData->fromAccount ?? ($_POST['fromAccount'] ?? '');
            $toAccount = $jsonData->toAccount ?? ($_POST['toAccount'] ?? '');
            $amount = $jsonData->amount ?? ($_POST['amount'] ?? '');

            if(!empty($fromAccount) && !empty($toAccount) && !empty($amount)) {
                $uri_processed = true;
                $transaction->transferMoney($fromAccount, $toAccount, $amount);
            }
        }
        break;
}

if(!$uri_processed) {
    http_response_code(404);
    echo json_encode(array(
        'status' => '404',
        'masssage' => 'Not Found',
    ), JSON_PRETTY_PRINT);
}