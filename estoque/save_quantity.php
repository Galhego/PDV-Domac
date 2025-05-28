<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_POST['id'] ?? null;
$quantidade_add = $_POST['quantidade'] ?? 0;

if (!$id || $quantidade_add <= 0) {
    die("Dados inválidos.");
}

try {
    // Atualiza a quantidade no estoque
    $stmt = $pdo->prepare("UPDATE estoque SET quantidade = quantidade + ? WHERE id = ?");
    $stmt->execute([$quantidade_add, $id]);


    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    die("Erro ao atualizar quantidade: " . $e->getMessage());
}