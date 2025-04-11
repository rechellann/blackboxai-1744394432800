<?php require_once 'includes/header.php'; ?>

<div class="relative bg-white">
    <!-- Hero section -->
    <div class="relative bg-blue-600">
        <div class="absolute inset-0">
            <img class="w-full h-full object-cover" src="https://images.pexels.com/photos/3943716/pexels-photo-3943716.jpeg" alt="People working on laptops">
            <div class="absolute inset-0 bg-blue-600 mix-blend-multiply"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">Welcome to Loan Management System</h1>
            <p class="mt-6 text-xl text-blue-100 max-w-3xl">Get quick and easy access to loans with our streamlined application process. Manage your loans efficiently with our user-friendly platform.</p>
            
            <?php if (!isLoggedIn()): ?>
            <div class="mt-10 flex space-x-4">
                <a href="/auth/login.php" class="inline-block bg-white py-3 px-8 rounded-lg text-blue-600 font-semibold hover:bg-blue-50 transition duration-300">Login</a>
                <a href="/auth/register.php" class="inline-block bg-blue-500 py-3 px-8 rounded-lg text-white font-semibold hover:bg-blue-400 transition duration-300">Register Now</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900">Why Choose Us?</h2>
                <p class="mt-4 text-lg text-gray-500">Experience the best loan management service with our feature-rich platform.</p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Feature 1 -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-bolt text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Quick Processing</h3>
                    <p class="mt-4 text-gray-500">Get your loan approved quickly with our streamlined application process.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-shield-alt text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Secure Platform</h3>
                    <p class="mt-4 text-gray-500">Your data is protected with industry-standard security measures.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="text-blue-600 mb-4">
                        <i class="fas fa-chart-line text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Easy Management</h3>
                    <p class="mt-4 text-gray-500">Track and manage your loans with our user-friendly dashboard.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="bg-blue-600">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-white">Ready to get started?</h2>
                <p class="mt-4 text-xl text-blue-100">Create an account today and explore our loan options.</p>
                <?php if (!isLoggedIn()): ?>
                <div class="mt-8">
                    <a href="/auth/register.php" class="inline-block bg-white py-3 px-8 rounded-lg text-blue-600 font-semibold hover:bg-blue-50 transition duration-300">Sign Up Now</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
