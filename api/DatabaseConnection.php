<?php
class DatabaseConnection {
    private mysqli|false $db;

    public function __construct()
    {
        try {
            $this->db = mysqli_init();
            mysqli_options ($this->db, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);

            $this->db->ssl_set(
                NULL,
                NULL,
                'api/ssl/DigiCertGlobalRootCA.crt.pem',
                NULL,
                NULL);

            mysqli_real_connect(
                $this->db,
                'it-perbankan-api.mysql.database.azure.com', // DB HOST
                'apiadmin', // DB USERNAME
                '5perbankan@', // DB PASSWORD
                'perbankan_api', // Database
                3306,
            );
        } catch (Exception $err) {
            header('Content-Type: application/json');
            http_response_code(response_code: 401);

            echo json_encode(array(
                'status' => 'Unauthorized',
                'message' => $err->getMessage(),
            ), JSON_PRETTY_PRINT);

            die();
        }

        // Display a message if database connection is successfully
        /*header('Content-Type: application/json');
        http_response_code(200);

        echo json_encode(array(
            'status' => 'OK',
            'message' => 'Connection successful',
        ), JSON_PRETTY_PRINT);*/
    }

    public function executeInsertQuery(array $query) {
        try {
            $this->db->begin_transaction();

            foreach ($query as $q) {
                $statement = $this->db->prepare($q);
                $statement->execute();
            }

            $status = $this->db->commit();

            return $status ? array('result' => 'successful') : array('result' => NULL);
        } catch (Exception $err) {
            header('Content-Type: application/json');
            http_response_code(response_code: 400);

            echo json_encode(array(
                'status' => 'Bad Request',
                'message' => $err->getMessage(),
            ), JSON_PRETTY_PRINT);
            $this->db->close();
            die();
        }
    }

    public function executeUpdateQuery($query) {
        try {
            $statement = $this->db->prepare($query);
            $status = $statement->execute();

            return $status ? array('result' => 'successful') : array('result' => NULL);
        } catch (Exception $err) {
            header('Content-Type: application/json');
            http_response_code(response_code: 400);

            echo json_encode(array(
                'status' => 'Bad Request',
                'message' => $err->getMessage(),
            ), JSON_PRETTY_PRINT);
            $this->db->close();
            die();
        }
    }

    public function executeSelectQuery($query) {
        try{
            $statement = $this->db->prepare($query);

            $statement->execute();
            $res = $statement->get_result();
            $rows = $res->fetch_all(MYSQLI_ASSOC);

            return count($rows) < 1 ? array('result' => NULL) : array('result' => $rows);
        } catch (Exception $err) {
            header('Content-Type: application/json');
            http_response_code(response_code: 400);

            echo json_encode(array(
                'status' => 'Bad Request',
                'message' => $err->getMessage(),
            ), JSON_PRETTY_PRINT);
            $this->db->close();
            die();
        }
    }

    public function closeConnection() : void {
        $this->db->close();
    }
}