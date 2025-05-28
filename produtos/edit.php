<?php
require_once '../config/db.php';
require_once '../includes/header.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    die("Produto não encontrado");
}
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Editar Produto</h2>
    </header>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="POST" action="save.php">
            <input type="hidden" name="id" value="<?= $produto['id'] ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="produto">Nome do Produto</label>
                <input type="text" name="produto" value="<?= $produto['produto'] ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="descricao">Descrição</label>
                <textarea name="descricao" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800"><?= $produto['descricao'] ?></textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="preco">Preço</label>
                <input type="text" name="preco" value="<?= number_format($produto['preco'], 2, ',', '.') ?>" required pattern="\d+(\,\d{2})?"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="disponivel">Disponível</label>
                <select name="disponivel"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                    <option value="1" <?= $produto['disponivel'] ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= !$produto['disponivel'] ? 'selected' : '' ?>>Não</option>
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