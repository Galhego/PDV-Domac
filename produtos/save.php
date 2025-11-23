<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $produto = trim($_POST['produto']);
    $descricao = $_POST['descricao'];
    
    $preco = str_replace(',', '.', $_POST['preco']);
    $preco_custo = str_replace(',', '.', $_POST['preco_custo']);
    $preco = floatval($preco);
    $preco_custo = floatval($preco_custo);

    $disponivel = $_POST['disponivel'];
    
    try {
        $sqlCheck = "SELECT id FROM produtos WHERE produto = ? AND id != ?";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$produto, $id ?? 0]);
        
        if ($stmtCheck->rowCount() > 0) {
            echo "<script>
                alert('Erro: Já existe um produto cadastrado com o nome \"$produto\".');
                window.history.back();
            </script>";
            exit;
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE produtos SET produto = ?, descricao = ?, preco = ?, preco_custo = ?, disponivel = ? WHERE id = ?");
            $stmt->execute([$produto, $descricao, $preco, $preco_custo, $disponivel, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO produtos (produto, descricao, preco, preco_custo, disponivel) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$produto, $descricao, $preco, $preco_custo, $disponivel]);
        }
        
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        die("Erro ao salvar produto: " . $e->getMessage());
    }
}
?>