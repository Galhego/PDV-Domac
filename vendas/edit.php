<?php
require_once '../config/db.php';
require_once '../includes/header.php';

$venda_id = $_GET['id'];

// Buscar dados da venda
$stmt = $pdo->prepare("SELECT v.id, v.data, v.cliente, vp.produto_id, vp.quantidade, p.produto, p.preco 
                      FROM vendas v
                      LEFT JOIN venda_produtos vp ON v.id = vp.venda_id
                      LEFT JOIN produtos p ON vp.produto_id = p.id
                      WHERE v.id = ?");
$stmt->execute([$venda_id]);
$venda_itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$venda_itens) {
    die("Venda não encontrada");
}

// Buscar todos os produtos
$stmt = $pdo->query("SELECT id, produto, preco FROM produtos WHERE disponivel = 1");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar dados para edição
$venda = [
    'id' => $venda_itens[0]['id'],
    'data' => $venda_itens[0]['data'],
    'cliente' => $venda_itens[0]['cliente'],
    'itens' => []
];

foreach ($venda_itens as $item) {
    if ($item['produto_id']) {
        $venda['itens'][$item['produto_id']] = [
            'produto' => $item['produto'],
            'quantidade' => $item['quantidade'],
            'preco' => $item['preco']
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar transação
        $pdo->beginTransaction();
        
        // Atualizar cliente
        $stmt = $pdo->prepare("UPDATE vendas SET cliente = ? WHERE id = ?");
        $stmt->execute([$_POST['cliente'], $venda_id]);
        
        // Deletar itens antigos
        $stmt = $pdo->prepare("DELETE FROM venda_produtos WHERE venda_id = ?");
        $stmt->execute([$venda_id]);
        
        // Inserir novos itens
        $stmt = $pdo->prepare("INSERT INTO venda_produtos (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        
        foreach ($_POST['produtos'] as $produto_id) {
            $quantidade = $_POST['quantidade'][$produto_id];
            $preco = $produtos[array_search($produto_id, array_column($produtos, 'id'))]['preco'];
            $stmt->execute([$venda_id, $produto_id, $quantidade, $preco]);
        }
        
        // Commit da transação
        $pdo->commit();
        
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erro ao atualizar venda: " . $e->getMessage();
    }
}
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Editar Venda #<?= $venda['id'] ?></h2>
    </header>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="cliente">Cliente</label>
                <input type="text" name="cliente" value="<?= $venda['cliente'] ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="produtos">Produtos</label>
                <select name="produtos[]" multiple required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                    <?php foreach ($produtos as $produto): ?>
                    <option value="<?= $produto['id'] ?>" <?= isset($venda['itens'][$produto['id']]) ? 'selected' : '' ?>>
                        <?= $produto['produto'] ?> - R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Script para campos dinâmicos de quantidade -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const select = document.querySelector('select[name="produtos[]"]');
                    let initialValues = {
                        <?php foreach ($venda['itens'] as $id => $item): ?>
                        "<?= $id ?>": <?= $item['quantidade'] ?>,
                        <?php endforeach; ?>
                    };
                    
                    function updateQuantities() {
                        const selected = Array.from(select.selectedOptions).map(opt => opt.value);
                        const container = document.getElementById('quantidades-container');
                        
                        // Limpar container
                        container.innerHTML = '';
                        
                        // Adicionar campos de quantidade
                        selected.forEach(id => {
                            const div = document.createElement('div');
                            div.className = 'mb-2';
                            div.innerHTML = `
                                <label class="block text-gray-700 mb-1" for="quantidade_${id}">
                                    Quantidade para <?= $produtos[array_search($id, array_column($produtos, 'id'))]['produto'] ?>
                                </label>
                                <input type="number" name="quantidade[${id}]" min="1" required
                                    value="${initialValues[id] || 1}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                            `;
                            container.appendChild(div);
                        });
                    }
                    
                    // Atualizar ao carregar
                    updateQuantities();
                    
                    // Atualizar ao mudar seleção
                    select.addEventListener('change', updateQuantities);
                });
            </script>
            
            <div id="quantidades-container" class="mb-4"></div>
            
            <div class="flex justify-end">
                <a href="index.php" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg">
                    Cancelar
                </a>
                <button type="submit" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>