<?php
session_start();
$activePage = 'vendas'; 
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->query("
    SELECT 
        v.id, 
        v.data,
        v.hora,
        v.cliente, 
        GROUP_CONCAT(CONCAT(p.produto, ' (', vp.quantidade, ')') SEPARATOR ' | ') AS produtos,
        SUM(vp.quantidade * vp.preco_unitario) AS total_calculado,
        v.total as total_final
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
        <h2 class="text-2xl font-bold text-red-800">Registro de Vendas</h2>
        <a href="add.php" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-cash-register mr-2"></i> Nova Venda
        </a>
    </header>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">#ID</th>
                        <th class="py-3 px-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Data / Hora</th>
                        <th class="py-3 px-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Cliente</th>
                        <th class="py-3 px-4 text-right text-sm font-bold text-gray-700 uppercase tracking-wider">Total</th>
                        <th class="py-3 px-4 text-center text-sm font-bold text-gray-700 uppercase tracking-wider">Itens</th>
                        <th class="py-3 px-4 text-right text-sm font-bold text-gray-700 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($vendas as $venda): ?>
                        <?php
                            $data_fmt = date('d/m/Y', strtotime($venda['data']));
                            $hora_fmt = date('H:i',  strtotime($venda['hora']));
                            $totalShow = number_format($venda['total_final'], 2, ',', '.');
                            
                            $jsVenda = [
                                'id'       => $venda['id'],
                                'produtos' => explode(' | ', $venda['produtos'] ?: ''),
                                'total'    => $totalShow,
                                'cliente'  => $venda['cliente'],
                                'data'     => $data_fmt,
                                'hora'     => $hora_fmt
                            ];
                        ?>
                    <tr class="hover:bg-red-50 transition duration-150 group">
                        <td class="py-4 px-4 font-bold text-gray-600">#<?= $venda['id'] ?></td>
                        
                        <td class="py-4 px-4 text-sm text-gray-600">
                            <div class="font-bold text-gray-800"><?= $data_fmt ?></div>
                            <div class="text-xs text-gray-400"><?= $hora_fmt ?></div>
                        </td>
                        
                        <td class="py-4 px-4 font-medium text-gray-800">
                            <?= htmlspecialchars($venda['cliente']) ?>
                        </td>
                        
                        <td class="py-4 px-4 text-right font-bold text-green-700">
                            R$ <?= $totalShow ?>
                        </td>

                        <td class="py-4 px-4 text-center">
                            <button onclick='showProducts(<?= json_encode($jsVenda, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)' 
                                    class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-full text-xs font-bold transition">
                                <i class="fas fa-eye mr-1"></i> Ver Detalhes
                            </button>
                        </td>

                        <td class="py-4 px-4 text-right whitespace-nowrap">
                            <a href="edit.php?id=<?= $venda['id'] ?>" class="text-yellow-600 hover:text-yellow-800 mr-3 transition" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?= $venda['id'] ?>" class="text-red-400 hover:text-red-800 transition"
                               onclick="return confirm('Tem certeza que deseja excluir esta venda?')" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($vendas)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500 italic">
                            Nenhuma venda registrada.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-2xl transform scale-100 transition-transform">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h3 class="text-xl font-bold text-red-800">Detalhes da Venda</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="modalHeader" class="mb-4 text-sm text-gray-600 bg-gray-50 p-3 rounded">
            </div>

        <h4 class="font-bold text-gray-700 mb-2 text-sm uppercase">Itens Comprados</h4>
        <div id="productList" class="space-y-2 max-h-60 overflow-y-auto pr-2 text-sm text-gray-800">
            </div>

        <div class="mt-6 flex justify-end">
            <button onclick="closeModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg transition">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
    function showProducts(venda) {
        const productList = document.getElementById('productList');
        const modalHeader = document.getElementById('modalHeader');
        
        productList.innerHTML = '';

        modalHeader.innerHTML = `
            <p><strong>Cliente:</strong> ${venda.cliente}</p>
            <p><strong>Data:</strong> ${venda.data} às ${venda.hora}</p>
            <p class="mt-1 text-lg text-green-700"><strong>Total: R$ ${venda.total}</strong></p>
        `;

        if (venda.produtos.length && venda.produtos[0] !== "") {
            venda.produtos.forEach(produto => {
                const div = document.createElement('div');
                div.className = 'flex items-center p-2 bg-gray-50 rounded border border-gray-100';
                div.innerHTML = `<i class="fas fa-box-open text-red-300 mr-2"></i> ${produto}`;
                productList.appendChild(div);
            });
        } else {
            const div = document.createElement('div');
            div.className = 'text-gray-400 italic text-center py-4';
            div.textContent = 'Nenhum produto detalhado.';
            productList.appendChild(div);
        }

        document.getElementById('productModal').classList.remove('hidden');
        document.getElementById('productModal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('productModal').classList.add('hidden');
        document.getElementById('productModal').classList.remove('flex');
    }
    
    document.getElementById('productModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>

<?php require_once '../includes/footer.php'; ?>