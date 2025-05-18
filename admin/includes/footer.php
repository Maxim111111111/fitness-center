    </div>
</div>

<footer class="footer py-2 mt-auto">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-center">
                <p class="mb-0 small">© <?= date('Y') ?> Moreon Fitness. Все права защищены.</p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Дополнительные скрипты -->
<script>
    // Инициализация всплывающих подсказок
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
    
    // Изменение темы графиков Chart.js
    Chart.defaults.color = '#333333';
    Chart.defaults.borderColor = 'rgba(0, 0, 0, 0.1)';
    
    // Адаптация размера шрифта в графиках
    Chart.defaults.font.size = 11;
</script>
</body>
</html> 