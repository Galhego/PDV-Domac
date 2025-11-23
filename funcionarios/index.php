<?php
session_start();
$activePage = 'funcionarios';
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM funcionarios ORDER BY nome ASC");
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Equipe</h2>
        <a href="add.php" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-user-plus mr-2"></i> Novo Funcionário
        </a>
    </header>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Nome</th>
                        <th class="py-3 px-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Cargo</th>
                        <th class="py-3 px-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Telefone</th>
                        <th class="py-3 px-4 text-right text-sm font-bold text-gray-700 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($funcionarios as $f): ?>
                    <tr class="hover:bg-red-50 transition duration-150">
                        <td class="py-4 px-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-red-100 rounded-full flex items-center justify-center text-red-800">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($f['nome']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-700">
                            <span class="px-2 py-1 rounded bg-gray-100 text-gray-800 text-xs font-bold">
                                <?= htmlspecialchars($f['cargo']) ?>
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm text-gray-500">
                            <i class="fas fa-phone-alt mr-2 text-gray-400"></i>
                            <?= htmlspecialchars($f['telefone'] ?? '—') ?>
                        </td>
                        <td class="py-4 px-4 text-right whitespace-nowrap text-sm font-medium">
                            <a href="edit.php?id=<?= $f['id'] ?>" class="text-yellow-600 hover:text-yellow-900 mr-3 transition" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?= $f['id'] ?>" class="text-red-400 hover:text-red-800 transition" 
                               onclick="return confirm('Tem certeza que deseja remover este funcionário?')" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if(empty($funcionarios)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-8 text-gray-500 italic">
                            Nenhum funcionário cadastrado.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>