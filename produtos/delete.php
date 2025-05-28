<?php
require_once '../config/db.php';

$id = $_GET['id'];

try {
    // Verificar se o produto está em alguma venda
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM venda_produtos WHERE produto_id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->fetchColumn() > 0) {
        die("Não é possível excluir este produto pois ele está associado a vendas.");
    }
    
    // Deletar produto
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    die("Erro ao excluir produto: " . $e->getMessage());
}
?>