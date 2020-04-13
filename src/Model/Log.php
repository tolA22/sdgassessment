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
        // echo "here";
        
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
            $statement->bindValue(':time',$this->format($input["time"]));
            $statement->execute();
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    private function format($time){
        // print($time);
        $time = (int)($time * 1000);
        if($time < 10){
            $time = (int)("0".(string)$time);
        }
        if($time >100){
            $time = (int)(((string)$time)[0].((string)$time)[1]);
        }
        // print($time);
        return $time;
    }
}
