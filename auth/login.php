<?php
session_start();
include '../config/db.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ClassOptima</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '#2563eb',
                        secondary: '#06b6d4',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease forwards;
        }

        .animate-delay-100 {
            animation-delay: 0.1s;
        }

        .animate-delay-200 {
            animation-delay: 0.2s;
        }

        .animate-delay-300 {
            animation-delay: 0.3s;
        }

        .bg-float-1 {
            position: absolute;
            top: -20%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .bg-float-2 {
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.06) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            padding: 16px 24px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 14px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            z-index: 9999;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }

        .toast.success {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        .toast.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
    </style>
</head>

<body class="flex justify-center items-center h-screen w-screen overflow-hidden ">
    <!-- Decorative Background -->
    <div class=" bg-float-1">
    </div>
    <div class="bg-float-2"></div>

    <div class="w-full max-w-md animate-fade-in-up">

        <!-- Login Card -->
        <div class="bg-white rounded-3xl px-12 py-5 shadow-xl shadow-blue-100 border border-blue-50">

            <!-- Logo & Header -->
            <div>
                <a href="../landing.php" class="inline-flex items-center gap-3 mb-5">
                    <div class="bg-gradient-to-br from-blue-600 to-cyan-500 p-2 rounded-xl shadow-lg shadow-blue-200">
                        <i data-lucide="graduation-cap" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="text-1xl font-bold tracking-tight text-slate-800">Class<span class="text-blue-600">Optima</span></span>
                </a>
            </div>
            <div class="text-center mb-10">
                <h1 class="text-2xl font-bold text-slate-900 mb-2">Welcome Back</h1>
                <p class="text-slate-500">Sign in to your account to continue</p>
            </div>
            <form method="POST" class="space-y-4">
                <!-- Username -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="user" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <input type="text" name="username" placeholder="Enter your username" required
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <input type="password" name="password" id="password" placeholder="Enter your password" required
                            class="w-full pl-12 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all">
                        <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer">
                            <i data-lucide="eye" class="w-5 h-5 text-slate-400 hover:text-slate-600 transition-colors" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between ">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <span class="text-sm text-slate-600">Remember me</span>
                    </label>
                    <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">Forgot Password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" name="login"
                    class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:shadow-blue-300 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2 text-base">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    <span>Sign In</span>
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-4 text-sm text-slate-400 bg-white">or continue with</span>
                </div>
            </div>

            <!-- Social Login -->
            <button class="w-full py-3 px-4 bg-white border-2 border-slate-200 hover:border-slate-300 rounded-xl text-slate-700 font-semibold transition-all duration-300 flex items-center justify-center gap-3 hover:bg-slate-50">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                </svg>
                <span>Sign in with Google</span>
            </button>

            <!-- Register Link -->
            <p class="text-center text-slate-600 text-sm mt-3 animate-fade-in-up animate-delay-200">
                Don't have an account?
                <a href="register.php" class="font-bold text-blue-600 hover:text-blue-700 transition-colors">Create Account</a>
            </p>

            <!-- Back to Home -->
            <div class="text-center mt-4 animate-fade-in-up animate-delay-300">
                <a href="../landing.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-700 text-sm transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back to Home
                </a>
            </div>

        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        lucide.createIcons();

        function showToast(message, type) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type + ' show';
            setTimeout(() => {
                toast.className = 'toast';
            }, 3000);
        }

        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                eyeIcon.setAttribute('data-lucide', type === 'password' ? 'eye' : 'eye-off');
                lucide.createIcons();
            });
        }
    </script>

    <?php
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['email'] = $user['email'];
                echo "<script>window.location='../pages/dashboard.php';</script>";
            } else {
                echo "<script>showToast('Wrong password','error');</script>";
            }
        } else {
            echo "<script>showToast('User not found','error');</script>";
        }
    }
    ?>
</body>

</html>