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
    $activeClasses = "bg-blue-600/10 text-blue-400 border border-blue-600/20 shadow-sm shadow-blue-900/10";
    $inactiveClasses = "text-slate-400 hover:bg-slate-800 hover:text-whitet";

    return ($page == $current_page) ? $activeClasses : $inactiveClasses;
}
?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


<div class="w-64 h-screen bg-slate-900 text-slate-300 fixed flex flex-col border-r border-slate-800 shadow-xl">
    <div class="p-6 flex items-center space-x-3 border-b border-slate-800/50">
        <div class="bg-blue-600 p-2 rounded-lg shadow-lg shadow-blue-900/20">
            <i data-lucide="calendar-check" class="text-white w-6 h-6"></i>
        </div>
        <span class="text-lg font-bold tracking-tight text-white">
            Class<span class="text-blue-500">Optima</span>
        </span>
    </div>

    <nav class="flex-1 p-4 space-y-2 mt-4">
        <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Main Menu</p>

        <a href="../pages/dashboard.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all duration-200 group <?php echo getNavClasses('dashboard.php', $current_page); ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5 <?php echo ($current_page == 'dashboard.php') ? 'text-blue-400' : 'group-hover:text-blue-400'; ?>"></i>
            <span class="<?php echo ($current_page == 'dashboard.php') ? 'font-semibold' : 'font-medium'; ?>">Dashboard</span>
        </a>

        <a href="../pages/section.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all duration-200 group <?php echo getNavClasses('section.php', $current_page); ?>">
            <i data-lucide="layers" class="w-5 h-5 <?php echo ($current_page == 'section.php') ? 'text-blue-400' : 'group-hover:text-blue-400'; ?>"></i>
            <span class="<?php echo ($current_page == 'section.php') ? 'font-semibold' : 'font-medium'; ?>">Section Management</span>
        </a>

        <a href="../students/list.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all duration-200 group <?php echo getNavClasses('list.php', $current_page); ?>">
            <i data-lucide="users" class="w-5 h-5 <?php echo ($current_page == 'list.php') ? 'text-blue-400' : 'group-hover:text-blue-400'; ?>"></i>
            <span class="<?php echo ($current_page == 'list.php') ? 'font-semibold' : 'font-medium'; ?>">Students</span>
        </a>

        <a href="../attendance/view.php" class="flex items-center space-x-3 p-3 rounded-xl transition-all duration-200 group <?php echo getNavClasses('view.php', $current_page); ?>">
            <i data-lucide="user-check" class="w-5 h-5 <?php echo ($current_page == 'view.php') ? 'text-blue-400' : 'group-hover:text-blue-400'; ?>"></i>
            <span class="<?php echo ($current_page == 'view.php') ? 'font-semibold' : 'font-medium'; ?>">Attendance</span>
        </a>
    </nav>

    <div class="p-4 border-t border-slate-800/50">
        <button onclick="openLogoutModal()" class="w-full flex items-center space-x-3 p-3 rounded-xl transition-all duration-200 text-slate-400 hover:bg-red-500/10 hover:text-red-400 group">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span class="font-medium text-sm">Sign Out Account</span>
        </button>
    </div>


</div>
<script src="../assets/js/modal.js"></script>