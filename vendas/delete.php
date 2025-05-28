<?php
require_once '../config/db.php';

$venda_id = $_GET['id'];

try {
    // Deletar produtos da venda primeiro por causa da chave estrangeira
    $stmt = $pdo->prepare("DELETE FROM venda_produtos WHERE venda_id = ?");
    $stmt->execute([$venda_id]);
    
    // Deletar a venda
    $stmt = $pdo->prepare("DELETE FROM vendas WHERE id = ?");
    $stmt->execute([$venda_id]);
    
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    die("Erro ao excluir venda: " . $e->getMessage());
}
?>