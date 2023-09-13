<?php
date_default_timezone_set("Asia/Jakarta");
header('Content-Type: application/json');
require_once 'DatabaseConnection.php';
class Transactions
{
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseConnection();
    }

    // Function to perform a money transfer
    public function transferMoney($fromAccount, $toAccount, $transferAmount): void
    {
        $query = "SELECT sender.balance as b1, receiver.balance as b2 FROM user sender, user receiver WHERE sender.account_id = $fromAccount AND receiver.account_id = $toAccount";
        $result = $this->db->executeSelectQuery($query);

        if(is_null($result['result']) || $transferAmount < 0) {
            http_response_code(response_code: 500);
            echo json_encode(array(
                'status' => '500',
                'message' => 'Internal Server Error.',
            ), JSON_PRETTY_PRINT);
            die();
        } else {
            $senderCurrentBalance = $result['result'][0]['b1'];
            $receiverCurrentBalance = $result['result'][0]['b2'];

            // check balance whether account balance is less than transfer amount or not
            if ($senderCurrentBalance < $transferAmount) {
                http_response_code(response_code: 200);
                echo json_encode(array(
                    'status' => 'warning',
                    'message' => 'Account balance is less than transfer amount.',
                ), JSON_PRETTY_PRINT);
            } else {
                $totalAmount = $receiverCurrentBalance + $transferAmount;
                $currentBalance = $senderCurrentBalance - $transferAmount;
                $query = "UPDATE user sender, user receiver SET sender.balance = $currentBalance, receiver.balance = $totalAmount WHERE sender.account_id = $fromAccount AND receiver.account_id = $toAccount";
                $result = $this->db->executeUpdateQuery($query);

                $date = date('Y-m-d');
                $time = date('h:m:s');

                echo $time;

                if(is_null($result['result'])) {
                    http_response_code(response_code: 500);
                    echo json_encode(array(
                        'status' => '500',
                        'message' => 'Something went wrong.'
                    ), JSON_PRETTY_PRINT);
                } else {
                    $this->updateTransactionHistory($fromAccount, $toAccount, $transferAmount, $date, $time);

                    http_response_code(200);

                    echo json_encode(array(
                        'status' => $result['result'],
                        'date' => $date,
                        'time' => $time,
                        'message' => 'Transfer to account ' . $toAccount . ' successful.'
                    ), JSON_PRETTY_PRINT);
                }
            }
        }
    }

    // Function to retrieve transaction history for an account
    public function getTransactionHistory($accountID): void
    {

    }

    // Function to retrieve transaction history from specific user for an account
    public function getTransactionsHistoryFromUser($accountID, $fromUser) {

    }

    private function updateTransactionHistory($fromAccount, $toAccount, $transferAmount, $date, $time) {
        $arrayQuery = array(
            "INSERT INTO transfer (fromAccount) VALUES ($fromAccount)",
            "SET @last_id = LAST_INSERT_ID()",
            "INSERT INTO transfer_detail (transfer_id, account_id, date, time, amount) VALUES (@last_id, $toAccount, '$date', '$time', $transferAmount)"
        );

        $result = $this->db->executeInsertQuery($arrayQuery);

        if(is_null($result['result'])) {
            http_response_code(response_code: 500);
            echo json_encode(array(
                'status' => '500',
                'message' => 'Something went wrong. (Insert)'
            ), JSON_PRETTY_PRINT);
        }
    }
}