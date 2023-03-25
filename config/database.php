<?php

class Database {

    private $host = 'localhost';
    private $name = 'assignment';
    private $user = 'rs';
    private $pass = '123';
    public $conn;

    public function getConnection(): PDO {
        $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";

        return new PDO($dsn, $this->user, $this->pass, [
            PDO::ATTR_EMULATE_PREPARES  => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ]);
    }

}
