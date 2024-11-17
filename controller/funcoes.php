<?php
include '../db.php';

// Função para criar os registros das chamadas no banco de dados
function create($table, $data) {
    global $conn;
    $keys = implode(',', array_keys($data));
    $values = ':' . implode(', :', array_keys($data));

    $sql = "INSERT INTO $table ($keys) VALUES ($values)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);

    return $conn->lastInsertId();
}

// Função para ler os registros das chamadas no banco de dados
function read($table, $conditions = []) {
    global $conn;

    $sql = "SELECT * FROM $table";
    if (!empty($conditions)) {
        $filters = implode(' AND ', array_map(fn($key) => "$key = :$key", array_keys($conditions)));
        $sql .= " WHERE $filters";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($conditions);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para atualizar os registros das chamadas no banco de dados
function update($table, $data, $conditions) {
    global $conn;

    $fields = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));
    $filters = implode(' AND ', array_map(fn($key) => "$key = :where_$key", array_keys($conditions)));

    $sql = "UPDATE $table SET $fields WHERE $filters";
    $stmt = $conn->prepare($sql);

    foreach ($data as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    foreach ($conditions as $key => $value) {
        $stmt->bindValue(":where_$key", $value);
    }

    $stmt->execute();
}

// Função para deletar os registros das chamadas no banco de dados
function delete($table, $conditions) {
    global $conn;

    $filters = implode(' AND ', array_map(fn($key) => "$key = :$key", array_keys($conditions)));
    $sql = "DELETE FROM $table WHERE $filters";
    $stmt = $conn->prepare($sql);
    $stmt->execute($conditions);
}

// Função para juntar a ordem de serviço, o produto e o cliente
function readOrdensServico() {
    global $conn;
    $sql = "
        SELECT 
            os.id, 
            os.numero_ordem, 
            os.data_abertura, 
            c.nome AS cliente_nome, 
            p.descricao AS produto_nome
        FROM 
            ordens os
        LEFT JOIN 
            clientes c ON os.cliente_id = c.id
        LEFT JOIN 
            produtos p ON os.produto_id = p.id
    ";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


?>