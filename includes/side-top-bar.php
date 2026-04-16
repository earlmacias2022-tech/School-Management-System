<?php
// Get the current file name (e.g., 'dashboard.php' or 'section.php')
$current_page = basename($_SERVER['PHP_SELF']);

/**
 * Helper function to determine CSS classes based on active state
 * @param string $page The filename to check against
 * @return string Tailwind CSS classes
 */
function getNavClasses($page, $current_page)
{
    $activeClasses = "bg-blue-50 text-blue-600 border border-blue-100";
    $inactiveClasses = "text-slate-500 hover:bg-gray-50 hover:text-slate-700";

    return ($page == $current_page) ? $activeClasses : $inactiveClasses;
}

$title = "";

if ($current_page == 'dashboard.php') {
    $title = 'Dashboard Overview';
} else if ($current_page == 'section.php') {
    $title = 'Section Management';
} else if ($current_page == 'students.php') {
    $title = 'Student Management';
} else if ($current_page == 'attendance.php') {
    $title = 'Attendance';
}
?>

<!-- SIDEBAR-->
<div class="w-64 h-screen bg-white text-slate-600 fixed flex flex-col border-r border-gray-100 shadow-lg">
    <div class="p-6 flex items-center space-x-3 border-b border-gray-100">
        <div class="bg-gradient-to-br from-blue-600 to-cyan-500 p-2 rounded-xl shadow-lg shadow-blue-200">
            <i data-lucide="calendar-check" class="text-white w-6 h-6"></i>
        </div>
        <span class="text-lg font-bold tracking-tight text-slate-800">
            Class<span class="text-blue-600">Optima</span>
        </span>
    </div>

    <nav class="flex-1 p-4 space-y-2 mt-4">
        <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Main Menu</p>

        <a href="../pages/dashboard.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all duration-200 group <?php echo getNavClasses('dashboard.php', $current_page); ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5 <?php echo ($current_page == 'dashboard.php') ? 'text-blue-600' : 'group-hover:text-blue-600'; ?>"></i>
            <span class="<?php echo ($current_page == 'dashboard.php') ? 'font-semibold text-blue-600' : 'font-medium'; ?>">Dashboard</span>
        </a>

        <a href="../pages/section.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all duration-200 group <?php echo getNavClasses('section.php', $current_page); ?>">
            <i data-lucide="layers" class="w-5 h-5 <?php echo ($current_page == 'section.php') ? 'text-blue-600' : 'group-hover:text-blue-600'; ?>"></i>
            <span class="<?php echo ($current_page == 'section.php') ? 'font-semibold text-blue-600' : 'font-medium'; ?>">Section Management</span>
        </a>

        <a href="../pages/students.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all duration-200 group <?php echo getNavClasses('students.php', $current_page); ?>">
            <i data-lucide="users" class="w-5 h-5 <?php echo ($current_page == 'students.php') ? 'text-blue-600' : 'group-hover:text-blue-600'; ?>"></i>
            <span class="<?php echo ($current_page == 'students.php') ? 'font-semibold text-blue-600' : 'font-medium'; ?>">Students</span>
        </a>

        <a href="../pages/attendance.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all duration-200 group <?php echo getNavClasses('attendance.php', $current_page); ?>">
            <i data-lucide="user-check" class="w-5 h-5 <?php echo ($current_page == 'attendance.php') ? 'text-blue-600' : 'group-hover:text-blue-600'; ?>"></i>
            <span class="<?php echo ($current_page == 'attendance.php') ? 'font-semibold text-blue-600' : 'font-medium'; ?>">Attendance</span>
        </a>
    </nav>

    <div class="p-4 border-t border-gray-100">
        <button onclick="openLogoutModal()" class="w-full flex items-center space-x-3 p-3 rounded-xl transition-all duration-200 text-slate-500 hover:bg-red-50 hover:text-red-500 group">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span class="font-medium text-sm">Sign Out Account</span>
        </button>
    </div>
</div>

<!-- TOPBAR-->
<div class="ml-64 bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-40">
    <div class="flex justify-between items-center px-8 py-4">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-bold text-gray-800"><?php echo $title ?></h1>
        </div>

        <div class="flex items-center gap-6">
            <!-- Search Bar -->
            <div class="hidden md:flex items-center bg-gray-100 px-4 py-2 rounded-xl border border-gray-200 hover:border-blue-300 focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-500/10 transition-all">
                <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                <input type="text" placeholder="Search students, sections..." class="bg-transparent border-none focus:ring-0 text-sm ml-2 w-64">
            </div>

            <!-- Notification Bell -->
            <button class="relative p-2 rounded-xl hover:bg-gray-100 transition-colors group">
                <i data-lucide="bell" class="w-5 h-5 text-gray-500 group-hover:text-blue-600 transition-colors"></i>
                <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
            </button>

            <!-- User Profile -->
            <div class="flex items-center gap-3 border-l pl-6 border-gray-200">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-gray-800 leading-none">
                        <?php
                        echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : "Guest User";
                        ?>
                    </p>
                    <p class="text-[10px] text-blue-600 font-medium uppercase mt-1">Faculty Member</p>
                </div>
                <div class="relative">
                    <img src="../assets/image/myformalpic.jpg" class="w-10 h-10 rounded-xl border-2 border-blue-100 shadow-sm object-cover">
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- logout Modal -->
<div id="logoutmodal" class="fixed inset-0 bg-slate-900/50 z-60 hidden items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 overflow-hidden animate-fade-in-up text-center">
        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-gradient-to-br from-red-100 to-red-200 mb-4">
            <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600"></i>
        </div>

        <h3 class="text-xl font-bold text-slate-900 mb-2">Are you sure?</h3>
        <p class="text-slate-500 mb-6 text-sm">You want to logout.</p>

        <div class="flex justify-center gap-3">
            <a href="../auth/logout.php">
                <button class=" bg-red-500 hover:bg-red-600 text-white font-semibold px-6 py-2.5 rounded-xl transition shadow-lg shadow-red-200">
                    Yes, logout
                </button>
            </a>
            <button type="button" onclick="closeLogoutModal()" class=" bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold  px-6   py-2.5 rounded-xl transition">
                Cancel
            </button>
        </div>
    </div>
</div>
<script src="../assets/js/modal.js"></script>
<script>
    function closeLogoutModal() {
        const modal = document.getElementById('logoutmodal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openLogoutModal() {
        const modal = document.getElementById('logoutmodal');
        modal.classList.add('flex');
        modal.classList.remove('hidden');
    }
</script>