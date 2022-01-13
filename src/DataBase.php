<?php
namespace App;

class DataBase {
    private $connection, $className, $classConstructorArgs, $error;
    
    public function __construct(
        string $dbname,
        string $user,
        string $pass,
        string $host = 'localhost',
        int $port = 3306,
        string $className = null,
        array $classConstructorArgs = []
    ) {
        $this->className = $className;
        $this->classConstructorArgs = $classConstructorArgs;

        try {
            $this->connection = new \PDO("mysql:host={$host};port={$port};dbname={$dbname}", $user, $pass); 
        } catch(\Exception $e) {
            $this->error = "Erro ao tentar se conectar ao banco de dados: {$e->getMessage()}";
        }
    }

    public function getConnection() : \PDO
    {
        return $this->connection;
    }

    public function getError() : string
    {
        return $this->error;
    }

    public function isConnected() : bool
    {
        return $this->connection && !$this->error;
    }

    public function query(string $sql) : ?\PDOStatement
    {
        if ($this->isConnected()) {
            //Return PDOStatement or null
            return $this->connection->query(
                $sql, 
                \PDO::FETCH_CLASS ,  
                $this->className, 
                $this->classConstructorArgs
            ) ?: null;
        }
        return null;
    }

    public function exec(string $sql) : int
    {
        if ($this->isConnected()) {
            return $this->connection->exec($sql);
        }
        return 0;
    }
}