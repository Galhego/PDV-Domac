<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $produto = $_POST['produto'];
    $descricao = $_POST['descricao'];
    $preco = str_replace(',', '.', $_POST['preco']);
    $disponivel = $_POST['disponivel'];
    
    if ($id) {
        // Atualizar produto
        $stmt = $pdo->prepare("UPDATE produtos SET produto = ?, descricao = ?, preco = ?, disponivel = ? WHERE id = ?");
        $stmt->execute([$produto, $descricao, $preco, $disponivel, $id]);
    } else {
        // Criar novo produto
        $stmt = $pdo->prepare("INSERT INTO produtos (produto, descricao, preco, disponivel) VALUES (?, ?, ?, ?)");
        $stmt->execute([$produto, $descricao, $preco, $disponivel]);
    }
    
    header('Location: index.php');
    exit;
}
?>