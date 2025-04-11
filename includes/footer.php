</div>
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <p>&copy; <?php echo date('Y'); ?> Loan Management System. All rights reserved.</p>
                </div>
                <div class="flex space-x-4">
                    <a href="#" class="hover:text-blue-400">Terms of Service</a>
                    <a href="#" class="hover:text-blue-400">Privacy Policy</a>
                    <a href="#" class="hover:text-blue-400">Contact</a>
                </div>
            </div>
        </div>
    </footer>
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('[class*="bg-"][class*="-100"]');
                alerts.forEach(function(alert) {
                    alert.style.transition = 'opacity 0.5s ease-in-out';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                });
            }, 5000);
        });
    </script>
</body>
</html>
