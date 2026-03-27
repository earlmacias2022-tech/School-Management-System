<!DOCTYPE html>
<html class="h-full">

<head>
    <title>Dashboard | ClassOptima</title>
    <link rel="stylesheet" href="../assets/css/output.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            background: radial-gradient(circle, rgba(37, 99, 235, 0.08) 0%, transparent 70%);
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
            background: radial-gradient(circle, rgba(6, 182, 212, 0.06) 0%, transparent 70%);
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
            opacity: 0;
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

        .animate-delay-500 {
            animation-delay: 0.5s;
        }

        .animate-delay-600 {
            animation-delay: 0.6s;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen relative overflow-x-hidden">
    <!-- Decorative Background -->
    <div class="bg-float-1"></div>
    <div class="bg-float-2"></div>

    <?php
    include '../config/db.php';
    include '../includes/side-top-bar.php';

    // Query total sections
    $query = "SELECT COUNT(*) AS total_sections FROM sections";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $totalSections = $row['total_sections'];

    // Query total students
    $query = "SELECT COUNT(*) AS total_students FROM students";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $totalStudents = $row['total_students'];
    ?>

    <!-- Main Content Area -->
    <main class="ml-64 pt-0 p-8 relative z-10">

        <!-- Dashboard Header -->
        <div class="mb-8 animate-fade-in-up mt-5">
            <div class="bg-gradient-to-r from-blue-600 to-cyan-500 rounded-3xl p-8 shadow-xl shadow-blue-200/50 border border-blue-100/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">School Management Dashboard</h1>
                        <p class="text-blue-100 text-sm">Monitor attendance, track student performance, and get actionable insights.</p>
                    </div>
                    <div class="hidden md:flex items-center gap-3">
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl px-4 py-3 text-center">
                            <p class="text-white/80 text-xs font-medium">Last Updated</p>
                            <p class="text-white font-bold text-sm" id="currentDate"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <!-- Total Students Card -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-blue-100/50 border border-blue-50 animate-fade-in-up animate-delay-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-200">
                        <i data-lucide="users" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">+12%</span>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Total Students</p>
                <h3 class="text-3xl font-bold text-slate-800"><?php echo $totalStudents ?></h3>
                <div class="mt-3 flex items-center gap-2">
                    <div class="flex-1 bg-blue-100 rounded-full h-1.5">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-1.5 rounded-full" style="width: 75%"></div>
                    </div>
                    <span class="text-xs text-slate-500">75% enrolled</span>
                </div>
            </div>

            <!-- Total Sections Card -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-green-100/50 border border-green-50 animate-fade-in-up animate-delay-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg shadow-green-200">
                        <i data-lucide="layers" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">+5%</span>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Total Sections</p>
                <h3 class="text-3xl font-bold text-slate-800"><?php echo $totalSections; ?></h3>
                <div class="mt-3 flex items-center gap-2">
                    <div class="flex-1 bg-green-100 rounded-full h-1.5">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-1.5 rounded-full" style="width: 60%"></div>
                    </div>
                    <span class="text-xs text-slate-500">Active sections</span>
                </div>
            </div>

            <!-- Present Today Card -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-cyan-100/50 border border-cyan-50 animate-fade-in-up animate-delay-300 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-cyan-500 to-teal-600 rounded-xl shadow-lg shadow-cyan-200">
                        <i data-lucide="user-check" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">+8%</span>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Present Today</p>
                <h3 class="text-3xl font-bold text-slate-800">1,100</h3>
                <div class="mt-3 flex items-center gap-2">
                    <div class="flex-1 bg-cyan-100 rounded-full h-1.5">
                        <div class="bg-gradient-to-r from-cyan-500 to-teal-600 h-1.5 rounded-full" style="width: 88%"></div>
                    </div>
                    <span class="text-xs text-slate-500">88% attendance</span>
                </div>
            </div>

            <!-- Absent Today Card -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-orange-100/50 border border-orange-50 animate-fade-in-up animate-delay-400 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl shadow-lg shadow-orange-200">
                        <i data-lucide="user-x" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">-3%</span>
                </div>
                <p class="text-sm font-medium text-slate-500 mb-1">Absent Today</p>
                <h3 class="text-3xl font-bold text-slate-800">150</h3>
                <div class="mt-3 flex items-center gap-2">
                    <div class="flex-1 bg-orange-100 rounded-full h-1.5">
                        <div class="bg-gradient-to-r from-orange-500 to-red-500 h-1.5 rounded-full" style="width: 12%"></div>
                    </div>
                    <span class="text-xs text-slate-500">12% absent</span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

            <!-- Students per Section Chart -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-blue-100/50 border border-blue-50 animate-fade-in-up animate-delay-500">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Students per Section</h2>
                        <p class="text-sm text-slate-500">Distribution of students across all sections</p>
                    </div>
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 text-blue-600"></i>
                    </div>
                </div>
                <div class="relative h-64">
                    <canvas id="studentsPerSectionChart"></canvas>
                </div>
            </div>

            <!-- Attendance Rate per Section Chart -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-green-100/50 border border-green-50 animate-fade-in-up animate-delay-600">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Attendance Rate per Section</h2>
                        <p class="text-sm text-slate-500">Attendance percentage by section</p>
                    </div>
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i data-lucide="trending-up" class="w-5 h-5 text-green-600"></i>
                    </div>
                </div>
                <div class="relative h-64">
                    <canvas id="attendanceRateChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Insights & Recommendations Section -->
        <div class="animate-fade-in-up animate-delay-600">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800">Insights & Recommendations</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Insight Card 1 -->
                <div class="bg-white rounded-2xl p-6 shadow-lg shadow-orange-100/50 border border-orange-50 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-orange-100 rounded-xl">
                            <i data-lucide="alert-triangle" class="w-6 h-6 text-orange-600"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 mb-1">Section A has highest absences</h4>
                            <p class="text-sm text-slate-500">Section A recorded 25 absent students today, the highest among all sections. Consider reviewing attendance patterns and conducting follow-ups.</p>
                            <div class="mt-3 flex items-center gap-2">
                                <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">Requires Action</span>
                                <span class="text-xs text-slate-400">Updated today</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Insight Card 2 -->
                <div class="bg-white rounded-2xl p-6 shadow-lg shadow-green-100/50 border border-green-50 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-green-100 rounded-xl">
                            <i data-lucide="trending-up" class="w-6 h-6 text-green-600"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 mb-1">Attendance improved by 5%</h4>
                            <p class="text-sm text-slate-500">Overall attendance rate has improved by 5% this week compared to the previous week. Great job maintaining consistency!</p>
                            <div class="mt-3 flex items-center gap-2">
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Positive Trend</span>
                                <span class="text-xs text-slate-400">This week</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Insight Card 3 -->
                <div class="bg-white rounded-2xl p-6 shadow-lg shadow-blue-100/50 border border-blue-50 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-blue-100 rounded-xl">
                            <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 mb-1">Section C has most enrollments</h4>
                            <p class="text-sm text-slate-500">Section C leads with 150 enrolled students. Consider adding an additional section or adjusting teacher allocation.</p>
                            <div class="mt-3 flex items-center gap-2">
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">High Enrollment</span>
                                <span class="text-xs text-slate-400">Current semester</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Insight Card 4 -->
                <div class="bg-white rounded-2xl p-6 shadow-lg shadow-purple-100/50 border border-purple-50 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-purple-100 rounded-xl">
                            <i data-lucide="eye" class="w-6 h-6 text-purple-600"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 mb-1">Review Section B trends</h4>
                            <p class="text-sm text-slate-500">Section B shows inconsistent attendance patterns over the past month. Early intervention could help stabilize participation.</p>
                            <div class="mt-3 flex items-center gap-2">
                                <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-medium rounded-full">Monitor</span>
                                <span class="text-xs text-slate-400">Last 30 days</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 animate-fade-in-up animate-delay-600">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <i data-lucide="zap" class="w-5 h-5 text-indigo-600"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800">Quick Actions</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="section.php" class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg hover:border-blue-200 hover:-translate-y-1 transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-50 rounded-lg group-hover:bg-blue-100 transition-colors">
                            <i data-lucide="plus-circle" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-800 text-sm">Add Section</h4>
                            <p class="text-xs text-slate-500">Create new section</p>
                        </div>
                    </div>
                </a>

                <a href="../students/list.php" class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg hover:border-green-200 hover:-translate-y-1 transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-green-50 rounded-lg group-hover:bg-green-100 transition-colors">
                            <i data-lucide="user-plus" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-800 text-sm">Register Student</h4>
                            <p class="text-xs text-slate-500">Add new student</p>
                        </div>
                    </div>
                </a>

                <a href="../attendance/view.php" class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg hover:border-cyan-200 hover:-translate-y-1 transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-cyan-50 rounded-lg group-hover:bg-cyan-100 transition-colors">
                            <i data-lucide="clipboard-check" class="w-5 h-5 text-cyan-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-800 text-sm">Take Attendance</h4>
                            <p class="text-xs text-slate-500">Mark today's attendance</p>
                        </div>
                    </div>
                </a>

                <a href="../attendance/view.php" class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg hover:border-purple-200 hover:-translate-y-1 transition-all duration-300 group">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-purple-50 rounded-lg group-hover:bg-purple-100 transition-colors">
                            <i data-lucide="download" class="w-5 h-5 text-purple-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-slate-800 text-sm">Export Reports</h4>
                            <p class="text-xs text-slate-500">Download attendance</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-sm text-slate-400">&copy; 2026 ClassOptima. All rights reserved.</p>
        </div>
    </main>

    <script>
        lucide.createIcons();

        // Set current date
        const dateOptions = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', dateOptions);

        // Students per Section - Bar Chart
        const studentsCtx = document.getElementById('studentsPerSectionChart').getContext('2d');
        new Chart(studentsCtx, {
            type: 'bar',
            data: {
                labels: ['Section A', 'Section B', 'Section C', 'Section D', 'Section E', 'Section F'],
                datasets: [{
                    label: 'Number of Students',
                    data: [120, 95, 150, 80, 110, 75],
                    backgroundColor: [
                        'rgba(37, 99, 235, 0.8)',
                        'rgba(6, 182, 212, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)'
                    ],
                    borderRadius: 8,
                    borderSkipped: false,
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
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                family: 'Inter'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Inter',
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        // Attendance Rate per Section - Line Chart
        const attendanceCtx = document.getElementById('attendanceRateChart').getContext('2d');
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: ['Section A', 'Section B', 'Section C', 'Section D', 'Section E', 'Section F'],
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: [78, 92, 85, 88, 95, 82],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
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
                        },
                        ticks: {
                            font: {
                                family: 'Inter'
                            },
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Inter',
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>