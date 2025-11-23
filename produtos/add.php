<?php
session_start();
$activePage = 'produtos';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Novo Produto</h2>
    </header>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
        <form action="save.php" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2 font-bold" for="produto">Nome do Produto</label>
                <input type="text" name="produto" id="produto" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2 font-bold" for="descricao">Descrição</label>
                <textarea name="descricao" id="descricao" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 mb-2 font-bold" for="preco">Preço de Venda (R$)</label>
                    <input type="number" step="0.01" name="preco" id="preco" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800"
                        placeholder="0.00">
                </div>
                <div>
                    <label class="block text-gray-700 mb-2 font-bold" for="preco_custo">Preço de Custo (R$)</label>
                    <input type="number" step="0.01" name="preco_custo" id="preco_custo" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800 bg-yellow-50"
                        placeholder="0.00">
                    <p class="text-xs text-gray-500 mt-1">Usado para calcular o lucro nos relatórios.</p>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 mb-2 font-bold" for="disponivel">Disponibilidade</label>
                <select name="disponivel" id="disponivel"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                    <option value="1">Disponível</option>
                    <option value="0">Indisponível</option>
                </select>
            </div>

            <div class="flex justify-end">
                <a href="index.php" class="mr-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg">
                    Cancelar
                </a>
                <button type="submit" class="bg-red-800 hover:bg-red-700 text-yellow-400 font-bold py-2 px-4 rounded-lg">
                    Salvar Produto
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>