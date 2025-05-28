<?php
session_start();
$activePage = 'funcionarios'; // Define a página ativa
require_once '../config/db.php';
require_once '../includes/header.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Redireciona para login
    exit; // Para a execução
}

$stmt = $pdo->query("SELECT * FROM funcionarios");
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Equipe de Funcionários</h2>
        <a href="add.php" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i> Adicionar Funcionário
        </a>
    </header>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-red-800 text-yellow-400">
                    <tr>
                        <th class="py-3 px-4 text-left">ID</th>
                        <th class="py-3 px-4 text-left">Nome</th>
                        <th class="py-3 px-4 text-left">Cargo</th>
                        <th class="py-3 px-4 text-left">Telefone</th>
                        <th class="py-3 px-4 text-left">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($funcionarios as $funcionario): ?>
                    <tr class="table-row-hover">
                        <td class="py-4 px-4"><?= $funcionario['id'] ?></td>
                        <td class="py-4 px-4"><?= $funcionario['nome'] ?></td>
                        <td class="py-4 px-4"><?= $funcionario['cargo'] ?></td>
                        <td class="py-4 px-4"><?= $funcionario['telefone'] ?></td>
                        <td class="py-4 px-4">
                            <a href="edit.php?id=<?= $funcionario['id'] ?>" class="text-yellow-600 hover:text-yellow-800 mr-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?= $funcionario['id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Tem certeza que deseja excluir este funcionário?')">
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