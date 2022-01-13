<?php 
//Autoload classes
require("vendor/autoload.php");

use App\DataBase;
use App\Contact;

//Ciar Objeto PDO
$database = new DataBase('php_pdo_oo', 'root', 'root', '127.0.0.1', 33068, Contact::class);

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