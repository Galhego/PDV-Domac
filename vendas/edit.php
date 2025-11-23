<?php
session_start();
require_once '../config/db.php';

// --- LÓGICA DE SALVAMENTO (Backend) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $venda_id = $_POST['venda_id'];
    $cliente = $_POST['cliente'];
    $novos_produtos = $_POST['produtos'] ?? [];
    $quantidades = $_POST['quantidade'] ?? [];
    
    try {
        $pdo->beginTransaction();

        // 1. Recuperar itens antigos para devolver ao estoque (Desfaz a venda anterior)
        $stmtAntigos = $pdo->prepare("
            SELECT produto_id, quantidade FROM venda_produtos WHERE venda_id = ?
        ");
        $stmtAntigos->execute([$venda_id]);
        $itensAntigos = $stmtAntigos->fetchAll(PDO::FETCH_ASSOC);

        $stmtDevolve = $pdo->prepare("UPDATE estoque SET quantidade = quantidade + ? WHERE item = (SELECT produto FROM produtos WHERE id = ?)");
        foreach ($itensAntigos as $item) {
            $stmtDevolve->execute([$item['quantidade'], $item['produto_id']]);
        }

        // 2. Limpar itens antigos da venda
        $pdo->prepare("DELETE FROM venda_produtos WHERE venda_id = ?")->execute([$venda_id]);

        // 3. Processar Novos Itens (Refaz a venda)
        $total = 0;
        $stmtInsertItem = $pdo->prepare("INSERT INTO venda_produtos (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmtBaixaEstoque = $pdo->prepare("UPDATE estoque SET quantidade = quantidade - ? WHERE item = (SELECT produto FROM produtos WHERE id = ?)");
        
        // Busca preços atuais para recalcular
        if (!empty($novos_produtos)) {
            $ids = implode(',', array_map('intval', $novos_produtos));
            $stmtPrecos = $pdo->query("SELECT id, preco FROM produtos WHERE id IN ($ids)");
            $precosDb = $stmtPrecos->fetchAll(PDO::FETCH_KEY_PAIR); // [id => preco]

            foreach ($novos_produtos as $pId) {
                $qtd = intval($quantidades[$pId]);
                $preco = floatval($precosDb[$pId]);
                
                $total += $preco * $qtd;

                // Insere novo item
                $stmtInsertItem->execute([$venda_id, $pId, $qtd, $preco]);
                // Baixa estoque novamente
                $stmtBaixaEstoque->execute([$qtd, $pId]);
            }
        }

        // 4. Atualiza cabeçalho da venda
        $stmtUpdateVenda = $pdo->prepare("UPDATE vendas SET cliente = ?, total = ? WHERE id = ?");
        $stmtUpdateVenda->execute([$cliente, $total, $venda_id]);

        $pdo->commit();
        $_SESSION['flash'] = ['type'=>'success', 'title'=>'Atualizado!', 'message'=>'Venda editada com sucesso.'];
        header('Location: index.php');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $erro = $e->getMessage();
    }
}

// --- CARREGAMENTO DA TELA ---
require_once '../includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }

// Dados da Venda
$stmt = $pdo->prepare("SELECT * FROM vendas WHERE id = ?");
$stmt->execute([$id]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

// Itens da Venda (para preencher o JS)
$stmtItens = $pdo->prepare("
    SELECT vp.produto_id, vp.quantidade, p.produto, p.preco 
    FROM venda_produtos vp 
    JOIN produtos p ON vp.produto_id = p.id 
    WHERE vp.venda_id = ?
");
$stmtItens->execute([$id]);
$itensAtuais = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

// Lista de Produtos para o Select
$listaProdutos = $pdo->query("SELECT id, produto, preco FROM produtos WHERE disponivel = 1 ORDER BY produto ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="text-2xl font-bold text-red-800">Editar Venda #<?= $venda['id'] ?></h2>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl mx-auto">
        <form method="POST">
            <input type="hidden" name="venda_id" value="<?= $venda['id'] ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Cliente</label>
                <input type="text" name="cliente" value="<?= htmlspecialchars($venda['cliente']) ?>" required
                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-800 outline-none">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Adicionar/Remover Produtos</label>
                <div class="flex gap-2">
                    <select id="produto-select" class="w-full px-3 py-2 border rounded-lg outline-none">
                        <option value="">Selecione...</option>
                        <?php foreach ($listaProdutos as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['produto']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btn-add" class="bg-yellow-400 text-red-900 font-bold px-4 rounded-lg hover:bg-yellow-300">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg border mb-6">
                <h3 class="text-sm font-bold text-gray-500 uppercase mb-3">Itens na Venda</h3>
                <ul id="lista-itens" class="space-y-2"></ul>
                <p id="empty-msg" class="text-center text-gray-400 text-sm py-2 hidden">Lista vazia.</p>
            </div>

            <div id="inputs-container"></div>

            <div class="flex justify-end gap-2">
                <a href="index.php" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg hover:bg-gray-400">Cancelar</a>
                <button type="submit" class="bg-red-800 text-yellow-400 font-bold py-2 px-4 rounded-lg hover:bg-red-700">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Dados vindos do PHP
    const todosProdutos = <?= json_encode($listaProdutos) ?>;
    const itensIniciais = <?= json_encode($itensAtuais) ?>;
    
    const select = document.getElementById('produto-select');
    const lista = document.getElementById('lista-itens');
    const container = document.getElementById('inputs-container');
    const btnAdd = document.getElementById('btn-add');
    
    let carrinho = {}; // Objeto { id: quantidade }

    // 1. Função para desenhar a lista
    function render() {
        lista.innerHTML = '';
        container.innerHTML = '';
        const ids = Object.keys(carrinho);

        if (ids.length === 0) document.getElementById('empty-msg').classList.remove('hidden');
        else document.getElementById('empty-msg').classList.add('hidden');

        ids.forEach(id => {
            const prod = todosProdutos.find(p => p.id == id);
            if(!prod) return;

            // Cria elemento visual (LI)
            const li = document.createElement('li');
            li.className = 'flex justify-between items-center bg-white p-2 rounded shadow-sm border';
            li.innerHTML = `
                <span class="font-medium text-gray-700">${prod.produto}</span>
                <div class="flex items-center gap-2">
                    <input type="number" min="1" value="${carrinho[id]}" 
                           onchange="updateQtd(${id}, this.value)"
                           class="w-16 border rounded p-1 text-center font-bold">
                    <button type="button" onclick="removeItem(${id})" class="text-red-500 hover:text-red-700 px-2">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            lista.appendChild(li);

            // Cria Inputs Hidden para envio POST
            const inputId = document.createElement('input');
            inputId.type = 'hidden'; inputId.name = 'produtos[]'; inputId.value = id;
            
            const inputQtd = document.createElement('input');
            inputQtd.type = 'hidden'; inputQtd.name = `quantidade[${id}]`; inputQtd.value = carrinho[id];

            container.appendChild(inputId);
            container.appendChild(inputQtd);
        });
    }

    // 2. Funções Globais
    window.updateQtd = (id, val) => {
        if(val < 1) val = 1;
        carrinho[id] = val;
        render(); // Re-renderiza os inputs hidden
    };

    window.removeItem = (id) => {
        delete carrinho[id];
        render();
    };

    btnAdd.addEventListener('click', () => {
        const id = select.value;
        if(!id) return;
        if(carrinho[id]) {
            alert('Produto já está na lista.');
            return;
        }
        carrinho[id] = 1;
        select.value = '';
        render();
    });

    // 3. Inicialização (Carregar itens que já existiam)
    itensIniciais.forEach(item => {
        carrinho[item.produto_id] = item.quantidade;
    });
    render();
});
</script>
<?php require_once '../includes/footer.php'; ?>