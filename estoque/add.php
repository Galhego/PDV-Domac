<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item = trim($_POST['item']);
    $quantidade = intval($_POST['quantidade']);
    $unidade = trim($_POST['unidade']);
    
    // Removida a captura de validade

    try {
        $stmtCheck = $pdo->prepare("SELECT id FROM estoque WHERE item = ?");
        $stmtCheck->execute([$item]);
        
        if ($stmtCheck->rowCount() > 0) {
            $erro = "Já existe um item com este nome no estoque. Use a opção de 'Adicionar Quantidade' na listagem.";
        } else {
            // SQL ajustado sem a coluna validade
            $stmt = $pdo->prepare("INSERT INTO estoque (item, quantidade, unidade) VALUES (?, ?, ?)");
            $stmt->execute([$item, $quantidade, $unidade]);

            header('Location: index.php');
            exit;
        }
    } catch (PDOException $e) {
        $erro = "Erro ao cadastrar item: " . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Novo Item de Estoque</h2>
    </header>

    <?php if (isset($erro)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2 font-bold" for="item">Nome do Item</label>
                <input type="text" name="item" id="item" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800"
                    placeholder="Ex: Farinha de Trigo">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-gray-700 mb-2 font-bold" for="quantidade">Quantidade Inicial</label>
                    <input type="number" name="quantidade" id="quantidade" min="0" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2 font-bold" for="unidade">Unidade de Medida</label>
                    <select name="unidade" id="unidade" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 bg-white">
                        <option value="un">Unidade (un)</option>
                        <option value="kg">Quilograma (kg)</option>
                        <option value="g">Grama (g)</option>
                        <option value="l">Litro (l)</option>
                        <option value="ml">Mililitro (ml)</option>
                        <option value="pct">Pacote (pct)</option>
                        <option value="cx">Caixa (cx)</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="index.php" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                    Cancelar
                </a>
                <button type="submit" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg shadow-md transition duration-200 flex items-center">
                    <i class="fas fa-save mr-2"></i> Cadastrar Item
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>