<?php
session_start();
$activePage = 'produtos';
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM produtos ORDER BY produto ASC");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Produtos</h2>
    </header>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-red-800">Gerir Produtos</h3>
            <a href="add.php" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i> Novo Produto
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Produto</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Descrição</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Custo (R$)</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Venda (R$)</th>
                        <th class="py-3 px-4 text-center text-sm font-semibold text-gray-700">Status</th>
                        <th class="py-3 px-4 text-right text-sm font-semibold text-gray-700">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($produtos as $p): 
                         $custo = isset($p['preco_custo']) ? $p['preco_custo'] : 0;
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-4 px-4 font-medium"><?= htmlspecialchars($p['produto']) ?></td>
                        <td class="py-4 px-4 text-gray-500 text-sm"><?= htmlspecialchars($p['descricao']) ?></td>
                        
                        <td class="py-4 px-4 text-gray-500">
                            <?= number_format($custo, 2, ',', '.') ?>
                        </td>

                        <td class="py-4 px-4 font-bold text-gray-800">
                            <?= number_format($p['preco'], 2, ',', '.') ?>
                        </td>

                        <td class="py-4 px-4 text-center">
                            <?php if ($p['disponivel']): ?>
                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">Ativo</span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-1 rounded">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <a href="edit.php?id=<?= $p['id'] ?>" class="text-yellow-600 hover:text-yellow-800 mr-3" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?= $p['id'] ?>" class="text-red-600 hover:text-red-800"
                               onclick="return confirm('Excluir este produto?')" title="Excluir">
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