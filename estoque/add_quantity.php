<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Item não encontrado.");
}

$stmt = $pdo->prepare("SELECT * FROM estoque WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item não encontrado.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Acescentar Quantidade - Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss @2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-red-800 mb-4">Acescentar Quantidade</h2>
        <form method="POST" action="save_quantity.php">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="item">Item</label>
                <input type="text" value="<?= htmlspecialchars($item['item']) ?>" disabled
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="quantidade">Quantidade a Acescentar</label>
                <input type="number" name="quantidade" min="1" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>
            
            <div class="flex justify-between">
                <a href="index.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg">
                    Cancelar
                </a>
                <button type="submit" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg">
                    Acescentar
                </button>
            </div>
        </form>
    </div>
</body>
</html>