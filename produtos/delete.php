<?php
require_once '../config/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

try {
    $stmtEstoque = $pdo->prepare("SELECT COUNT(*) FROM estoque WHERE item = (SELECT produto FROM produtos WHERE id = ?)");
    $stmtEstoque->execute([$id]);
    $noEstoque = $stmtEstoque->fetchColumn();

    $stmtVendas = $pdo->prepare("SELECT COUNT(*) FROM venda_produtos WHERE produto_id = ?");
    $stmtVendas->execute([$id]);
    $nasVendas = $stmtVendas->fetchColumn();

    if ($noEstoque > 0 || $nasVendas > 0) {
        echo "<script>
            alert('Operação Negada: Este produto não pode ser excluído pois possui registros no Estoque ou em Histórico de Vendas. Inative-o em vez de excluir.');
            window.location.href = 'index.php';
        </script>";
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    die("Erro ao excluir: " . $e->getMessage());
}
?>