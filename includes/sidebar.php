<div class="sidebar fixed h-full bg-red-800 text-white w-64 z-10" id="sidebar">
    <a href="/prog/PDV-GENERICO/dashboard.php" class="block hover:bg-red-900 transition duration-200 no-underline">
        <div class="p-4 flex items-center border-b border-red-700">
            <div class="bg-yellow-400 p-2 rounded-lg">
                <i class="fas fa-utensils text-red-800 text-xl"></i>
            </div>
            <h1 class="logo-text text-xl font-bold ml-3 text-white">Meu Coxinha</h1>
        </div>
    </a>

    <nav class="mt-4">
        <ul>
            <li class="mb-1">
                <a href="/prog/PDV-GENERICO/dashboard.php" class="nav-item flex items-center p-3 hover:bg-red-700 <?php echo ($activePage == 'dashboard') ? 'active-nav' : ''; ?>">
                    <i class="fas fa-home text-yellow-400 mr-3"></i>
                    <span class="nav-text">Início</span>
                </a>
            </li>
            <li class="mb-1">
                <a href="/prog/PDV-GENERICO/vendas/index.php" class="nav-item flex items-center p-3 hover:bg-red-700 <?php echo ($activePage == 'vendas') ? 'active-nav' : ''; ?>">
                    <i class="fas fa-cash-register text-yellow-400 mr-3"></i>
                    <span class="nav-text">Vendas</span>
                </a>
            </li>
            <li class="mb-1">
                <a href="/prog/PDV-GENERICO/estoque/index.php" class="nav-item flex items-center p-3 hover:bg-red-700 <?php echo ($activePage == 'estoque') ? 'active-nav' : ''; ?>">
                    <i class="fas fa-boxes text-yellow-400 mr-3"></i>
                    <span class="nav-text">Estoque</span>
                </a>
            </li>
            <li class="mb-1">
                <a href="/prog/PDV-GENERICO/produtos/index.php" class="nav-item flex items-center p-3 hover:bg-red-700 <?php echo ($activePage == 'produtos') ? 'active-nav' : ''; ?>">
                    <i class="fas fa-hamburger text-yellow-400 mr-3"></i>
                    <span class="nav-text">Produtos</span>
                </a>
            </li>
            <li class="mb-1">
                <a href="/prog/PDV-GENERICO/funcionarios/index.php" class="nav-item flex items-center p-3 hover:bg-red-700 <?php echo ($activePage == 'funcionarios') ? 'active-nav' : ''; ?>">
                    <i class="fas fa-users text-yellow-400 mr-3"></i>
                    <span class="nav-text">Funcionários</span>
                </a>
            </li>
            <li class="mb-1">
                <a href="/prog/PDV-GENERICO/relatorios.php" class="nav-item flex items-center p-3 hover:bg-red-700 <?php echo ($activePage == 'relatorios') ? 'active-nav' : ''; ?>">
                    <i class="fas fa-chart-line text-yellow-400 mr-3"></i>
                    <span class="nav-text">Relatórios</span>
                </a>
            </li>
            
            <li class="mt-8 border-t border-red-700 pt-2">
                <a href="/prog/PDV-GENERICO/logout.php" class="nav-item flex items-center p-3 hover:bg-red-700 text-yellow-400">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    <span class="nav-text">Sair</span>
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