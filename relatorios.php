<?php
session_start();
$activePage = 'relatorios';
require_once 'includes/header.php';
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
}

$dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
$dataFim    = $_GET['data_fim'] ?? date('Y-m-d');

$isDiaUnico = ($dataInicio === $dataFim);

try {
    $labels = [];
    $valores = [];

    if ($isDiaUnico) {
        $sqlGrafico = "
            SELECT HOUR(hora) as hora_venda, SUM(total) as total_hora 
            FROM vendas 
            WHERE data = ?
            GROUP BY HOUR(hora)
            ORDER BY hora_venda ASC
        ";
        $stmt = $pdo->prepare($sqlGrafico);
        $stmt->execute([$dataInicio]);
        $dadosDb = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        for ($h = 0; $h < 24; $h++) {
            $labels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ":00";
            $valores[] = $dadosDb[$h] ?? 0;
        }
        $tituloGrafico = "Vendas por Horário (Dia " . date('d/m/Y', strtotime($dataInicio)) . ")";

    } else {
        $sqlGrafico = "
            SELECT data, SUM(total) as total_diario 
            FROM vendas 
            WHERE data BETWEEN ? AND ?
            GROUP BY data
            ORDER BY data ASC
        ";
        $stmt = $pdo->prepare($sqlGrafico);
        $stmt->execute([$dataInicio, $dataFim]);
        $dadosGrafico = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dadosGrafico as $dado) {
            $labels[] = date('d/m', strtotime($dado['data']));
            $valores[] = $dado['total_diario'];
        }
        $tituloGrafico = "Evolução de Vendas (" . date('d/m', strtotime($dataInicio)) . " a " . date('d/m', strtotime($dataFim)) . ")";
    }
    
} catch (Exception $e) {
    $erroGrafico = "Erro ao carregar gráfico: " . $e->getMessage();
}

try {
    $sqlRelatorio = "
        SELECT 
            p.produto,
            SUM(vp.quantidade) as qtd_vendida,
            SUM(vp.quantidade * vp.preco_unitario) as receita_total,
            SUM(vp.quantidade * IFNULL(p.preco_custo, 0)) as custo_total
        FROM venda_produtos vp
        JOIN produtos p ON vp.produto_id = p.id
        JOIN vendas v ON vp.venda_id = v.id
        WHERE v.data BETWEEN ? AND ?
        GROUP BY p.id, p.produto
        ORDER BY receita_total DESC
    ";
    
    $stmt = $pdo->prepare($sqlRelatorio);
    $stmt->execute([$dataInicio, $dataFim]);
    $relatorioProdutos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalVendasGeral = 0;
    $totalLucroGeral = 0;
    $produtoMaisVendido = '-';
    $maiorQtd = 0;

    foreach ($relatorioProdutos as $r) {
        $totalVendasGeral += $r['receita_total'];
        $lucroItem = $r['receita_total'] - $r['custo_total'];
        $totalLucroGeral += $lucroItem;

        if ($r['qtd_vendida'] > $maiorQtd) {
            $maiorQtd = $r['qtd_vendida'];
            $produtoMaisVendido = $r['produto'];
        }
    }

} catch (Exception $e) {
    $relatorioProdutos = [];
    $erroTabela = "Erro: " . $e->getMessage();
}
?>

<style>
    @media print {
        .sidebar, #filter-form, .no-print, button {
            display: none !important;
        }
        
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 !important;
            max-width: 100% !important;
        }

        .shadow, .bg-white {
            box-shadow: none !important;
            border: none !important;
        }

        #chart-container {
            width: 95% !important;
            height: 300px !important;
            margin: 0 auto !important;
            page-break-inside: avoid;
            display: block;
        }
        
        canvas {
            max-width: 100% !important;
            height: 100% !important;
        }
        
        body {
            background-color: white;
            -webkit-print-color-adjust: exact;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex flex-col md:flex-row justify-between items-center no-print">
        <h2 class="text-2xl font-bold text-red-800">Relatórios Gerenciais</h2>
        <button onclick="window.print()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg mt-4 md:mt-0">
            <i class="fas fa-print mr-2"></i> Imprimir
        </button>
    </header>

    <div class="bg-white rounded-lg shadow p-4 mb-6" id="filter-form">
        <form method="GET" class="flex flex-col md:flex-row items-end gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Data Início</label>
                <input type="date" name="data_inicio" value="<?= $dataInicio ?>" 
                       class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Data Fim</label>
                <input type="date" name="data_fim" value="<?= $dataFim ?>" 
                       class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-red-800 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-filter mr-2"></i> Filtrar
                </button>
                <a href="?data_inicio=<?= date('Y-m-d') ?>&data_fim=<?= date('Y-m-d') ?>" 
                   class="bg-yellow-400 hover:bg-yellow-300 text-red-900 font-bold py-2 px-4 rounded">
                   Hoje
                </a>
            </div>
        </form>
    </div>

    <div class="mb-4 text-center hidden print:block">
        <h1 class="text-2xl font-bold">Relatório de Vendas</h1>
        <p>Período: <?= date('d/m/Y', strtotime($dataInicio)) ?> até <?= date('d/m/Y', strtotime($dataFim)) ?></p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                    <i class="fas fa-coins text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Faturamento</p>
                    <p class="text-2xl font-bold text-gray-800">R$ <?= number_format($totalVendasGeral, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                    <i class="fas fa-chart-pie text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Lucro Estimado</p>
                    <p class="text-2xl font-bold text-gray-800">R$ <?= number_format($totalLucroGeral, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-400">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                    <i class="fas fa-crown text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Mais Vendido</p>
                    <p class="text-xl font-bold text-gray-800 truncate w-32" title="<?= $produtoMaisVendido ?>">
                        <?= $produtoMaisVendido ?>
                    </p>
                    <p class="text-xs text-gray-400"><?= $maiorQtd ?> unidades</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6 avoid-break">
        <h3 class="text-lg font-bold text-gray-700 mb-4"><?= $tituloGrafico ?></h3>
        <?php if (isset($erroGrafico)): ?>
            <p class="text-red-500 bg-red-100 p-3 rounded"><?= $erroGrafico ?></p>
        <?php else: ?>
            <div class="relative h-80 w-full" id="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-lg shadow p-6 avoid-break">
        <h3 class="text-lg font-bold text-gray-700 mb-4">Detalhamento por Produto</h3>
        <?php if (isset($erroTabela)): ?>
            <p class="text-red-500 bg-red-100 p-3 rounded"><?= $erroTabela ?></p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden text-sm">
                <thead class="bg-red-800 text-yellow-400">
                    <tr>
                        <th class="py-2 px-4 text-left">Produto</th>
                        <th class="py-2 px-4 text-center">Qtd.</th>
                        <th class="py-2 px-4 text-right">Receita</th>
                        <th class="py-2 px-4 text-right">Custo</th>
                        <th class="py-2 px-4 text-right">Lucro</th>
                        <th class="py-2 px-4 text-center">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($relatorioProdutos as $item): 
                        $lucro = $item['receita_total'] - $item['custo_total'];
                        $margem = ($item['receita_total'] > 0) ? ($lucro / $item['receita_total']) * 100 : 0;
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 font-medium"><?= htmlspecialchars($item['produto']) ?></td>
                        <td class="py-3 px-4 text-center"><?= $item['qtd_vendida'] ?></td>
                        <td class="py-3 px-4 text-right text-green-700 font-bold">
                            <?= number_format($item['receita_total'], 2, ',', '.') ?>
                        </td>
                        <td class="py-3 px-4 text-right text-gray-500">
                            <?= number_format($item['custo_total'], 2, ',', '.') ?>
                        </td>
                        <td class="py-3 px-4 text-right font-bold <?= $lucro >= 0 ? 'text-blue-600' : 'text-red-600' ?>">
                            <?= number_format($lucro, 2, ',', '.') ?>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <?= number_format($margem, 1, ',', '.') ?>%
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const ctx = document.getElementById('salesChart');
    const labels = <?php echo json_encode($labels); ?>;
    const dataValues = <?php echo json_encode($valores); ?>;
    const labelSet = "<?php echo $isDiaUnico ? 'Vendas por Hora (R$)' : 'Vendas Diárias (R$)'; ?>";

    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: labelSet,
                    data: dataValues,
                    borderColor: '#991b1b',
                    backgroundColor: 'rgba(153, 27, 27, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#fbbf24',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
</script>

<?php require_once 'includes/footer.php'; ?>