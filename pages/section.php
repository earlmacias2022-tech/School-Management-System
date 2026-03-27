<?php
include '../config/db.php';

// --- INSERT LOGIC ---
if (isset($_POST['action_type']) && $_POST['action_type'] == 'create') {
    $name = $_POST['sec_name'];
    $subject = $_POST['sec_subject'];
    $location = $_POST['sec_location'];
    $time = $_POST['sec_time'];
    $school_year = $_POST['sec_year'];
    $grade_level = $_POST['sec_grade'];
    $strand = $_POST['sec_strand'];
    $days_array = isset($_POST['sec_days']) ? $_POST['sec_days'] : [];
    $days_string = implode(',', $days_array);

    $query = "INSERT INTO sections (section_name, subject, location, time, school_year, grade_level, strand, class_days)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssss", $name, $subject, $location, $time, $school_year, $grade_level, $strand, $days_string);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>showToast('Section created successfully');</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// --- UPDATE LOGIC ---
if (isset($_POST['action_type']) && $_POST['action_type'] == 'update') {
    $id = $_POST['section_id'];
    $name = $_POST['sec_name'];
    $subject = $_POST['sec_subject'];
    $location = $_POST['sec_location'];
    $time = $_POST['sec_time'];
    $school_year = $_POST['sec_year'];
    $grade_level = $_POST['sec_grade'];
    $strand = $_POST['sec_strand'];
    $days_array = isset($_POST['sec_days']) ? $_POST['sec_days'] : [];
    $days_string = implode(',', $days_array);

    $query = "UPDATE sections
              SET section_name=?, subject=?, location=?, time=?, school_year=?, grade_level=?, strand=?, class_days=?
              WHERE sectionID=?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssssssi", $name, $subject, $location, $time, $school_year, $grade_level, $strand, $days_string, $id);
    mysqli_stmt_execute($stmt);
    $sections = mysqli_query($conn, "SELECT * FROM sections ORDER BY sectionID DESC");
}

// Delete Logic
if (isset($_POST['action']) && $_POST['action'] == 'delete_section') {
    $id_to_delete = intval($_POST['section_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM sections WHERE sectionID = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_to_delete);
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>showToast('Section deleted successfully');</script>";
    }
    mysqli_stmt_close($stmt);
    $sections = mysqli_query($conn, "SELECT * FROM sections ORDER BY sectionID DESC");
}

// Search and Filter Query
$search = $_GET['search'] ?? "";
$grade_level = $_GET['grade_level'] ?? "";
$school_year = $_GET['school_year'] ?? "";
$class_day = $_GET['class_day'] ?? "";
$strand = $_GET['strand'] ?? "";

$query = "SELECT * FROM sections WHERE 1=1";
if (!empty($search)) {
    $query .= " AND section_name LIKE '%$search%'";
}
if (!empty($grade_level)) {
    $query .= " AND grade_level = '$grade_level'";
}
if (!empty($school_year)) {
    $query .= " AND school_year = '$school_year'";
}
if (!empty($strand)) {
    $query .= " AND strand = '$strand'";
}
if (!empty($class_day)) {
    $query .= " AND FIND_IN_SET('$class_day', class_days)";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html class="h-full">

<head>
    <title>Sections | Attendance Pro</title>
    <link rel="stylesheet" href="../assets/css/output.css">
    <script src="https://unpkg.com/lucide@latest"></script>
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
    </style>
</head>

<body class="bg-gray-50 min-h-screen relative overflow-x-hidden">
    <!-- Decorative Background -->
    <div class="bg-float-1"></div>
    <div class="bg-float-2"></div>

    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/topbar.php'; ?>

    <main class="ml-64 p-8 relative z-10">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8 animate-fade-in-up">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 mb-2">Section Management</h1>
                <p class="text-slate-500">Add and manage your class sections</p>
            </div>
            <button onclick="toggleModal(true)" class="bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 transition shadow-lg shadow-blue-200 hover:shadow-blue-300 hover:-translate-y-0.5">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                Add Section
            </button>
        </div>

        <!-- Filters -->
        <form method="GET" class="bg-white rounded-2xl p-5 shadow-lg shadow-blue-100/50 border border-blue-50 mb-6 animate-fade-in-up animate-delay-100">
            <div class="flex flex-wrap gap-4 items-center">
                <!-- Grade Level -->
                <select name="grade_level" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-700 cursor-pointer outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all">
                    <option value="" <?= ($_GET['grade_level'] ?? '') == '' ? 'selected' : '' ?>>Grade Level</option>
                    <option value="Grade 11" <?= ($_GET['grade_level'] ?? '') == 'Grade 11' ? 'selected' : '' ?>>Grade 11</option>
                    <option value="Grade 12" <?= ($_GET['grade_level'] ?? '') == 'Grade 12' ? 'selected' : '' ?>>Grade 12</option>
                </select>

                <!-- School Year -->
                <select name="school_year" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-700 cursor-pointer outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all">
                    <option value="" <?= ($_GET['school_year'] ?? '') == '' ? 'selected' : '' ?>>School Year</option>
                    <option value="2025-2026" <?= ($_GET['school_year'] ?? '') == '2025-2026' ? 'selected' : '' ?>>2025-2026</option>
                    <option value="2026-2027" <?= ($_GET['school_year'] ?? '') == '2026-2027' ? 'selected' : '' ?>>2026-2027</option>
                </select>

                <!-- Class Day -->
                <select name="class_day" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-700 cursor-pointer outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all">
                    <option value="" <?= ($_GET['class_day'] ?? '') == '' ? 'selected' : '' ?>>Class Day</option>
                    <option value="M" <?= ($_GET['class_day'] ?? '') == 'M' ? 'selected' : '' ?>>Monday</option>
                    <option value="T" <?= ($_GET['class_day'] ?? '') == 'T' ? 'selected' : '' ?>>Tuesday</option>
                    <option value="W" <?= ($_GET['class_day'] ?? '') == 'W' ? 'selected' : '' ?>>Wednesday</option>
                    <option value="TH" <?= ($_GET['class_day'] ?? '') == 'TH' ? 'selected' : '' ?>>Thursday</option>
                    <option value="F" <?= ($_GET['class_day'] ?? '') == 'F' ? 'selected' : '' ?>>Friday</option>
                </select>

                <!-- Strand -->
                <select name="strand" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-700 cursor-pointer outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all">
                    <option value="" <?= ($_GET['strand'] ?? '') == '' ? 'selected' : '' ?>>Strand</option>
                    <option value="TVL" <?= ($_GET['strand'] ?? '') == 'TVL' ? 'selected' : '' ?>>TVL</option>
                    <option value="STEM" <?= ($_GET['strand'] ?? '') == 'STEM' ? 'selected' : '' ?>>STEM</option>
                    <option value="ABM" <?= ($_GET['strand'] ?? '') == 'ABM' ? 'selected' : '' ?>>ABM</option>
                    <option value="HUMMS" <?= ($_GET['strand'] ?? '') == 'HUMMS' ? 'selected' : '' ?>>HUMMS</option>
                    <option value="GAS" <?= ($_GET['strand'] ?? '') == 'GAS' ? 'selected' : '' ?>>GAS</option>
                </select>

                <!-- Search -->
                <div class="relative flex-1 min-w-[200px]">

                    <input type="text" name="search" id="search"
                        class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all"
                        placeholder="  Search sections here... "
                        value="<?= $_GET['search'] ?? '' ?>"
                        onkeyup="setTimeout(() => this.form.submit(), 1500)">
                </div>
            </div>
        </form>

        <!-- Sections List -->
        <div class="bg-white rounded-2xl shadow-lg shadow-blue-100/50 border border-blue-50 overflow-hidden animate-fade-in-up animate-delay-200">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h2 class="font-bold text-slate-800 text-lg">Sections List</h2>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full"><?php echo mysqli_num_rows($result); ?> Total</span>
                </div>
            </div>
            <div class="p-4 space-y-3">
                <?php if (mysqli_num_rows($result) == 0): ?>
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="folder-open" class="w-8 h-8 text-slate-400"></i>
                        </div>
                        <p class="text-slate-500">No sections found</p>
                    </div>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="flex items-center justify-between p-4 border border-slate-100 rounded-xl hover:bg-slate-50 hover:border-slate-200 transition-all group cursor-pointer">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                                    <i data-lucide="book-open" class="w-5 h-5 text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800"><?php echo $row['section_name']; ?></h4>
                                    <p class="text-xs text-slate-500">
                                        <span class="font-semibold text-blue-600"><?php echo $row['strand']; ?> <?php echo $row['grade_level']; ?></span>
                                        <span class="mx-1">•</span>
                                        <?php echo $row['subject']; ?>
                                        <span class="mx-1">•</span>
                                        <?php echo $row['class_days']; ?>
                                        <span class="mx-1">•</span>
                                        <?php echo $row['time']; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                <button onclick='openEditModal(<?php echo json_encode($row); ?>)' class="p-2.5 hover:bg-blue-50 rounded-xl border border-transparent hover:border-blue-100 text-blue-600 cursor-pointer transition-all">
                                    <i data-lucide="pen" class="w-4 h-4"></i>
                                </button>
                                <button onclick='openViewModal(<?php echo json_encode($row); ?>)' class="p-2.5 hover:bg-green-50 rounded-xl border border-transparent hover:border-green-100 text-green-600 cursor-pointer transition-all">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                                <button onclick="openDeleteModal(<?php echo $row['sectionID']; ?>)" class="p-2.5 hover:bg-red-50 rounded-xl border border-transparent hover:border-red-100 text-red-500 cursor-pointer transition-all">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="addModal" class="fixed inset-0 bg-slate-900/50 z-50 hidden items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden animate-fade-in-up">
            <div class="bg-gradient-to-r from-blue-600 to-cyan-500 p-5 text-white flex justify-between items-center">
                <h3 class="font-bold flex items-center gap-2 text-lg">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    <span id="modalTitle">Add Section</span>
                </h3>
                <button onclick="toggleModal(false)" class="hover:bg-white/20 p-2 rounded-xl transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" class="p-10">
                <input type="hidden" name="action_type" id="action_type">
                <input type="hidden" name="section_id" id="section_id">
                <input type="hidden" name="form_mode" id="form_mode">

                <div class="space-y-5">
                    <div>
                        <label class="text-sm font-semibold text-slate-700 mb-2 block">Section Name</label>
                        <input type="text" name="sec_name" placeholder="Ex: ICT-12A" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-2 block">Subject</label>
                            <input type="text" name="sec_subject" placeholder="Programming" required
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-2 block">Location</label>
                            <input type="text" name="sec_location" placeholder="Room 101" required
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-2 block">School Year</label>
                            <select name="sec_year"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all cursor-pointer">
                                <option>2025-2026</option>
                                <option>2026-2027</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-2 block">Grade Level</label>
                            <select name="sec_grade"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all cursor-pointer">
                                <option>Grade 11</option>
                                <option>Grade 12</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-2 block">Strand</label>
                            <select name="sec_strand"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all cursor-pointer">
                                <option>ICT</option>
                                <option>STEM</option>
                                <option>ABM</option>
                                <option>HUMSS</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-2 block">Schedule (Time)</label>
                            <input type="text" name="sec_time" placeholder="07:30 AM - 09:30 AM" required
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-3 block">Class Days</label>
                            <div class="flex flex-wrap gap-3 pt-1">
                                <?php
                                $days = [
                                    'M' => 'Mon',
                                    'T' => 'Tue',
                                    'W' => 'Wed',
                                    'TH' => 'Thu',
                                    'F' => 'Fri'
                                ];
                                foreach ($days as $value => $label): ?>
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="checkbox" name="sec_days[]" value="<?php echo $value; ?>"
                                            class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer bg-slate-50">
                                        <span class="text-sm font-medium text-slate-600 group-hover:text-blue-600 transition-colors">
                                            <?php echo $label; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-10 border-t border-slate-100">
                    <button type="button" onclick="toggleModal(false)" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:shadow-blue-300 hover:-translate-y-0.5 transition-all cursor-pointer">
                        Save Section
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deletemodal" class="fixed inset-0 bg-slate-900/50 z-60 hidden items-center justify-center backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 overflow-hidden animate-fade-in-up text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-gradient-to-br from-red-100 to-red-200 mb-4">
                <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600"></i>
            </div>

            <h3 class="text-xl font-bold text-slate-900 mb-2">Are you sure?</h3>
            <p class="text-slate-500 mb-6 text-sm">You are about to delete this section. This action cannot be undone.</p>

            <form method="POST" action="section.php">
                <input type="hidden" name="action" value="delete_section">
                <input type="hidden" name="section_id" id="delete_id_input">

                <div class="flex justify-center gap-3">
                    <button type="submit" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2.5 rounded-xl transition shadow-lg shadow-red-200">
                        Yes, Delete
                    </button>
                    <button type="button" onclick="closeDeleteModal()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        lucide.createIcons();

        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast success show';
            setTimeout(() => {
                toast.className = 'toast';
            }, 3000);
        }

        function toggleModal(show) {
            const modal = document.getElementById('addModal');

            if (show) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                document.querySelector('#addModal form').reset();
                document.getElementById('section_id').value = "";
                document.getElementById('form_mode').value = "";
                document.getElementById('action_type').value = "create";

                document.querySelectorAll('#addModal input, #addModal select').forEach(el => {
                    el.disabled = false;
                });

                const btn = document.getElementById('submitBtn');
                btn.style.display = "block";
                btn.innerText = "Save Section";

                document.getElementById('modalTitle').innerText = "Add Section";

                lucide.createIcons();
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function openDeleteModal(id) {
            const idInput = document.getElementById('delete_id_input');
            idInput.value = id;

            const modal = document.getElementById('deletemodal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deletemodal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openEditModal(data) {
            toggleModal(true);

            document.getElementById('form_mode').value = "edit";
            document.getElementById('section_id').value = data.sectionID;
            document.getElementById('action_type').value = "update";
            document.getElementById('modalTitle').innerText = "Edit Section";

            document.querySelector('[name="sec_name"]').value = data.section_name;
            document.querySelector('[name="sec_subject"]').value = data.subject;
            document.querySelector('[name="sec_location"]').value = data.location;
            document.querySelector('[name="sec_time"]').value = data.time;
            document.querySelector('[name="sec_year"]').value = data.school_year;
            document.querySelector('[name="sec_grade"]').value = data.grade_level;
            document.querySelector('[name="sec_strand"]').value = data.strand;

            if (data.class_days) {
                let days = data.class_days.split(',');
                document.querySelectorAll('[name="sec_days[]"]').forEach(cb => {
                    cb.checked = days.includes(cb.value);
                });
            }

            const btn = document.getElementById('submitBtn');
            btn.innerText = "Update Section";
        }

        function openViewModal(data) {
            openEditModal(data);

            document.querySelectorAll('#addModal input, #addModal select').forEach(el => {
                el.disabled = true;
            });

            document.getElementById('submitBtn').style.display = "none";
            document.getElementById('modalTitle').innerText = "View Section";
        }
    </script>
</body>

</html>