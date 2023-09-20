<?php
date_default_timezone_set("Asia/Jakarta");
header('Content-Type: application/json');
require_once 'DatabaseConnection.php';
class Transactions
{
    private DatabaseConnection $db;

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
                'status' => '400',
                'message' => 'Bad Request',
            ), JSON_PRETTY_PRINT);
            $this->db->closeConnection();
            die();
        }
        
        $senderCurrentBalance = $result['result'][0]['b1'];
        $receiverCurrentBalance = $result['result'][0]['b2'];

        // check balance whether account balance is less than transfer amount or not
        if ($senderCurrentBalance < $transferAmount) {
            http_response_code(response_code: 400);
            echo json_encode(array(
                'status' => '400',
                'message' => 'Account balance is less than transfer amount.',
            ), JSON_PRETTY_PRINT);
            $this->db->closeConnection();
            die();
        }
        
        $totalAmount = $receiverCurrentBalance + $transferAmount;
        $currentBalance = $senderCurrentBalance - $transferAmount;
        $query = "UPDATE user sender, user receiver SET sender.balance = $currentBalance, receiver.balance = $totalAmount WHERE sender.account_id = $fromAccount AND receiver.account_id = $toAccount";
        $result = $this->db->executeUpdateQuery($query);
        
        if(is_null($result['result'])) {
            http_response_code(response_code: 400);
            echo json_encode(array(
                'status' => '400',
                'message' => 'Bad Request'
            ), JSON_PRETTY_PRINT);
            $this->db->closeConnection();
            die();
        }

        $date = date('Y-m-d');
        $time = date('h:m:s');
        
        $this->updateTransactionHistory($fromAccount, $toAccount, $transferAmount, $date, $time);

        http_response_code(200);

        echo json_encode(array(
            'status' => $result['result'],
            'date' => $date,
            'time' => $time,
            'message' => 'Transfer to account ' . $toAccount . ' successful.'
        ), JSON_PRETTY_PRINT);

        $this->db->closeConnection();
    }

    // Function to retrieve transaction history for an account
    public function getTransactionHistory($accountID): array
    {
        $query = "SELECT * FROM transfer_detail WHERE account_id = $accountID";
        $result = $this->db->executeSelectQuery($query);

        try {
            $result = $this->db->executeSelectQuery($query, $result);
            if (!empty($result['result'])) {
                return $result['result'];
            } else {
                return [];
            }
        } catch (Exception $err) {
            error_log("Error in getTransactionHistory: " . $err->getMessage());
            return [];
        }
    }

    // Function to retrieve transaction history from specific user for an account
    public function getTransactionsHistoryFromUser($accountID, $fromUser) 
    {

    }

    private function updateTransactionHistory($fromAccount, $toAccount, $transferAmount, $date, $time): void
    {
        $arrayQuery = array(
            "INSERT INTO transfer (fromAccount) VALUES ($fromAccount)",
            "SET @last_id = LAST_INSERT_ID()",
            "INSERT INTO transfer_detail (transfer_id, account_id, date, time, amount) VALUES (@last_id, $toAccount, '$date', '$time', $transferAmount)"
        );

        $result = $this->db->executeInsertQuery($arrayQuery);

        if(is_null($result['result'])) {
            http_response_code(response_code: 400);
            echo json_encode(array(
                'status' => '400',
                'message' => 'Bad Request'
            ), JSON_PRETTY_PRINT);
            die();
        }
    }
}