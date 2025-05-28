<?php
session_start();
$activePage = 'vendas'; // Define a página ativa
require_once '../config/db.php';
require_once '../includes/header.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Redireciona para login
    exit; // Para a execução
}

// Buscar vendas com produtos
$stmt = $pdo->query("
    SELECT v.id, v.data, v.cliente, 
           GROUP_CONCAT(p.produto SEPARATOR ', ') AS produtos,
           SUM(vp.quantidade * vp.preco_unitario) AS total
    FROM vendas v
    LEFT JOIN venda_produtos vp ON v.id = vp.venda_id
    LEFT JOIN produtos p ON vp.produto_id = p.id
    GROUP BY v.id
");
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Vendas</h2>
        <!-- Resto do header -->
    </header>

    <section class="section-content">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-red-800">Registro de Vendas</h3>
                <a href="add.php" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Adicionar Venda
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg overflow-hidden">
                    <!-- Estrutura da tabela igual ao original -->
                    <?php foreach ($vendas as $venda): ?>
                    <tr class="table-row-hover">
                        <td class="py-4 px-4"><?= $venda['id'] ?></td>
                        <td class="py-4 px-4"><?= date('d/m/Y', strtotime($venda['data'])) ?></td>
                        <td class="py-4 px-4"><?= $venda['cliente'] ?></td>
                        <td class="py-4 px-4"><?= $venda['produtos'] ?></td>
                        <td class="py-4 px-4">R$ <?= number_format($venda['total'], 2, ',', '.') ?></td>
                        <td class="py-4 px-4">
                            <a href="edit.php?id=<?= $venda['id'] ?>" class="text-yellow-600 hover:text-yellow-800 mr-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?= $venda['id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Tem certeza que deseja excluir esta venda?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </section>
</div>

<?php require_once '../includes/footer.php'; ?>