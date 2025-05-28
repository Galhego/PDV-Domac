<?php
require_once '../config/db.php';
require_once '../includes/header.php';
?>

<div class="main-content ml-64 p-6">
    <header class="bg-white rounded-lg shadow p-4 mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-red-800">Novo Produto</h2>
    </header>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="POST" action="save.php">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="produto">Nome do Produto</label>
                <input type="text" name="produto" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="descricao">Descrição</label>
                <textarea name="descricao" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800"></textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="preco">Preço</label>
                <input type="text" name="preco" required pattern="\d+(\,\d{2})?"
                    placeholder="R$ 0,00"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2" for="disponivel">Disponível</label>
                <select name="disponivel"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-800">
                    <option value="1">Sim</option>
                    <option value="0">Não</option>
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