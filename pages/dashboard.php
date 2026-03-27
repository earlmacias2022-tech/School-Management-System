<!DOCTYPE html>
<html class="h-full">

<head>
    <title>Dashboard | Attendance Pro</title>
    <link rel="stylesheet" href="../assets/css/output.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }

        .bg-float-1 {
            position: fixed;
            top: -20%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.06) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .bg-float-2 {
            position: fixed;
            bottom: -20%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
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

        .animate-delay-400 {
            animation-delay: 0.4s;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen relative overflow-x-hidden">
    <!-- Decorative Background -->
    <div class="bg-float-1"></div>
    <div class="bg-float-2"></div>

    <?php
    include '../config/db.php';
    include '../includes/sidebar.php';
    include '../includes/topbar.php';


    ?>

    <?php
    // Query total number of sections
    $query = "SELECT COUNT(*) AS total_sections FROM sections";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $totalSections = $row['total_sections'];
    ?>

    <main class="ml-64 p-8 relative z-10">
        <!-- Welcome Header -->
        <div class="mb-8 animate-fade-in-up">
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Welcome back, <?php echo $_SESSION['fullname'] ?? 'Admin'; ?>!</h1>
            <p class="text-slate-500">Here's what's happening with your school today.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Students -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-blue-100/50 border border-blue-50 animate-fade-in-up animate-delay-100">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-200">
                        <i data-lucide="users" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">+12%</span>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Total Students</p>
                <h3 class="text-3xl font-bold text-slate-800">1,284</h3>
            </div>

            <!-- Total Sections -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-green-100/50 border border-green-50 animate-fade-in-up animate-delay-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg shadow-green-200">
                        <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">+5%</span>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Total Sections</p>
                <h3 class="text-3xl font-bold text-slate-800"><?php echo $totalSections ?></h3>
            </div>

            <!-- Total Present -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-green-100/50 border border-green-50 animate-fade-in-up animate-delay-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-500 rounded-xl shadow-lg shadow-green-200">
                        <i data-lucide="user-check" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">+5%</span>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Total Present</p>
                <h3 class="text-3xl font-bold text-slate-800">52</h3>
            </div>
            <!-- Absent -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-orange-100/50 border border-orange-50 animate-fade-in-up animate-delay-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl shadow-lg shadow-orange-200">
                        <i data-lucide="user-x" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">-3%</span>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Absent Today</p>
                <h3 class="text-3xl font-bold text-slate-800">42</h3>
            </div>



        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Weekly Attendance Trends -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-blue-100/50 border border-blue-50">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-slate-800">Weekly Attendance Trends</h2>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                        <span class="text-sm text-slate-500">Attendance %</span>
                    </div>
                </div>
                <div class="relative h-64">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>

            <!-- Department Distribution -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-blue-100/50 border border-blue-50">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-slate-800">Department Distribution</h2>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                        <span class="text-sm text-slate-500">Sections</span>
                    </div>
                </div>
                <div class="relative h-64">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="section.php" class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg hover:border-blue-200 transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-50 rounded-lg group-hover:bg-blue-100 transition-colors">
                            <i data-lucide="plus-circle" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-800">Add New Section</h4>
                            <p class="text-sm text-slate-500">Create a new class section</p>
                        </div>
                    </div>
                </a>
                <a href="student.php" class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg hover:border-green-200 transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-green-50 rounded-lg group-hover:bg-green-100 transition-colors">
                            <i data-lucide="user-plus" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-800">Register Student</h4>
                            <p class="text-sm text-slate-500">Add a new student</p>
                        </div>
                    </div>
                </a>
                <a href="attendance.php" class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg hover:border-purple-200 transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-purple-50 rounded-lg group-hover:bg-purple-100 transition-colors">
                            <i data-lucide="clipboard-check" class="w-5 h-5 text-purple-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-800">Take Attendance</h4>
                            <p class="text-sm text-slate-500">Mark today's attendance</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();

        // Line Chart Logic
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                datasets: [{
                    label: 'Attendance %',
                    data: [85, 92, 88, 95, 82],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 70,
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Pie Chart Logic
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['ICT', 'STEM', 'ABM', 'HUMMS'],
                datasets: [{
                    data: [40, 25, 20, 15],
                    backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>