<?php
session_start();
$activePage = 'produtos';
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    die("Produto não encontrado.");
}
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Editar Produto</h2>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
        <form action="save.php" method="POST">
            <input type="hidden" name="id" value="<?= $produto['id'] ?>">

            <div class="mb-4">
                <label class="block text-gray-700 mb-2 font-bold" for="produto">Nome do Produto</label>
                <input type="text" name="produto" id="produto" value="<?= htmlspecialchars($produto['produto']) ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2 font-bold" for="descricao">Descrição</label>
                <textarea name="descricao" id="descricao" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800"><?= htmlspecialchars($produto['descricao']) ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 mb-2 font-bold" for="preco">Preço de Venda (R$)</label>
                    <input type="number" step="0.01" name="preco" id="preco" value="<?= $produto['preco'] ?>" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2 font-bold" for="preco_custo">Preço de Custo (R$)</label>
                    <input type="number" step="0.01" name="preco_custo" id="preco_custo" 
                           value="<?= isset($produto['preco_custo']) ? $produto['preco_custo'] : '0.00' ?>" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 bg-yellow-50">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 mb-2 font-bold" for="disponivel">Disponibilidade</label>
                <select name="disponivel" id="disponivel"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                    <option value="1" <?= $produto['disponivel'] == 1 ? 'selected' : '' ?>>Disponível</option>
                    <option value="0" <?= $produto['disponivel'] == 0 ? 'selected' : '' ?>>Indisponível</option>
                </select>
            </div>

            <div class="flex justify-end">
                <a href="index.php" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg">
                    Cancelar
                </a>
                <button type="submit" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>