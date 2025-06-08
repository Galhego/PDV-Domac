<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/db.php';
require_once '../includes/header.php';

// Pegar ID da venda
$venda_id = $_GET['id'] ?? null;
if (!$venda_id) {
    header('Location: index.php');
    exit;
}

// Buscar venda existente
$stmt = $pdo->prepare("SELECT id, cliente, data, hora FROM vendas WHERE id = ?");
$stmt->execute([$venda_id]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar itens antigos da venda
$stmt2 = $pdo->prepare("SELECT produto_id, quantidade FROM venda_produtos WHERE venda_id = ?");
$stmt2->execute([$venda_id]);
$itens_anteriores = [];
$venda['itens'] = [];
while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    $itens_anteriores[$row['produto_id']] = $row['quantidade'];
    $venda['itens'][$row['produto_id']] = ['quantidade' => $row['quantidade']];
}

// Buscar produtos ativos para select
$stmt3 = $pdo->query("SELECT id, produto, preco FROM produtos WHERE disponivel = 1");
$produtos = $stmt3->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Novas quantidades selecionadas
        $novos = $_POST['quantidade'] ?? [];

        // Ajustar estoque: para cada produto, calcular delta
        $todos_ids = array_unique(array_merge(array_keys($itens_anteriores), array_keys($novos)));
        $stmt_estoque = $pdo->prepare(
            "UPDATE estoque
             SET quantidade = quantidade - :delta
             WHERE item = (
               SELECT produto FROM produtos WHERE id = :pid
             )"
        );

        foreach ($todos_ids as $pid) {
            $ant = $itens_anteriores[$pid] ?? 0;
            $nov = intval($novos[$pid] ?? 0);
            $delta = $nov - $ant; // >0 retira, <0 devolve
            if ($delta !== 0) {
                $stmt_estoque->execute([':delta' => $delta, ':pid' => $pid]);
            }
        }

        // Recalcular total
        $cliente = $_POST['cliente'];
        $total = 0;
        foreach ($novos as $pid => $qtd) {
            $key = array_search((int)$pid, array_column($produtos, 'id'));
            if ($key !== false) {
                $total += $produtos[$key]['preco'] * intval($qtd);
            }
        }

        // Atualizar tabela vendas
        $stmt_up = $pdo->prepare("UPDATE vendas SET cliente = ?, total = ? WHERE id = ?");
        $stmt_up->execute([$cliente, $total, $venda_id]);

        // Remover itens antigos e inserir novos
        $pdo->prepare("DELETE FROM venda_produtos WHERE venda_id = ?")->execute([$venda_id]);
        $stmt_ins = $pdo->prepare(
            "INSERT INTO venda_produtos (venda_id, produto_id, quantidade, preco_unitario)
             VALUES (?, ?, ?, ?)"
        );
        foreach ($novos as $pid => $qtd) {
            $key = array_search((int)$pid, array_column($produtos, 'id'));
            if ($key !== false) {
                $preco = floatval($produtos[$key]['preco']);
                $stmt_ins->execute([$venda_id, $pid, intval($qtd), $preco]);
            }
        }

        $pdo->commit();
        header('Location: index.php');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>";
        echo "Erro ao atualizar venda: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Editar Venda #<?= $venda['id'] ?></h2>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl mx-auto">
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="cliente">Cliente</label>
                <input type="text" name="cliente" value="<?= htmlspecialchars($venda['cliente']) ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="produtos">Produtos</label>
                <select id="produto-select" multiple required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                    <?php foreach ($produtos as $produto): ?>
                        <option value="<?= $produto['id'] ?>"
                            <?= isset($venda['itens'][$produto['id']]) ? 'selected' : '' ?> >
                            <?= htmlspecialchars($produto['produto']) ?> - R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Container quantidades -->
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('produto-select');
    const container = document.getElementById('quantidades-container');
    const oldQ = <?= json_encode($itens_anteriores) ?>;
    
    function updateQuantities() {
        const selected = Array.from(select.selectedOptions).map(o => o.value);
        container.innerHTML = '';
        selected.forEach(id => {
            const qtdOld = oldQ[id] || 1;
            const text  = document.querySelector(`#produto-select option[value='${id}']`).textContent;
            const div   = document.createElement('div');
            div.className = 'mb-2';
            div.innerHTML = `
                <label class="block text-gray-700 mb-1" for="quantidade_${id}">Quantidade para ${text}</label>
                <input type="number" id="quantidade_${id}" name="quantidade[${id}]" min="1" value="${qtdOld}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            `;
            container.appendChild(div);
        });
    }
    updateQuantities();
    select.addEventListener('change', updateQuantities);
});
</script>

<?php require_once '../includes/footer.php'; ?>
