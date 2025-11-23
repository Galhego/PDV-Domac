<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/db.php';

// --- LÓGICA DE SALVAMENTO (MOVIDA PARA O TOPO) ---
// Deve ficar ANTES de incluir o header.php para evitar o erro de "Headers already sent"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $data    = date('Y-m-d');
        $hora    = date('H:i:s');
        $cliente = $_POST['cliente'];
        $total   = 0;

        $produtos_selecionados = $_POST['produtos'] ?? [];
        
        // Se nenhum produto foi selecionado
        if (empty($produtos_selecionados)) {
             throw new Exception("Selecione pelo menos um produto.");
        }

        // 1. Busca informações dos produtos para calcular total e validar
        // (Fazemos isso aqui dentro para garantir integridade antes de salvar)
        $placeholders = implode(',', array_fill(0, count($produtos_selecionados), '?'));
        $stmtBusca = $pdo->prepare("SELECT id, produto, preco FROM produtos WHERE id IN ($placeholders)");
        $stmtBusca->execute($produtos_selecionados);
        $dadosProdutos = $stmtBusca->fetchAll(PDO::FETCH_ASSOC);
        
        // Indexar produtos por ID para acesso rápido
        $prodMap = [];
        foreach($dadosProdutos as $p) {
            $prodMap[$p['id']] = $p;
        }

        // 2. Calcular Total Geral
        foreach ($produtos_selecionados as $produto_id) {
            $qtd = $_POST['quantidade'][$produto_id] ?? 0;
            if (!is_numeric($qtd) || $qtd <= 0) {
                throw new Exception("Quantidade inválida para o produto.");
            }
            
            if (!isset($prodMap[$produto_id])) {
                throw new Exception("Produto ID $produto_id não encontrado no banco.");
            }

            $preco  = floatval($prodMap[$produto_id]['preco']);
            $total += $preco * intval($qtd);
        }

        // 3. Inserir Venda
        $stmt_venda = $pdo->prepare(
            "INSERT INTO vendas (data, hora, cliente, total)
             VALUES (?, ?, ?, ?)"
        );
        $stmt_venda->execute([$data, $hora, $cliente, $total]);
        $venda_id = $pdo->lastInsertId();

        // 4. Inserir Itens e Baixar Estoque
        $stmt_produto = $pdo->prepare(
            "INSERT INTO venda_produtos (venda_id, produto_id, quantidade, preco_unitario)
             VALUES (?, ?, ?, ?)"
        );
        
        $stmt_estoque = $pdo->prepare(
            "UPDATE estoque 
             SET quantidade = quantidade - :qtd 
             WHERE item = (SELECT produto FROM produtos WHERE id = :prod_id)"
        );

        foreach ($produtos_selecionados as $produto_id) {
            $qtd = intval($_POST['quantidade'][$produto_id]);
            $preco = floatval($prodMap[$produto_id]['preco']);

            // Salva na tabela de relacionamento
            $stmt_produto->execute([$venda_id, $produto_id, $qtd, $preco]);

            // Abate do estoque
            $stmt_estoque->execute([
                ':qtd'     => $qtd,
                ':prod_id' => $produto_id
            ]);
        }

        $pdo->commit();
        header('Location: index.php');
        exit; // Importante parar o script aqui

    } catch (Exception $e) {
        $pdo->rollBack();
        $erro = $e->getMessage(); // Guarda o erro para exibir no HTML abaixo
    }
}

// --- AGORA SIM INCLUÍMOS O HTML (HEADER) ---
require_once '../includes/header.php';

// Buscar produtos ativos para preencher o <select>
$stmt = $pdo->query("SELECT id, produto, preco FROM produtos WHERE disponivel = 1 ORDER BY produto ASC");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Nova Venda</h2>
    </header>

    <?php if (isset($erro)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 relative">
            <strong class="font-bold">Erro!</strong>
            <span class="block sm:inline"><?= htmlspecialchars($erro) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl mx-auto">
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="cliente">Cliente</label>
                <input type="text" name="cliente" required
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="produto-select">Adicionar Produtos</label>
                <select id="produto-select"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                    <option value="">Selecione um produto...</option>
                    <?php foreach ($produtos as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['produto']) ?> – R$ <?= number_format($p['preco'], 2, ',', '.') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="itens-selecionados" class="mb-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h3 class="text-lg font-semibold mb-3 text-gray-700">Carrinho</h3>
                <ul id="lista-itens" class="space-y-2">
                    <li id="empty-msg" class="text-gray-400 text-sm italic">Nenhum item adicionado ainda.</li>
                </ul>
            </div>

            <div id="input-hidden-container"></div>

            <div class="flex justify-end mt-6">
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
  const emptyMsg = document.getElementById('empty-msg');
  
  // Passa os dados do PHP para o JS
  const produtosDb = <?= json_encode($produtos) ?>;
  const itensNoCarrinho = {}; // Objeto para controlar IDs já adicionados

  function atualizarInputsOcultos() {
    container.innerHTML = '';
    const ids = Object.keys(itensNoCarrinho);
    
    if (ids.length > 0) {
        if(emptyMsg) emptyMsg.style.display = 'none';
    } else {
        if(emptyMsg) emptyMsg.style.display = 'block';
    }

    ids.forEach(id => {
      const inp = document.createElement('input');
      inp.type = 'hidden'; 
      inp.name = 'produtos[]'; 
      inp.value = id;
      container.appendChild(inp);
    });
  }

  function addItem(id) {
    if (itensNoCarrinho[id]) {
        alert('Este item já está na lista!');
        return;
    }
    
    const prod = produtosDb.find(p => p.id == id);
    if (!prod) return;

    itensNoCarrinho[id] = true;

    const li = document.createElement('li');
    li.setAttribute('data-id', id);
    li.className = 'flex items-center justify-between bg-white p-3 rounded shadow-sm border border-gray-100';
    
    li.innerHTML = `
      <div class="flex flex-col">
          <span class="font-medium text-gray-800">${prod.produto}</span>
          <span class="text-xs text-gray-500">R$ ${parseFloat(prod.preco).toFixed(2).replace('.', ',')} un.</span>
      </div>
      <div class="flex items-center space-x-3">
        <div class="flex items-center border rounded">
            <span class="px-2 text-gray-500 text-sm bg-gray-100">Qtd</span>
            <input type="number" name="quantidade[${id}]" min="1" value="1" class="w-16 px-2 py-1 focus:outline-none text-center font-bold">
        </div>
        <button type="button" class="text-red-500 hover:text-red-700 p-2" onclick="rmItem(${id})">
            <i class="fas fa-trash"></i>
        </button>
      </div>
    `;
    
    lista.appendChild(li);
    atualizarInputsOcultos();
  }

  window.rmItem = (id) => {
    delete itensNoCarrinho[id];
    const item = lista.querySelector(`li[data-id="${id}"]`);
    if (item) item.remove();
    atualizarInputsOcultos();
  };

  select.addEventListener('change', () => {
      if(select.value){
          addItem(select.value); 
          select.value='';
      }
  });
});
</script>

<?php require_once '../includes/footer.php'; ?>