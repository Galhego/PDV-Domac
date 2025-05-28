<?php
require_once '../config/db.php';
require_once '../includes/header.php';

// Buscar produtos ativos
$stmt = $pdo->query("SELECT id, produto, preco FROM produtos WHERE disponivel = 1");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar transação
        $pdo->beginTransaction();
        
        // Inserir venda
        $data = date('Y-m-d');
        $cliente = $_POST['cliente'];
        $total = 0;

        // Calcular total com foreach 
        foreach ($_POST['produtos'] as $produto_id) {
            $key = array_search($produto_id, array_column($produtos, 'id'));
            if ($key !== false) {
                $total += $produtos[$key]['preco'] * $_POST['quantidade'][$produto_id];
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO vendas (data, cliente, total) VALUES (?, ?, ?)");
        $stmt->execute([$data, $cliente, $total]);
        $venda_id = $pdo->lastInsertId();
        
        // Inserir produtos da venda
        $stmt = $pdo->prepare("INSERT INTO venda_produtos (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        
        foreach ($_POST['produtos'] as $produto_id) {
            $key = array_search($produto_id, array_column($produtos, 'id'));
            if ($key !== false) {
                $preco = $produtos[$key]['preco'];
                $stmt->execute([$venda_id, $produto_id, $_POST['quantidade'][$produto_id], $preco]);
            }
        }
        
        // Commit da transação
        $pdo->commit();
        
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erro ao salvar venda: " . $e->getMessage();
    }
}
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Nova Venda</h2>
    </header>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="cliente">Cliente</label>
                <input type="text" name="cliente" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="produtos">Produtos</label>
                <select name="produtos[]" multiple required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                    <?php foreach ($produtos as $produto): ?>
                    <option value="<?= $produto['id'] ?>">
                        <?= $produto['produto'] ?> - R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Campos dinâmicos de quantidade -->
            <div id="quantidades-container" class="mb-4"></div>
            
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
        const select = document.querySelector('select[name="produtos[]"]');
        const container = document.getElementById('quantidades-container');

        function updateQuantities() {
            const selected = Array.from(select.selectedOptions).map(opt => opt.value);
            container.innerHTML = '';

            selected.forEach(id => {
                const div = document.createElement('div');
                div.className = 'mb-2';

                div.innerHTML = `
                    <label class="block text-gray-700 mb-1" for="quantidade_${id}">
                        Quantidade para <?= $produtos[array_search($id, array_column($produtos, 'id'))]['produto'] ?>
                    </label>
                    <input type="number" name="quantidade[${id}]" min="1" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                `;
                container.appendChild(div);
            });
        }

        select.addEventListener('change', updateQuantities);
        updateQuantities(); // Carregar campos iniciais
    });
</script>

<?php require_once '../includes/footer.php'; ?>