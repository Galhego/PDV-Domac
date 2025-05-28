<?php
session_start();
$activePage = 'history';
require_once '../includes/header.php';
require_once '../config/db.php';

// Redireciona se não logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Busca histórico (junta com nome do item)
$sql = "
  SELECT h.id, h.estoque_id, e.item,
         h.operacao, h.tipo_movimentacao,
         h.quantidade_anterior, h.quantidade_nova,
         h.data_movimentacao, h.usuario
    FROM estoque_historico h
    LEFT JOIN estoque e ON e.id = h.estoque_id
   ORDER BY h.data_movimentacao DESC
";
$stmt = $pdo->query($sql);
$historicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content ml-64 p-6">
  <header class="bg-white rounded-lg shadow p-4 mb-6">
    <h2 class="text-2xl font-bold text-red-800">Histórico de Estoque</h2>
  </header>

  <div class="bg-white rounded-lg shadow p-6">
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg overflow-hidden">
        <thead class="bg-red-800 text-yellow-400">
          <tr>
            <th class="py-3 px-4 text-left">Data/Hora</th>
            <th class="py-3 px-4 text-left">Item</th>
            <th class="py-3 px-4 text-left">Operação</th>
            <th class="py-3 px-4 text-left">Tipo</th>
            <th class="py-3 px-4 text-left">Qtd. Anterior</th>
            <th class="py-3 px-4 text-left">Qtd. Nova</th>
            <th class="py-3 px-4 text-left">Usuário</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php foreach ($historicos as $h): ?>
          <tr class="table-row-hover">
            <td class="py-4 px-4"><?= date('d/m/Y H:i:s', strtotime($h['data_movimentacao'])) ?></td>
            <td class="py-4 px-4"><?= htmlspecialchars($h['item'] ?? '—') ?></td>
            <td class="py-4 px-4"><?= $h['operacao'] ?></td>
            <td class="py-4 px-4"><?= $h['tipo_movimentacao'] ?? '—' ?></td>
            <td class="py-4 px-4"><?= $h['quantidade_anterior'] ?? '—' ?></td>
            <td class="py-4 px-4"><?= $h['quantidade_nova'] ?? '—' ?></td>
            <td class="py-4 px-4"><?= htmlspecialchars($h['usuario']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
