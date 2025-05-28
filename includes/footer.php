    </div>

    <!-- Modal Structure -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden" id="modal-overlay">
        <!-- Conteúdo dos modais será inserido aqui -->
    </div>

    <script>
        // Função para alternar a sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleIcon = document.getElementById('sidebar-toggle-icon');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
            } else {
                toggleIcon.classList.remove('fa-chevron-right');
                toggleIcon.classList.add('fa-chevron-left');
            }
        }
    </script>
</body>
</html>