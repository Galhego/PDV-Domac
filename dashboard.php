<?php
session_start();
$activePage = 'dashboard';
require_once 'includes/header.php';
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 1. Totais de Hoje
$hoje = date('Y-m-d');
$sqlVendas = "SELECT COUNT(*) as qtd, SUM(total) as valor FROM vendas WHERE data = ?";
$stmt = $pdo->prepare($sqlVendas);
$stmt->execute([$hoje]);
$vendasHoje = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Produtos com Estoque Baixo (Menor que 5 unidades)
$sqlBaixo = "SELECT item, quantidade FROM estoque WHERE quantidade < 5 ORDER BY quantidade ASC LIMIT 5";
$baixoEstoque = $pdo->query($sqlBaixo)->fetchAll(PDO::FETCH_ASSOC);

// 3. Últimas 5 Vendas
$sqlUltimas = "SELECT id, hora, total, cliente FROM vendas ORDER BY id DESC LIMIT 5";
$ultimasVendas = $pdo->query($sqlUltimas)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="text-2xl font-bold text-red-800">Visão Geral - Hoje (<?= date('d/m') ?>)</h2>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-600 flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-semibold">Vendido Hoje</p>
                <h3 class="text-3xl font-bold text-gray-800">R$ <?= number_format($vendasHoje['valor'] ?? 0, 2, ',', '.') ?></h3>
            </div>
            <div class="p-3 bg-green-100 rounded-full text-green-600">
                <i class="fas fa-cash-register text-2xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500 flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-semibold">Pedidos Hoje</p>
                <h3 class="text-3xl font-bold text-gray-800"><?= $vendasHoje['qtd'] ?></h3>
            </div>
            <div class="p-3 bg-blue-100 rounded-full text-blue-500">
                <i class="fas fa-receipt text-2xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500 flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-semibold">Itens Críticos</p>
                <h3 class="text-3xl font-bold text-gray-800"><?= count($baixoEstoque) ?></h3>
                <p class="text-xs text-red-500">Abaixo de 5 un.</p>
            </div>
            <div class="p-3 bg-red-100 rounded-full text-red-500">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200 bg-red-50">
                <h3 class="font-bold text-red-800"><i class="fas fa-arrow-down mr-2"></i>Reposição Necessária</h3>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left">Item</th>
                        <th class="py-2 px-4 text-center">Qtd. Atual</th>
                        <th class="py-2 px-4 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($baixoEstoque as $item): ?>
                    <tr>
                        <td class="py-3 px-4"><?= htmlspecialchars($item['item']) ?></td>
                        <td class="py-3 px-4 text-center font-bold text-red-600"><?= $item['quantidade'] ?></td>
                        <td class="py-3 px-4 text-right">
                            <a href="/prog/PDV-GENERICO/estoque/index.php" class="text-blue-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($baixoEstoque)): ?>
                    <tr><td colspan="3" class="p-4 text-center text-green-600">Estoque Saudável!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200 bg-blue-50">
                <h3 class="font-bold text-blue-800"><i class="fas fa-history mr-2"></i>Últimas Vendas</h3>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 text-left">Hora</th>
                        <th class="py-2 px-4 text-left">Cliente</th>
                        <th class="py-2 px-4 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($ultimasVendas as $venda): ?>
                    <tr>
                        <td class="py-3 px-4"><?= date('H:i', strtotime($venda['hora'])) ?></td>
                        <td class="py-3 px-4"><?= htmlspecialchars($venda['cliente']) ?></td>
                        <td class="py-3 px-4 text-right font-bold text-green-700">R$ <?= number_format($venda['total'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>