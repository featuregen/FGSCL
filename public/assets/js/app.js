/**
 * EduGen — Core JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {

    // ─── Sidebar Toggle ──────────────────────────────
    const sidebar = document.getElementById('sidebar');
    const appContent = document.getElementById('appContent');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileOverlay = document.getElementById('mobileOverlay');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const isMobile = window.innerWidth <= 1024;
            
            if (isMobile) {
                sidebar.classList.toggle('mobile-open');
                mobileOverlay.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                appContent.classList.toggle('sidebar-collapsed');
                // Save preference
                localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
            }
        });
    }

    // Close sidebar on mobile overlay click
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', function() {
            sidebar.classList.remove('mobile-open');
            mobileOverlay.classList.remove('show');
        });
    }

    // Restore sidebar state
    if (localStorage.getItem('sidebar_collapsed') === 'true' && window.innerWidth > 1024) {
        sidebar?.classList.add('collapsed');
        appContent?.classList.add('sidebar-collapsed');
    }

    // ─── Theme Toggle (Dark/Light) ──────────────────
    const themeToggle = document.getElementById('themeToggle');
    
    // Apply saved theme on load
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        });
    }

    // ─── Dropdowns ───────────────────────────────────
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        const trigger = dropdown.querySelector('[id$="Btn"]');
        const menu = dropdown.querySelector('.dropdown-menu');

        if (trigger && menu) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                    if (m !== menu) m.classList.remove('show');
                });
                
                menu.classList.toggle('show');
            });
        }
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });

    // ─── Auto-dismiss Alerts ─────────────────────────
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.transition = 'opacity 0.5s, transform 0.5s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);

    // ─── Fullscreen Toggle ───────────────────────────
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    if (fullscreenBtn) {
        fullscreenBtn.style.display = 'flex';
        fullscreenBtn.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                this.querySelector('i').classList.replace('bi-arrows-fullscreen', 'bi-fullscreen-exit');
            } else {
                document.exitFullscreen();
                this.querySelector('i').classList.replace('bi-fullscreen-exit', 'bi-arrows-fullscreen');
            }
        });
    }

    // ─── Password Visibility Toggle ─────────────────
    document.querySelectorAll('.toggle-password, .password-toggle').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const wrapper = this.closest('.input-icon-wrapper') || this.closest('.password-wrapper');
            const input = wrapper ? wrapper.querySelector('input[type="password"], input[type="text"]') : this.previousElementSibling;
            if (!input) return;

            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });

    // ─── Delete Confirmations ────────────────────────
    document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href') || this.dataset.url;
            const name = this.dataset.name || 'this item';
            
            Swal.fire({
                title: 'Are you sure?',
                html: `You are about to delete <strong>${name}</strong>. This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'swal-custom-popup',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });

    // ─── Form Validation Highlight ───────────────────
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            
            this.querySelectorAll('[required]').forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    valid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                const firstInvalid = this.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
            }
        });
    });

    // Remove invalid class on input
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });

    // ─── DataTables Init ─────────────────────────────
    document.querySelectorAll('.datatable').forEach(table => {
        if ($.fn.DataTable) {
            $(table).DataTable({
                responsive: true,
                pageLength: 20,
                language: {
                    search: '',
                    searchPlaceholder: 'Search...',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_',
                    paginate: {
                        first: '<i class="bi bi-chevron-double-left"></i>',
                        last: '<i class="bi bi-chevron-double-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>',
                        next: '<i class="bi bi-chevron-right"></i>',
                    }
                },
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            });
        }
    });

    // ─── Keyboard Shortcuts ──────────────────────────
    document.addEventListener('keydown', function(e) {
        // Ctrl+K — Focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('globalSearch')?.click();
        }
    });

    // ─── AJAX Form Submission Helper ─────────────────
    window.submitFormAjax = function(form, successCallback) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn?.innerHTML;
        
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;"></span> Processing...';
        }
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false,
                });
                if (successCallback) successCallback(data);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong.' });
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    };

    console.log('%c🎓 EduGen', 'color: #4F46E5; font-size: 20px; font-weight: bold;');
    console.log('%cVersion ' + '1.0.0', 'color: #6B7280; font-size: 12px;');
});
