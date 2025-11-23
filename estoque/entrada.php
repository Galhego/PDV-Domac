<?php
session_start();
require_once '../config/db.php';

// --- PROCESSAR ENTRADA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtos = $_POST['produtos'] ?? [];
    $quantidades = $_POST['quantidade'] ?? [];

    try {
        $pdo->beginTransaction();
        
        $stmtUpdate = $pdo->prepare("UPDATE estoque SET quantidade = quantidade + ? WHERE id = ?");
        // O trigger do banco já cuida do histórico!

        $cont = 0;
        foreach ($produtos as $id) {
            $qtd = intval($quantidades[$id]);
            if ($qtd > 0) {
                // Precisamos pegar o ID do estoque baseado no ID do produto?
                // No seu banco as tabelas são separadas (produtos vs estoque), 
                // mas o estoque tem 'item' (nome). Vamos assumir que o select envia o ID da tabela ESTOQUE.
                
                $stmtUpdate->execute([$qtd, $id]);
                $cont++;
            }
        }

        $pdo->commit();
        $_SESSION['flash'] = ['type'=>'success', 'title'=>'Estoque Atualizado', 'message'=>"$cont itens tiveram o estoque reposto."];
        header('Location: index.php');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $erro = $e->getMessage();
    }
}

require_once '../includes/header.php';

// Buscar itens do estoque
$stmt = $pdo->query("SELECT id, item, quantidade FROM estoque ORDER BY item ASC");
$itensEstoque = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="text-2xl font-bold text-red-800">Entrada de Estoque (Múltiplos)</h2>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl mx-auto">
        <form method="POST">
            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Selecione o Item para adicionar quantidade</label>
                <div class="flex gap-2">
                    <select id="estoque-select" class="w-full px-3 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-red-800">
                        <option value="">Escolha um item...</option>
                        <?php foreach ($itensEstoque as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['item']) ?> (Atual: <?= $item['quantidade'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btn-add-stock" class="bg-green-600 text-white font-bold px-4 rounded-lg hover:bg-green-700">
                        <i class="fas fa-plus"></i> Adicionar
                    </button>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg border mb-6">
                <h3 class="text-sm font-bold text-gray-500 uppercase mb-3">Itens a serem repostos</h3>
                <ul id="stock-list" class="space-y-2">
                    <li id="msg-vazio" class="text-center text-gray-400 italic">Nenhum item selecionado.</li>
                </ul>
            </div>

            <div id="hidden-inputs"></div>

            <div class="flex justify-end gap-2">
                <a href="index.php" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg hover:bg-gray-400">Cancelar</a>
                <button type="submit" class="bg-red-800 text-yellow-400 font-bold py-2 px-4 rounded-lg hover:bg-red-700">
                    <i class="fas fa-check mr-2"></i> Confirmar Entrada
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const itensDb = <?= json_encode($itensEstoque) ?>;
    const select = document.getElementById('estoque-select');
    const list = document.getElementById('stock-list');
    const hiddenDiv = document.getElementById('hidden-inputs');
    const emptyMsg = document.getElementById('msg-vazio');
    
    let selection = {}; // { id: qtd_a_adicionar }

    function renderStock() {
        list.innerHTML = '';
        hiddenDiv.innerHTML = '';
        const ids = Object.keys(selection);

        if (ids.length === 0) {
            list.appendChild(emptyMsg);
            return;
        }

        ids.forEach(id => {
            const item = itensDb.find(i => i.id == id);
            
            const li = document.createElement('li');
            li.className = 'flex justify-between items-center bg-white p-3 rounded border shadow-sm';
            li.innerHTML = `
                <div>
                    <span class="font-bold text-gray-700 block">${item.item}</span>
                    <span class="text-xs text-gray-500">Estoque Atual: ${item.quantidade}</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-gray-100 rounded px-2">
                        <span class="text-xs text-gray-500 mr-2">Entrada:</span>
                        <input type="number" min="1" value="${selection[id]}" 
                               onchange="updateStock(${id}, this.value)"
                               class="w-20 bg-transparent border-none outline-none font-bold text-green-700 text-right">
                    </div>
                    <button type="button" onclick="removeStock(${id})" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            list.appendChild(li);

            // Inputs ocultos
            const inId = document.createElement('input'); inId.type='hidden'; inId.name='produtos[]'; inId.value=id;
            const inQtd = document.createElement('input'); inQtd.type='hidden'; inQtd.name=`quantidade[${id}]`; inQtd.value=selection[id];
            hiddenDiv.appendChild(inId);
            hiddenDiv.appendChild(inQtd);
        });
    }

    window.updateStock = (id, val) => { selection[id] = val; renderStock(); };
    window.removeStock = (id) => { delete selection[id]; renderStock(); };

    document.getElementById('btn-add-stock').addEventListener('click', () => {
        const id = select.value;
        if(!id) return;
        if(selection[id]) { alert('Item já listado!'); return; }
        
        selection[id] = 10; // Sugere 10 unidades por padrão
        renderStock();
        select.value = '';
    });
});
</script>
<?php require_once '../includes/footer.php'; ?>