<?php
require_once '../config/db.php';

$venda_id = $_GET['id'] ?? null;

if (!$venda_id) {
    header('Location: index.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $stmtItens = $pdo->prepare("
        SELECT vp.quantidade, p.produto as nome_item
        FROM venda_produtos vp
        INNER JOIN produtos p ON vp.produto_id = p.id
        WHERE vp.venda_id = ?
    ");
    $stmtItens->execute([$venda_id]);
    $itensParaDevolver = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    $stmtUpdateEstoque = $pdo->prepare("UPDATE estoque SET quantidade = quantidade + ? WHERE item = ?");

    foreach ($itensParaDevolver as $item) {
        $stmtUpdateEstoque->execute([$item['quantidade'], $item['nome_item']]);
    }

    $stmtDelItens = $pdo->prepare("DELETE FROM venda_produtos WHERE venda_id = ?");
    $stmtDelItens->execute([$venda_id]);

    $stmtDelVenda = $pdo->prepare("DELETE FROM vendas WHERE id = ?");
    $stmtDelVenda->execute([$venda_id]);

    $pdo->commit();

    header('Location: index.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: index.php');
    exit;
}
?>