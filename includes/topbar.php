<div class="ml-64 bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-40 p-4 flex justify-between items-center px-8">
    <div class="flex items-center gap-4">
        <h1 class="text-xl font-bold text-gray-800">Dashboard Overview</h1>
    </div>

    <div class="flex items-center gap-6">
        <div class="hidden md:flex items-center bg-gray-100 px-3 py-1.5 rounded-lg border border-gray-200">
            <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
            <input type="text" placeholder="Search..." class="bg-transparent border-none focus:ring-0 text-sm ml-2">
        </div>

        <div class="flex items-center gap-3 border-l pl-6 border-gray-200">
            <div class="text-right">
                <p class="text-xs font-bold text-gray-800 leading-none">
                    <?php
                    echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : "Guest User";
                    ?>
                </p>
                <p class="text-[10px] text-blue-600 font-medium uppercase mt-1">Faculty Member</p>
            </div>
            <div class="relative">
                <img src="../assets/image/myformalpic.jpg" class="w-10 h-10 rounded-full border-2 border-blue-100 shadow-sm object-cover">
                <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
            </div>
        </div>
    </div>
</div>