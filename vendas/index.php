<?php
session_start();
$activePage = 'vendas'; // Define a página ativa
require_once '../config/db.php';
require_once '../includes/header.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Buscar vendas com produtos (ordenado por ID decrescente)
$stmt = $pdo->query("
    SELECT 
        v.id, 
        v.data, 
        v.hora,
        v.cliente, 
        GROUP_CONCAT(CONCAT(p.produto, ' (', vp.quantidade, ')') SEPARATOR ' | ') AS produtos,
        SUM(vp.quantidade * vp.preco_unitario) AS total
    FROM vendas v
    LEFT JOIN venda_produtos vp ON v.id = vp.venda_id
    LEFT JOIN produtos p ON vp.produto_id = p.id
    GROUP BY v.id
    ORDER BY v.id DESC
");
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Vendas</h2>
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
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">ID</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Data</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Horário</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Cliente</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Total</th>
                            <th class="py-3 px-4 text-right text-sm font-semibold text-gray-700">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($vendas as $venda): ?>
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick='showProducts(<?= json_encode([
                                'id' => $venda['id'],
                                'produtos' => explode(' | ', $venda['produtos']),
                                'total' => $venda['total'],
                                'cliente' => $venda['cliente'],
                                'data' => date('d/m/Y', strtotime($venda['data'])),
                                'hora' => date('H:i', strtotime($venda['hora']))
                            ]) ?>)'>
                            <td class="py-4 px-4"><?= $venda['id'] ?></td>
                            <td class="py-4 px-4"><?= date('d/m/Y', strtotime($venda['data'])) ?></td>
                            <td class="py-4 px-4"><?= date('H:i', strtotime($venda['hora'])) ?></td>
                            <td class="py-4 px-4"><?= htmlspecialchars($venda['cliente']) ?></td>
                            <td class="py-4 px-4">R$ <?= number_format($venda['total'], 2, ',', '.') ?></td>
                            <td class="py-4 px-4 text-right">
                                <a href="edit.php?id=<?= $venda['id'] ?>" class="text-yellow-600 hover:text-yellow-800 mr-2">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?= $venda['id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Tem certeza que deseja excluir esta venda?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Modal para mostrar produtos -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-lg">
        <h3 class="text-lg font-semibold text-red-800 mb-4">Produtos da Venda</h3>
        <div id="productList" class="space-y-2"></div>
        <div class="mt-4 flex justify-end">
            <button onclick="closeModal()" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
    function showProducts(venda) {
        const productList = document.getElementById('productList');
        productList.innerHTML = '';
        
        // Exibir cliente e data
        const info = document.createElement('div');
        info.className = 'text-gray-700 font-semibold mb-2';
        info.textContent = `Cliente: ${venda.cliente} - ${venda.data} ${venda.hora}`;
        productList.appendChild(info);

        // Exibir produtos
        if (Array.isArray(venda.produtos)) {
            venda.produtos.forEach(produto => {
                const div = document.createElement('div');
                div.className = 'text-gray-700';
                div.textContent = produto;
                productList.appendChild(div);
            });
        } else {
            const div = document.createElement('div');
            div.className = 'text-gray-500';
            div.textContent = 'Nenhum produto registrado.';
            productList.appendChild(div);
        }
        
        document.getElementById('productModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('productModal').classList.add('hidden');
    }
</script>

<?php require_once '../includes/footer.php'; ?>