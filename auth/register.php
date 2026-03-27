<?php include '../config/db.php'; ?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/output.css">
</head>

<body>

    <div class=" flex h-screen font-sans bg-gray-50">

        <div class="w-full md:w-1/2 flex items-center justify-center bg-white p-8 md:p-16">
            <div class="max-w-md w-full">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Create Faculty Account</h2>
                <p class="text-gray-500 mb-8">Join our academic community and manage your classes easily.</p>

                <button class="w-full flex items-center justify-center gap-3 border border-gray-300 py-3 rounded-lg hover:bg-gray-50 transition mb-6">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google">
                    <span class="font-medium text-gray-700 text-sm">Register via Gmail</span>
                </button>

                <div class="relative mb-6 text-center">
                    <hr class="border-gray-200">
                    <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white px-4 text-xs text-gray-400">or use your details</span>
                </div>

                <form method="POST" class="grid grid-cols-1 gap-4">
                    <div>
                        <input type="text" name="fullname" placeholder="Full Name" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" name="contact" placeholder="Contact No." required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition">
                        <input type="text" name="username" placeholder="Username" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>

                    <div>
                        <input type="email" name="email" placeholder="Email Address" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>

                    <div>
                        <input type="password" name="password" placeholder="Password" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition">
                    </div>

                    <button type="submit" name="register"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg mt-2 transition duration-300 shadow-md">
                        Complete Registration
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-gray-600">
                    Already have an account? <a href="login.php" class="text-blue-600 font-bold hover:underline">Login</a>
                </p>
            </div>
        </div>

        <div class="hidden md:flex w-1/2 bg-blue-600 relative overflow-hidden flex-col items-center justify-center p-25 text-white">
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <circle cx="10" cy="10" r="20" fill="white" />
                    <circle cx="90" cy="90" r="30" fill="white" />
                </svg>
            </div>

            <div class="relative z-10 text-center">
                <h1 class="text-5xl font-extrabold leading-tight mb-4">
                    Empowering <br><span class="text-yellow-400">Educators</span>
                </h1>
                <p class="text-blue-100 text-lg max-w-sm mx-auto mb-10">
                    Simplify your classroom workflow. Automate attendance, manage grades, and focus on what matters: teaching.
                </p>

                <img src="https://illustrations.popsy.co/white/web-design.svg"
                    alt="Teacher Illustration"
                    class="w-3/4 mx-auto drop-shadow-2xl">
            </div>

            <div class="absolute bottom-10 flex gap-4 text-xs text-blue-200">
                <span>© 2024 Attendance Management System</span>
                <span class="hover:text-white cursor-pointer underline">Terms of Service</span>
            </div>
        </div>

    </div>

    <div id="toast" class="toast"></div>

    <script src="../assets/js/toast.js"></script>

    <?php
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $fullname = $_POST['fullname'];
        $contact = $_POST['contact'];

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, fullname, contact) VALUES (?, ?, ?, ?, ?)");

        $stmt->bind_param("sssss", $username, $email, $password, $fullname, $contact);

        if ($stmt->execute()) {
            echo "<script>
            showToast('Registered successfully');

            setTimeout(() => {
                window.location.href = '../auth/login.php';
            }, 3000);
        </script>";
        } else {
            echo "<script>showToast('Error occurred','error');</script>";
        }

        $stmt->close();
    }
    ?>

</body>

</html>