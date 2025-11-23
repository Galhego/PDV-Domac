<?php
session_start();
$activePage = 'estoque';
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM estoque ORDER BY item ASC");
$estoque = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Controle de Estoque</h2>
        <div class="flex gap-2">
            <a href="add.php" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg flex items-center shadow-sm">
                <i class="fas fa-plus mr-2"></i> Novo Item
            </a>
        </div>
    </header>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Item</th>
                        <th class="py-3 px-4 text-center text-sm font-bold text-gray-700 uppercase tracking-wider">Qtd.</th>
                        <th class="py-3 px-4 text-center text-sm font-bold text-gray-700 uppercase tracking-wider">Un.</th>
                        <th class="py-3 px-4 text-right text-sm font-bold text-gray-700 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($estoque as $item): ?>
                    <tr class="hover:bg-red-50 transition duration-150 group">
                        <td class="py-4 px-4 font-medium text-gray-800">
                            <?= htmlspecialchars($item['item']) ?>
                        </td>
                        
                        <td class="py-4 px-4 text-center">
                            <span class="font-bold text-lg <?= $item['quantidade'] < 5 ? 'text-red-600' : 'text-gray-700' ?>">
                                <?= $item['quantidade'] ?>
                            </span>
                        </td>
                        
                        <td class="py-4 px-4 text-center text-gray-500 text-sm">
                            <?= htmlspecialchars($item['unidade']) ?>
                        </td>

                        <td class="py-4 px-4 text-right whitespace-nowrap">
                            <a href="add_quantity.php?id=<?= $item['id'] ?>" 
                               class="inline-flex items-center justify-center w-8 h-8 bg-green-100 text-green-700 rounded-full hover:bg-green-600 hover:text-white mr-2 transition"
                               title="Adicionar Entrada">
                                <i class="fas fa-plus"></i>
                            </a>

                            <a href="edit.php?id=<?= $item['id'] ?>" class="text-yellow-600 hover:text-yellow-800 mr-3 transition" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <a href="delete.php?id=<?= $item['id'] ?>" class="text-red-400 hover:text-red-800 transition"
                               onclick="return confirm('Tem certeza que deseja excluir este item permanentemente?')" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($estoque)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-8 text-gray-500 italic">
                                Nenhum item cadastrado no estoque.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>