<?php
session_start();
$activePage = 'estoque'; // Define a página ativa
require_once '../includes/header.php';
require_once '../config/db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Redireciona para login
    exit; // Para a execução
}

$stmt = $pdo->query("SELECT * FROM estoque");
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Controle de Estoque</h2>
        <a href="history.php" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i> Ver Histórico
        </a>
        <a href="add.php" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i> Adicionar Item
        </a>
    </header>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-red-800 text-yellow-400">
                    <tr>
                        <th class="py-3 px-4 text-left">ID</th>
                        <th class="py-3 px-4 text-left">Item</th>
                        <th class="py-3 px-4 text-left">Quantidade</th>
                        <th class="py-3 px-4 text-left">Unidade</th>
                        <th class="py-3 px-4 text-left">Validade</th>
                        <th class="py-3 px-4 text-left">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($itens as $item): ?>
                    <tr class="table-row-hover">
                        <td class="py-4 px-4"><?= $item['id'] ?></td>
                        <td class="py-4 px-4"><?= $item['item'] ?></td>
                        <td class="py-4 px-4"><?= $item['quantidade'] ?></td>
                        <td class="py-4 px-4"><?= $item['unidade'] ?></td>
                        <td class="py-4 px-4"><?= date('d/m/Y', strtotime($item['validade'])) ?></td>
                        <td class="py-4 px-4">
                            <a href="edit.php?id=<?= $item['id'] ?>" class="text-yellow-600 hover:text-yellow-800 mr-2">
                                <i class="fas fa-edit"></i>
                            </a>

                            <!-- Novo botão -->
                            <a href="add_quantity.php?id=<?= $item['id'] ?>" class="text-green-600 hover:text-green-800 mr-2" title="Acescentar Quantidade">
                                <i class="fas fa-plus-circle"></i>
                            </a>

                            <a href="delete.php?id=<?= $item['id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Tem certeza que deseja excluir este item do estoque?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>