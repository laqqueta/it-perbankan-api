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

    }

    public function getAccountProfile($accountID)
    {

    }
}