<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM estoque WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item não encontrado no banco de dados.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantidade_add = intval($_POST['quantidade']);

    if ($quantidade_add > 0) {
        try {
            $pdo->beginTransaction();

            $stmtUpdate = $pdo->prepare("UPDATE estoque SET quantidade = quantidade + ? WHERE id = ?");
            $stmtUpdate->execute([$quantidade_add, $id]);

            $pdo->commit();
            
            header('Location: index.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = "Erro ao atualizar: " . $e->getMessage();
        }
    } else {
        $erro = "A quantidade deve ser maior que zero.";
    }
}

require_once '../includes/header.php';
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Entrada de Estoque</h2>
    </header>

    <?php if (isset($erro)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6 max-w-lg mx-auto">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">

            <div class="mb-6 text-center">
                <div class="inline-block p-4 rounded-full bg-red-100 text-red-800 mb-3">
                    <i class="fas fa-box-open text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($item['item']) ?></h3>
                <p class="text-gray-500">Estoque Atual: <span class="font-bold"><?= $item['quantidade'] ?></span> <?= htmlspecialchars($item['unidade'] ?? 'un') ?></p>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2" for="quantidade">
                    Quantidade a Adicionar
                </label>
                <div class="relative">
                    <input type="number" name="quantidade" id="quantidade" min="1" required autofocus
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-red-800 focus:ring-1 focus:ring-red-800 text-lg font-bold text-gray-700"
                        placeholder="0">
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                        <i class="fas fa-plus"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Digite apenas o valor que chegou (ex: 10).</p>
            </div>

            <div class="flex justify-end gap-3">
                <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-bold transition duration-200">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-red-800 text-yellow-400 rounded-lg hover:bg-red-700 font-bold shadow-md transition duration-200 flex items-center">
                    <i class="fas fa-save mr-2"></i> Confirmar Entrada
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>