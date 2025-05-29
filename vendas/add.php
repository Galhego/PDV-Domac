<?php
require_once '../config/db.php';
require_once '../includes/header.php';

// Buscar produtos ativos
$stmt = $pdo->query("SELECT id, produto, preco FROM produtos WHERE disponivel = 1");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $data = date('Y-m-d');
        $hora = date('H:i:s');
        $cliente = $_POST['cliente'];
        $total = 0;

        $produtos_selecionados = $_POST['produtos'] ?? [];

        // Calcular total com base nas quantidades
        foreach ($produtos_selecionados as $produto_id) {
            $qtd = $_POST['quantidade'][$produto_id] ?? 0;

            if (!is_numeric($qtd) || $qtd <= 0) {
                throw new Exception("Quantidade inválida para o produto ID: $produto_id");
            }
            $qtd = intval($qtd);

            $key = array_search($produto_id, array_column($produtos, 'id'));
            if ($key === false) {
                throw new Exception("Produto não encontrado: $produto_id");
            }

            $preco = floatval($produtos[$key]['preco']);
            $total += $preco * $qtd;
        }

        // Inserir venda
        $stmt_venda = $pdo->prepare("INSERT INTO vendas (data, hora, cliente, total) VALUES (?, ?, ?, ?)");
        $stmt_venda->execute([$data, $hora, $cliente, $total]);
        $venda_id = $pdo->lastInsertId();

        // Inserir produtos da venda
        $stmt_produto = $pdo->prepare("
            INSERT INTO venda_produtos (venda_id, produto_id, quantidade, preco_unitario) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($produtos_selecionados as $produto_id) {
            $qtd = $_POST['quantidade'][$produto_id] ?? 0;

            if (!is_numeric($qtd) || $qtd <= 0) continue;

            $qtd = intval($qtd);
            $key = array_search($produto_id, array_column($produtos, 'id'));

            if ($key === false) continue;

            $preco = floatval($produtos[$key]['preco']);
            $stmt_produto->execute([$venda_id, $produto_id, $qtd, $preco]);
        }

        $pdo->commit();
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Erro ao salvar venda: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Nova Venda</h2>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl mx-auto">
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="cliente">Cliente</label>
                <input type="text" name="cliente" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="produtos">Produtos</label>
                <select id="produto-select"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                    <option value="">Selecione um produto</option>
                    <?php foreach ($produtos as $produto): ?>
                        <option value="<?= $produto['id'] ?>">
                            <?= htmlspecialchars($produto['produto']) ?> - R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Lista de Itens Selecionados -->
            <div id="itens-selecionados" class="mb-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Itens Adicionados</h3>
                <ul id="lista-itens" class="space-y-2"></ul>
            </div>

            <input type="hidden" name="produtos[]" id="produtos-input">

            <div class="flex justify-end">
                <a href="index.php" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg">
                    Cancelar
                </a>
                <button type="submit" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg">
                    Salvar Venda
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('produto-select');
        const listaItens = document.getElementById('lista-itens');

        const itensSelecionados = {};
        const produtos = <?= json_encode($produtos) ?>; // Produtos em JSON

        function atualizarInputs() {
            const produtosArray = Object.keys(itensSelecionados);
            document.getElementById('produtos-input').value = produtosArray.join(',');
        }

        function adicionarItem(id) {
            if (itensSelecionados[id]) return;

            const produto = produtos.find(p => p.id == id); // Busca o produto correto
            if (!produto) return;

            itensSelecionados[id] = 1;

            const li = document.createElement('li');
            li.className = 'flex items-center justify-between bg-gray-50 p-3 rounded-lg';
            li.setAttribute('data-id', id);

            li.innerHTML = `
                <span class="font-medium">${produto.produto}</span>
                <div class="flex items-center space-x-2">
                    <input type="number" name="quantidade[${id}]" min="1" value="1"
                        class="w-16 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-red-800">
                    <button type="button" onclick="removerItem(this, ${id})"
                        class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;

            listaItens.appendChild(li);
            atualizarInputs();
        }

        window.removerItem = function(button, id) {
            delete itensSelecionados[id];
            button.closest('li').remove();
            atualizarInputs();
        }

        select.addEventListener('change', function () {
            const id = this.value;
            if (id) {
                adicionarItem(id);
                this.value = ''; // Limpar seleção
            }
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>