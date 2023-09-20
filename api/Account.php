<?php
require_once 'DatabaseConnection.php';

header('Content-Type: application/json');
class Account {
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseConnection();
    }

    // Function to retrieve account balance
    public function getAccountBalance($accountID)
    {
        $query = "SELECT balance FROM user WHERE account_id = $accountID";
        $result = $this->db->executeSelectQuery($query);

        try {
            $result = $this->db->executeSelectQuery($query, $result);
            if (!empty($result['result'])) {
                return $result['result'][0]['balance'];
            } else {
                return null;
            }
        } catch (Exception $err) {
            error_log("Error in getAccountBalance: " . $err->getMessage());
            return null;
        }

        
    }

    public function getAccountProfile($accountID)
    {

    }
}