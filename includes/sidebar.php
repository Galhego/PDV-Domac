<!-- Arquivo: includes/sidebar.php -->
<div class="sidebar fixed h-full bg-red-800 text-white w-64 z-10" id="sidebar">
    <div class="p-4 flex items-center border-b border-red-700">
        <div class="bg-yellow-400 p-2 rounded-lg">
            <i class="fas fa-utensils text-red-800 text-xl"></i>
        </div>
        <h1 class="logo-text text-xl font-bold ml-3">Seu Coxinha</h1>
    </div>
    <nav class="mt-4">
        <ul>
            <li class="mb-1">
                <!-- Link para a página de vendas -->
                <a href="/prog/PDV-GENERICO/vendas/index.php" class="nav-item flex items-center p-3 hover:bg-red-700 <?php echo ($activePage == 'vendas') ? 'active-nav' : ''; ?>">
                    <i class="fas fa-cash-register text-yellow-400 mr-3"></i>
                    <span class="nav-text">Vendas</span>
                </a>
            </li>
            <li class="mb-1">
                <!-- Link para a página de estoque -->
                <a href="/prog/PDV-GENERICO/estoque/index.php" class="nav-item flex items-center p-3 hover:bg-red-700 <?php echo ($activePage == 'estoque') ? 'active-nav' : ''; ?>">
                    <i class="fas fa-boxes text-yellow-400 mr-3"></i>
                    <span class="nav-text">Estoque</span>
                </a>
            </li>
            <li class="mb-1">
                <!-- Link para a página de produtos -->
                <a href="/prog/PDV-GENERICO/produtos/index.php" class="nav-item flex items-center p-3 hover:bg-red-700 <?php echo ($activePage == 'produtos') ? 'active-nav' : ''; ?>">
                    <i class="fas fa-hamburger text-yellow-400 mr-3"></i>
                    <span class="nav-text">Produtos</span>
                </a>
            </li>
            <li class="mb-1">
                <!-- Link para a página de funcionários -->
                <a href="/prog/PDV-GENERICO/funcionarios/index.php" class="nav-item flex items-center p-3 hover:bg-red-700 <?php echo ($activePage == 'funcionarios') ? 'active-nav' : ''; ?>">
                    <i class="fas fa-users text-yellow-400 mr-3"></i>
                    <span class="nav-text">Funcionários</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="absolute bottom-0 w-full p-4 border-t border-red-700">
        <button onclick="toggleSidebar()" class="text-yellow-400 hover:text-yellow-300 w-full text-left">
            <i class="fas fa-chevron-left" id="sidebar-toggle-icon"></i>
            <span class="nav-text ml-2">Recolher</span>
        </button>
    </div>
</div>