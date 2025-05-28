<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verificar se é uma edição ou inserção
        $id = $_POST['id'] ?? null;

        if ($id) {
            // Atualizar item existente
            $item = $_POST['item'] ?? '';
            $quantidade = $_POST['quantidade'] ?? 0;
            $unidade = $_POST['unidade'] ?? '';
            $validade = $_POST['validade'] ?? null;

            // Validação básica
            if (empty($item) || empty($unidade) || empty($validade) || $quantidade <= 0) {
                throw new Exception("Todos os campos são obrigatórios e a quantidade deve ser maior que zero.");
            }

            // Atualizar no estoque
            $stmt = $pdo->prepare("UPDATE estoque SET item = ?, quantidade = ?, unidade = ?, validade = ? WHERE id = ?");
            $stmt->execute([$item, $quantidade, $unidade, $validade, $id]);
        } else {
            // Inserir novos itens (como antes)
            $produto_ids = $_POST['produtos'] ?? [];
            $quantidade = $_POST['quantidade'];
            $unidade = $_POST['unidade'];
            $validade = $_POST['validade'];

            // Validação básica
            if (empty($produto_ids) || empty($quantidade) || empty($unidade) || empty($validade)) {
                throw new Exception("Todos os campos são obrigatórios.");
            }

            // Buscar os nomes dos produtos com base nos IDs
            $placeholders = implode(',', array_fill(0, count($produto_ids), '?'));
            $stmt = $pdo->prepare("SELECT id, produto FROM produtos WHERE id IN ($placeholders)");
            $stmt->execute($produto_ids);
            $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($produtos) !== count($produto_ids)) {
                throw new Exception("Alguns produtos selecionados não foram encontrados.");
            }

            // Inserir no estoque
            $stmt = $pdo->prepare("INSERT INTO estoque (item, quantidade, unidade, validade) VALUES (?, ?, ?, ?)");

            foreach ($produtos as $p) {
                $stmt->execute([
                    $p['produto'],
                    $quantidade,
                    $unidade,
                    $validade
                ]);
            }
        }

        header('Location: index.php');
        exit;

    } catch (Exception $e) {
        echo "Erro ao salvar estoque: " . $e->getMessage();
    }
}
?>