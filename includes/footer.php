    </div>

    <!-- Footer -->
    <footer class="footer">
        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
    </footer>

    <script src="<?php echo SITE_URL; ?>js/interactions.js"></script>
    <script>

        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>
</html>

