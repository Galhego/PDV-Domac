<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_nome = trim($_POST['item']);
    $unidade = trim($_POST['unidade']);
    
    try {
        $stmt = $pdo->prepare("UPDATE estoque SET item = ?, unidade = ? WHERE id = ?");
        $stmt->execute([$item_nome, $unidade, $id]);
        
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao atualizar: " . $e->getMessage();
    }
}

$stmt = $pdo->prepare("SELECT * FROM estoque WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) die("Item não encontrado.");

require_once '../includes/header.php';
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="text-2xl font-bold text-red-800">Editar Item</h2>
    </header>

    <?php if (isset($erro)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2 font-bold">Nome do Item</label>
                <input type="text" name="item" value="<?= htmlspecialchars($item['item']) ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-gray-700 mb-2 font-bold">Quantidade Atual</label>
                    <input type="text" value="<?= $item['quantidade'] ?>" disabled
                        class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-lg text-gray-500 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Para alterar quantidade, use a tela de Entrada ou Vendas.</p>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2 font-bold">Unidade</label>
                    <select name="unidade" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 bg-white">
                        <?php 
                        $unidades = ['un', 'kg', 'g', 'l', 'ml', 'pct', 'cx'];
                        foreach($unidades as $u): 
                        ?>
                            <option value="<?= $u ?>" <?= $item['unidade'] == $u ? 'selected' : '' ?>><?= $u ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="index.php" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg">Cancelar</a>
                <button type="submit" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg">Salvar</button>
            </div>
        </form>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>