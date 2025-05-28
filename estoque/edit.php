<?php
session_start();
$activePage = 'estoque';
require_once '../includes/header.php';
require_once '../config/db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Redireciona para login
    exit; // Para a execução
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM estoque WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item não encontrado");
}
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Editar Item do Estoque</h2>
    </header>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="POST" action="save.php">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="item">Item</label>
                <input type="text" name="item" value="<?= $item['item'] ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="quantidade">Quantidade</label>
                <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" required min="1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="unidade">Unidade</label>
                <select name="unidade" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                    <option value="kg" <?= $item['unidade'] == 'kg' ? 'selected' : '' ?>>kg</option>
                    <option value="g" <?= $item['unidade'] == 'g' ? 'selected' : '' ?>>g</option>
                    <option value="l" <?= $item['unidade'] == 'l' ? 'selected' : '' ?>>l</option>
                    <option value="ml" <?= $item['unidade'] == 'ml' ? 'selected' : '' ?>>ml</option>
                    <option value="un" <?= $item['unidade'] == 'un' ? 'selected' : '' ?>>un</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="validade">Data de Validade</label>
                <input type="date" name="validade" value="<?= $item['validade'] ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
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