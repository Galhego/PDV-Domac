<?php
session_start();
$activePage = 'estoque';
require_once '../includes/header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$limit = 50;
$sql = "
  SELECT h.*, e.item 
  FROM estoque_historico h
  LEFT JOIN estoque e ON e.id = h.estoque_id
  ORDER BY h.data_movimentacao DESC
  LIMIT $limit
";
$historicos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

function traduzirOperacao($op, $tipo) {
    if ($op === 'insercao') return ['Criação', 'bg-blue-100 text-blue-800', 'fa-plus'];
    if ($op === 'remocao') return ['Exclusão', 'bg-gray-100 text-gray-800', 'fa-trash'];
    
    // Atualização
    if ($tipo === 'entrada') return ['Entrada', 'bg-green-100 text-green-800', 'fa-arrow-up'];
    if ($tipo === 'saida') return ['Saída', 'bg-red-100 text-red-800', 'fa-arrow-down'];
    
    return ['Ajuste', 'bg-yellow-100 text-yellow-800', 'fa-edit'];
}
?>

<div class="main-content ml-64 p-6">
  <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-red-800">Movimentações de Estoque</h2>
    <span class="text-sm text-gray-500">Últimos <?= $limit ?> registros</span>
  </header>

  <div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-100 border-b">
          <tr>
            <th class="py-3 px-4 text-left font-semibold text-gray-600">Quando</th>
            <th class="py-3 px-4 text-left font-semibold text-gray-600">Produto</th>
            <th class="py-3 px-4 text-center font-semibold text-gray-600">Tipo</th>
            <th class="py-3 px-4 text-center font-semibold text-gray-600">Alteração</th>
            <th class="py-3 px-4 text-right font-semibold text-gray-600">Usuário</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($historicos as $h): 
              list($label, $classe, $icone) = traduzirOperacao($h['operacao'], $h['tipo_movimentacao']);
              
              $delta = '';
              if ($h['quantidade_nova'] !== null && $h['quantidade_anterior'] !== null) {
                  $diff = $h['quantidade_nova'] - $h['quantidade_anterior'];
                  $delta = ($diff > 0 ? '+' : '') . $diff;
              } elseif ($h['operacao'] === 'insercao') {
                  $delta = '+' . $h['quantidade_nova'];
              }
          ?>
          <tr class="hover:bg-gray-50 transition">
            <td class="py-3 px-4 text-gray-600">
                <?= date('d/m/y', strtotime($h['data_movimentacao'])) ?> 
                <span class="text-xs text-gray-400 ml-1"><?= date('H:i', strtotime($h['data_movimentacao'])) ?></span>
            </td>
            
            <td class="py-3 px-4 font-medium text-gray-800">
                <?= htmlspecialchars($h['item'] ?? 'Item Excluído') ?>
            </td>

            <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $classe ?>">
                    <i class="fas <?= $icone ?> mr-1"></i> <?= $label ?>
                </span>
            </td>

            <td class="py-3 px-4 text-center">
                <?php if($delta): ?>
                    <span class="font-bold <?= strpos($delta, '+') !== false ? 'text-green-600' : 'text-red-600' ?>">
                        <?= $delta ?>
                    </span>
                    <div class="text-xs text-gray-400">
                        (<?= $h['quantidade_anterior'] ?? 0 ?> → <?= $h['quantidade_nova'] ?? 0 ?>)
                    </div>
                <?php else: ?>
                    <span class="text-gray-400">-</span>
                <?php endif; ?>
            </td>

            <td class="py-3 px-4 text-right text-gray-500">
                <i class="fas fa-user-circle text-gray-300 mr-1"></i>
                <?= htmlspecialchars(explode('@', $h['usuario'])[0])?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>