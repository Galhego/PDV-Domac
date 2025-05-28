<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nome = $_POST['nome'];
    $cargo = $_POST['cargo'];
    $telefone = $_POST['telefone'];
    
    if ($id) {
        // Atualizar funcionário
        $stmt = $pdo->prepare("UPDATE funcionarios SET nome = ?, cargo = ?, telefone = ? WHERE id = ?");
        $stmt->execute([$nome, $cargo, $telefone, $id]);
    } else {
        // Criar novo funcionário
        $stmt = $pdo->prepare("INSERT INTO funcionarios (nome, cargo, telefone) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $cargo, $telefone]);
    }
    
    header('Location: index.php');
    exit;
}
?>