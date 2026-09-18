        </main>
    </div>
    <!-- Modal de Confirmación de Cerrar Sesión -->
    <div class="modal-overlay" id="modal-logout">
        <div class="modal-content">
            <h3 class="modal-title">Cerrar Sesión</h3>
            <div class="modal-body">
                <p>¿Estás seguro que deseas cerrar la sesión actual?</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-modal-cancel" id="btn-cancel-logout">Cancelar</button>
                <button type="button" class="btn btn-modal-confirm" id="btn-confirm-logout">Cerrar Sesión</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos
            const btnLogout = document.getElementById('btn-logout');
            const modalLogout = document.getElementById('modal-logout');
            const btnCancel = document.getElementById('btn-cancel-logout');
            const btnConfirm = document.getElementById('btn-confirm-logout');
            const toggleSidebarBtn = document.getElementById('toggle-sidebar');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            // Toggle sidebar
            if (toggleSidebarBtn && sidebar && mainContent) {
                toggleSidebarBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                });
            }

            if (btnLogout && modalLogout) {
                // Abrir modal
                btnLogout.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevenir navegación
                    modalLogout.classList.add('active');
                });
                
                // Cerrar modal (Cancelar)
                btnCancel.addEventListener('click', function() {
                    modalLogout.classList.remove('active');
                });
                
                // Confirmar logout
                btnConfirm.addEventListener('click', function() {
                    window.location.href = btnLogout.getAttribute('href');
                });
                
                // Cerrar al hacer clic fuera
                modalLogout.addEventListener('click', function(e) {
                    if (e.target === modalLogout) {
                        modalLogout.classList.remove('active');
                    }
                });
            }
        });
    </script>
</body>
</html>

