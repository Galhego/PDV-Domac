<?php
require_once '../config/db.php';

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM funcionarios WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    die("Erro ao excluir funcionário: " . $e->getMessage());
}
?>