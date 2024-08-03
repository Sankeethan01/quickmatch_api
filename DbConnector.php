<?php
/**
	* Database Connection
	*/

    class DBConnector {
        private $host = 'localhost';
        private $db = 'quickmatch';
        private $user = 'root';
        private $pass = '';
        //private $charset = 'utf8mb4'; charset=$this->charset
        private $pdo;
        
        public function connect() {
            $dsn = "mysql:host=$this->host;dbname=$this->db;";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            try {
                $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
            
            return $this->pdo;
        }
    }
    