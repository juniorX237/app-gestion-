// ========================================
// JavaScript principal - Lycée Kaisen
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s, transform 0.5s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() { alert.remove(); }, 500);
        }, 5000);
    });

    // Confirm delete actions
    const deleteLinks = document.querySelectorAll('.confirm-delete');
    deleteLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.')) {
                e.preventDefault();
            }
        });
    });

    // Mobile sidebar toggle
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== menuToggle) {
                sidebar.classList.remove('open');
            }
        });
    }

    // File input preview for photos
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');
    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    photoPreview.src = ev.target.result;
                    photoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Dynamic filtering for selects
    const filterSelects = document.querySelectorAll('.filter-auto');
    filterSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // Animate stat numbers
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach(function(el) {
        const target = parseInt(el.textContent);
        if (isNaN(target)) return;
        let current = 0;
        const increment = Math.ceil(target / 30);
        const timer = setInterval(function() {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current;
        }, 30);
    });

    // Notes input validation (0-20)
    const noteInputs = document.querySelectorAll('input[name^="notes"]');
    noteInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            let val = parseFloat(this.value);
            if (val < 0) this.value = 0;
            if (val > 20) this.value = 20;
        });
    });
});

// Print function
function printPage() {
    window.print();
}
