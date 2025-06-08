<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/db.php';
require_once '../includes/header.php';

// Buscar produtos ativos
$stmt = $pdo->query("SELECT id, produto, preco FROM produtos WHERE disponivel = 1");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $data    = date('Y-m-d');
        $hora    = date('H:i:s');
        $cliente = $_POST['cliente'];
        $total   = 0;

        $produtos_selecionados = $_POST['produtos'] ?? [];

        // Calcular total
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

            $preco  = floatval($produtos[$key]['preco']);
            $total += $preco * $qtd;
        }

        // Inserir venda COM hora
        $stmt_venda = $pdo->prepare(
            "INSERT INTO vendas (data, hora, cliente, total)
             VALUES (?, ?, ?, ?)"
        );
        $stmt_venda->execute([$data, $hora, $cliente, $total]);
        $venda_id = $pdo->lastInsertId();

        // Inserir itens da venda e reduzir estoque
        $stmt_produto = $pdo->prepare(
            "INSERT INTO venda_produtos (venda_id, produto_id, quantidade, preco_unitario)
             VALUES (?, ?, ?, ?)"
        );
        $stmt_estoque = $pdo->prepare(
            "UPDATE estoque 
             SET quantidade = quantidade - :qtd 
             WHERE item = (
               SELECT produto FROM produtos WHERE id = :prod_id
             )"
        );

        foreach ($produtos_selecionados as $produto_id) {
            $qtd = intval($_POST['quantidade'][$produto_id]);
            $key = array_search($produto_id, array_column($produtos, 'id'));
            $preco = floatval($produtos[$key]['preco']);

            // Inserir registro na tabela venda_produtos
            $stmt_produto->execute([$venda_id, $produto_id, $qtd, $preco]);

            // Reduzir quantidade no estoque (por nome de item)
            $stmt_estoque->execute([
                ':qtd'     => $qtd,
                ':prod_id' => $produto_id
            ]);
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
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="produto-select">Produtos</label>
                <select id="produto-select"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                    <option value="">Selecione um produto</option>
                    <?php foreach ($produtos as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['produto']) ?> – R$ <?= number_format($p['preco'], 2, ',', '.') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="itens-selecionados" class="mb-4">
                <h3 class="text-lg font-semibold mb-2">Itens Adicionados</h3>
                <ul id="lista-itens" class="space-y-2"></ul>
            </div>

            <div id="input-hidden-container"></div>

            <div class="flex justify-end">
                <a href="index.php" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg">Cancelar</a>
                <button type="submit" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg">Salvar Venda</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const select = document.getElementById('produto-select');
  const lista = document.getElementById('lista-itens');
  const container = document.getElementById('input-hidden-container');
  const produtos = <?= json_encode($produtos) ?>;
  const itens = {};

  function atualizar() {
    container.innerHTML = '';
    Object.keys(itens).forEach(id => {
      const inp = document.createElement('input');
      inp.type = 'hidden'; inp.name = 'produtos[]'; inp.value = id;
      container.appendChild(inp);
    });
  }

  function addItem(id) {
    if (itens[id]) return;
    const prod = produtos.find(p => p.id == id);
    if (!prod) return;
    itens[id]=true;
    const li = document.createElement('li');
    li.setAttribute('data-id', id);
    li.className = 'flex items-center justify-between bg-gray-50 p-3 rounded-lg';
    li.innerHTML = `
      <span class="font-medium">${prod.produto}</span>
      <div class="flex items-center space-x-2">
        <input type="number" name="quantidade[${id}]" min="1" value="1" class="w-16 px-2 py-1 border rounded focus:outline-none focus:ring-2 focus:ring-red-800">
        <button type="button" class="text-red-600 hover:text-red-800" onclick="rmItem(${id})"><i class="fas fa-trash"></i></button>
      </div>
    `;
    lista.appendChild(li);
    atualizar();
  }

  window.rmItem=id=>{delete itens[id]; lista.querySelector(`li[data-id="${id}"]`).remove(); atualizar();};
  select.addEventListener('change',()=>{if(select.value){addItem(select.value); select.value='';}});
});
</script>

<?php require_once '../includes/footer.php'; ?>
