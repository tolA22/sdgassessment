<?php

namespace Src\Model;


class Log {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findAll()
    {
        $statement = "
            SELECT 
                verb, uri, status_code, time
            FROM
                logs;
        ";

        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function insert(Array $input)
    {
        
        $statement = "
            INSERT INTO logs 
                (verb, uri, status_code, time)
            VALUES
                (:verb, :uri, :status_code, :time);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->bindValue(':verb',$input["verb"]);
            $statement->bindValue(':uri',$input["uri"]);
            $statement->bindValue(':status_code',$input["status"]);
            $statement->bindValue(':time',$input["time"]);
            $statement->execute();
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }
}
