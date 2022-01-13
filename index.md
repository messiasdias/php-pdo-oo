# PHP PDO 
## Ajudando um amigo com PDO

O arquivo [index.php](#index)
Instacia a Classe [DataBase](#database) que usa de forma associada a classe [Contact](#contact)
para `Inserir`, `Selecionar`, `Atualizar` e `Excluir` dados da tabela `contacts`.

> Nota: O código contido nesse repositório deve servir como referência pra futuras implementações reais. Algumas práticas tipo passar dados do banco diretamente não são aconselhadas num cenário real. 

Subindo banco de dados com Docker-Compose (se preferir):
* dbname: php_pdo_oo | string
* user: root | string
* pass: root | string
* host: 127.0.0.1 | string
* port: 33068 | int

```bash
docker-compose up
```

Rodado a aplicação de teste no terminal:
```bash
php index.php
```

```bash
# Resultado abaixo:
Banco de Dados conectado!
PDO Object
(
)
Tabela 'contacts' Criada!
Total de dados inseridos na tabela 'contacts' 3!

            ---------------------------

            Nome: Evandro José

            Email: evandro@teste.com

            Telefone: +5581983538087

            Criado: 13/01/2022 20:02:40

            ----------------------------



            ---------------------------

            Nome: Domingos Ksaé

            Email: domingos@teste.com

            Telefone: +5581983538187

            Criado: 13/01/2022 20:02:40

            ----------------------------



            ---------------------------

            Nome: Donald Trump

            Email: trump@teste.com

            Telefone: +5581983538287

            Criado: 13/01/2022 20:02:40

            ----------------------------


Total de dados atualizados na tabela 'contacts' 1!
Novo nome do usuario 'evandro@teste.com': Evandro José Da Silva 
Usuário 'evandro@teste.com' excluido da tabela!
Total de dados excluidos na tabela 'contacts' 2!
```


## <a id="index"></a> Index

```php
<?php 
//Autoload classes
require("vendor/autoload.php");

use App\DataBase;
use App\Contact;

//Ciar Objeto PDO
$database = new DataBase(
    'php_pdo_oo', 
    'root', 
    'root', 
    '127.0.0.1', 
    33068, 
    Contact::class
);

//Tentando se conexão
if ($database->isConnected()) {
    //Somente para visualização, deve ser removido num código real
    echo  "Banco de Dados conectado!\n";

    //A partir daqui a conexão pode ser usada para selct, insert, update e delete no banco de dados
    print_r($database->getConnection());

    /*
        O print_r é só pra mostar que a connexao foi criada
        Deve Aparecer isso:

        Banco de Dados conectado!
        PDO Object
        (
        )
    */


    //Create Table - Criando uma tabela qualquer, chamada contacts (contatos)
    try {
        $database->exec(
            "CREATE TABLE IF NOT EXISTS contacts (
                id INT(11) NOT NULL AUTO_INCREMENT, 
                name VARCHAR(50) NOT NULL, 
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(25) NOT NULL,
                created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY(id)
            );"
        );

        echo "Tabela 'contacts' Criada!\n";
    }  catch(\Exception $e) {
        die("Erro ao criar tabela 'contacts': {$e->getMessage()}");
    }



    //Insert - Inserindo Dados na Tabela
    try {
        $inserteds =  $database->exec(
            "INSERT INTO contacts 
            (name, email, phone)
            VALUES 
            ('Evandro José', 'evandro@teste.com', '+5581983538087'),
            ('Domingos Ksaé', 'domingos@teste.com', '+5581983538187'),
            ('Donald Trump', 'trump@teste.com', '+5581983538287');"
        );

        if ($inserteds) echo "Total de dados inseridos na tabela 'contacts' {$inserteds}!\n";
    }  catch(\Exception $e) {
        die("Erro ao inserir os dados na tabela 'contacts': {$e->getMessage()}");
    }


    //Select - Buscando dados do banco
    try {
        $statement = $database->query("SELECT * FROM contacts;", \PDO::FETCH_ASSOC);

        /* 
            \PDO::FETCH_ASSOC - Associa o nome da coluna ao valor 
            $statement->fetchAll() - Retornar um array das linha da tabela
            $contato - Equivale a linha da tabela
        */
        foreach ($statement->fetchAll() as $contato) {
            $created  = date('d/m/Y H:i:s', strtotime($contato->created));
            echo "
            ---------------------------\n
            Nome: {$contato->name}\n
            Email: {$contato->email}\n
            Telefone: {$contato->phone}\n
            Criado: {$created}\n
            ----------------------------\n\n\n";
        }
        
    }  catch(\Exception $e) {
        die("Erro ao selecionar os dados na tabela 'contacts': {$e->getMessage()}");
    }



    // Update  - Excluindo dados de um contato
    try {
        $updateds = $database->exec("UPDATE contacts SET name = 'Evandro José Da Silva' WHERE email = 'evandro@teste.com';");
        //WHERE email = 'evandro@teste.com' pode ser convertido para WHERE email = 1 por exemplo, adepender do contato que deseja excluir
        if ($updateds) echo "Total de dados atualizados na tabela 'contacts' {$updateds}!\n";


        //Select de verificação
        $statement = $database->query("SELECT * FROM contacts WHERE email = 'evandro@teste.com';");
        $contato = $statement->fetch();
        if ($updateds) echo "Novo nome do usuario 'evandro@teste.com': {$contato->name} \n";


    }  catch(\Exception $e) {
        die("Erro ao excluir os dados na tabela 'contacts': {$e->getMessage()}");
    }


    // Delete  - Excluindo dados de um contato
    try {
        $deleteds = $database->exec("DELETE FROM contacts WHERE email = 'evandro@teste.com';");
        if ($deleteds) echo "Usuário 'evandro@teste.com' excluido da tabela!\n";
    }  catch(\Exception $e) {
        die("Erro ao excluir os dados na tabela 'contacts': {$e->getMessage()}");
    }

    // Delete  - Excluindo dados  de muitos contatos
    try {
        $deleteds = $database->exec("DELETE FROM contacts WHERE email IN ('domingos@teste.com', 'trump@teste.com');");
        if ($deleteds) echo "Total de dados excluidos na tabela 'contacts' {$deleteds}!\n";
    }  catch(\Exception $e) {
        die("Erro ao excluir os dados na tabela 'contacts': {$e->getMessage()}");
    }


} else {
    echo "{$database->getError()}\n"; //Somente para visualização, deve ser removido num código real se preferir
}
```


## <a id="database"></a>  App\Database

```php
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
```


## <a id="contact"></a> App\Contact

```php
<?php
namespace App;

class Contact {
    protected $id, $name, $email, $phone, $created;

    //Using  Magic methods __get
    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    //Using  Magic methods __set
    public function __set($property, $value) {
        if (property_exists($this, $property) && $property != 'id') {
            $this->$property = $value;
        }
    }
}
```