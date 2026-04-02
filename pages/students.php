<?php
include '../config/db.php';
include '../config/auth.php';

$current_page = 'students.php';
$title = 'Student Management';

// ============================
// CREATE STUDENT
// ============================
if (isset($_POST['action_type']) && $_POST['action_type'] === 'add_student') {
    $section_id = intval($_POST['section_id']);
    $legal_name = trim($_POST['legal_name']);
    $preferred_name = trim($_POST['preferred_name']);
    $dob = $_POST['dob'];
    $gender = trim($_POST['gender']);
    $student_id = trim($_POST['student_id']) ?: 'STU-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $parent_contacts = trim($_POST['parent_contacts']);
    $emergency_contacts = trim($_POST['emergency_contacts']);
    $allergies = trim($_POST['allergies']);
    $grade_level = trim($_POST['grade_level']);

    $check = mysqli_query($conn, "SELECT id FROM students WHERE student_id='$student_id'");
    if (mysqli_num_rows($check) > 0) {
        $student_id = 'STU-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    $query = "INSERT INTO students (student_id, legal_name, preferred_name, dob, gender, address, phone, email, parent_contacts, emergency_contacts, allergies, grade_level, section_id)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssssssssssi", $student_id, $legal_name, $preferred_name, $dob, $gender, $address, $phone, $email, $parent_contacts, $emergency_contacts, $allergies, $grade_level, $section_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>showToast('Student added successfully');</script>";
    } else {
        echo "<script>showToast('Error adding student', 'error');</script>";
    }
    mysqli_stmt_close($stmt);
}

// ============================
// UPDATE STUDENT
// ============================
if (isset($_POST['action_type']) && $_POST['action_type'] === 'update_student') {
    $id = intval($_POST['edit_student_id']);
    $section_id = intval($_POST['section_id']);
    $legal_name = trim($_POST['legal_name']);
    $preferred_name = trim($_POST['preferred_name']);
    $dob = $_POST['dob'];
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $parent_contacts = trim($_POST['parent_contacts']);
    $emergency_contacts = trim($_POST['emergency_contacts']);
    $allergies = trim($_POST['allergies']);
    $grade_level = trim($_POST['grade_level']);

    $query = "UPDATE students SET legal_name=?, preferred_name=?, dob=?, gender=?, address=?, phone=?, email=?, parent_contacts=?, emergency_contacts=?, allergies=?, grade_level=?, section_id=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssssssssii", $legal_name, $preferred_name, $dob, $gender, $address, $phone, $email, $parent_contacts, $emergency_contacts, $allergies, $grade_level, $section_id, $id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>showToast('Student updated successfully');</script>";
    } else {
        echo "<script>showToast('Error updating student', 'error');</script>";
    }
    mysqli_stmt_close($stmt);
}

// ============================
// DELETE STUDENT
// ============================
if (isset($_POST['action_type']) && $_POST['action_type'] === 'delete_student') {
    $id = intval($_POST['delete_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>showToast('Student deleted successfully');</script>";
    } else {
        echo "<script>showToast('Error deleting student', 'error');</script>";
    }
    mysqli_stmt_close($stmt);
}

// ============================
// FETCH STUDENTS (AJAX)
// ============================
if (isset($_GET['action_type']) && $_GET['action_type'] === 'fetch_students') {
    header('Content-Type: application/json');
    $search = $_GET['search'] ?? "";
    $section_filter = $_GET['section'] ?? "";
    $grade_filter = $_GET['grade'] ?? "";
    $gender_filter = $_GET['gender'] ?? "";

    $query = "SELECT s.*, sec.section_name FROM students s LEFT JOIN sections sec ON s.section_id = sec.sectionID WHERE 1=1";
    if (!empty($search)) {
        $query .= " AND (s.legal_name LIKE '%$search%' OR s.student_id LIKE '%$search%' OR s.preferred_name LIKE '%$search%')";
    }
    if (!empty($section_filter)) {
        $query .= " AND s.section_id = " . intval($section_filter);
    }
    if (!empty($grade_filter)) {
        $query .= " AND s.grade_level = '$grade_filter'";
    }
    if (!empty($gender_filter)) {
        $query .= " AND s.gender = '$gender_filter'";
    }
    $query .= " ORDER BY s.legal_name ASC";

    $result = mysqli_query($conn, $query);
    $students = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
    echo json_encode($students);
    exit;
}

// ============================
// FETCH RECOMMENDATIONS (AJAX)
// ============================
if (isset($_GET['action_type']) && $_GET['action_type'] === 'fetch_recommendations') {
    header('Content-Type: application/json');

    // Warning students (no section assigned)
    $no_section = mysqli_query($conn, "SELECT * FROM students WHERE section_id = 0 OR section_id IS NULL LIMIT 5");
    $no_section_students = [];
    while ($row = mysqli_fetch_assoc($no_section)) {
        $no_section_students[] = $row;
    }

    // Recently added students (last 7 days)
    $recent = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC LIMIT 5");
    $recent_students = [];
    while ($row = mysqli_fetch_assoc($recent)) {
        $recent_students[] = $row;
    }

    // High risk students (have allergies or chronic issues noted)
    $high_risk = mysqli_query($conn, "SELECT * FROM students WHERE allergies != '' OR chronic_issues != '' LIMIT 5");
    $high_risk_students = [];
    while ($row = mysqli_fetch_assoc($high_risk)) {
        $high_risk_students[] = $row;
    }

    echo json_encode([
        'no_section' => $no_section_students,
        'recent' => $recent_students,
        'high_risk' => $high_risk_students
    ]);
    exit;
}

// ============================
// GET SINGLE STUDENT (AJAX)
// ============================
if (isset($_GET['action_type']) && $_GET['action_type'] === 'get_student') {
    header('Content-Type: application/json');
    $id = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM students WHERE id = $id");
    $student = mysqli_fetch_assoc($result);
    echo json_encode($student);
    exit;
}

// ============================
// LOAD DATA FOR PAGE
// ============================
// Get sections for dropdown
$sections = mysqli_query($conn, "SELECT * FROM sections ORDER BY section_name ASC");

// Get all students for initial load
$all_students = mysqli_query($conn, "SELECT s.*, sec.section_name FROM students s LEFT JOIN sections sec ON s.section_id = sec.sectionID ORDER BY s.legal_name ASC");
?>
<!DOCTYPE html>
<html class="h-full">

<head>
    <title>Students | ClassOptima</title>
    <link rel="stylesheet" href="../assets/css/output.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
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

        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            padding: 16px 24px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 14px;
            z-index: 9999;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .toast-success {
            background: #10b981;
            color: white;
        }

        .toast-error {
            background: #ef4444;
            color: white;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <?php include '../includes/side-top-bar.php'; ?>

    <main class="ml-64 pt-0 p-8">
        <!-- Page Header -->
        <div class="mb-8 animate-fade-in-up mt-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">Student Management</h1>
                    <p class="text-slate-500 text-sm mt-1">Manage all students, search, filter and view recommendations.</p>
                </div>
                <button onclick="openAddModal()" class="bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-semibold px-6 py-3 rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    Add Student
                </button>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white rounded-2xl p-6 shadow-lg shadow-blue-100/50 border border-blue-50 mb-6 animate-fade-in-up animate-delay-100">
            <div class="flex flex-wrap gap-4 items-end">
                <!-- Search -->
                <div class="flex-1 min-w-[250px]">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Search</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                        <input type="text" id="searchInput" placeholder="Search by name or student ID..."
                            class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                    </div>
                </div>
                <!-- Section Filter -->
                <div class="w-48">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Section</label>
                    <select id="sectionFilter" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                        <option value="">All Sections</option>
                        <?php while ($sec = mysqli_fetch_assoc($sections)): ?>
                            <option value="<?php echo $sec['sectionID']; ?>"><?php echo htmlspecialchars($sec['section_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <!-- Grade Filter -->
                <div class="w-36">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Grade</label>
                    <select id="gradeFilter" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                        <option value="">All Grades</option>
                        <option value="7">Grade 7</option>
                        <option value="8">Grade 8</option>
                        <option value="9">Grade 9</option>
                        <option value="10">Grade 10</option>
                        <option value="11">Grade 11</option>
                        <option value="12">Grade 12</option>
                    </select>
                </div>
                <!-- Gender Filter -->
                <div class="w-36">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Gender</label>
                    <select id="genderFilter" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                        <option value="">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <!-- Reset Button -->
                <button onclick="resetFilters()" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-slate-600 font-medium rounded-xl transition-all flex items-center gap-2">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    Reset
                </button>
            </div>
        </div>

        <!-- Recommendations Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Unassigned Students Warning -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-6 border border-amber-100 animate-fade-in-up animate-delay-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl shadow-lg shadow-amber-200">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-amber-800">Needs Section</h3>
                        <p class="text-xs text-amber-600">Students without a section</p>
                    </div>
                </div>
                <div id="noSectionList" class="space-y-2">
                    <p class="text-sm text-amber-600 italic">Loading...</p>
                </div>
            </div>

            <!-- Recently Added -->
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-6 border border-blue-100 animate-fade-in-up animate-delay-200">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl shadow-lg shadow-blue-200">
                        <i data-lucide="user-plus" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-blue-800">Recently Added</h3>
                        <p class="text-xs text-blue-600">Latest student entries</p>
                    </div>
                </div>
                <div id="recentList" class="space-y-2">
                    <p class="text-sm text-blue-600 italic">Loading...</p>
                </div>
            </div>

            <!-- Medical Alerts -->
            <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-2xl p-6 border border-red-100 animate-fade-in-up animate-delay-300">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-gradient-to-br from-red-500 to-pink-500 rounded-xl shadow-lg shadow-red-200">
                        <i data-lucide="heart-pulse" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-red-800">Medical Notes</h3>
                        <p class="text-xs text-red-600">Allergies or conditions</p>
                    </div>
                </div>
                <div id="medicalList" class="space-y-2">
                    <p class="text-sm text-red-600 italic">Loading...</p>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div class="bg-white rounded-2xl shadow-lg shadow-blue-100/50 border border-blue-50 overflow-hidden animate-fade-in-up animate-delay-200">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-bold text-slate-800">All Students</h2>
                <p class="text-sm text-slate-500">Showing <span id="studentCount">0</span> students</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Section</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Grade</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Gender</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Medical Notes</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody" class="divide-y divide-gray-100">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
            <!-- Empty State -->
            <div id="emptyState" class="hidden p-12 text-center">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="users" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-700">No students found</h3>
                <p class="text-slate-500 text-sm mt-1">Try adjusting your search or filters, or add a new student.</p>
            </div>
        </div>
    </main>

    <div id="addStudentsModal" class="fixed inset-0 bg-slate-900/50 z-50 hidden items-center justify-center backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden animate-fade-in-up flex flex-col">

            <!-- Modal Header -->
            <div class="bg-gradient-to-r  from-blue-500 to-cyan-500  p-5 text-white flex justify-between items-center flex-shrink-0">
                <h3 class="font-bold flex items-center gap-2 text-lg">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>Student Management</span>
                </h3>
                <button onclick="closeModal()" class="hover:bg-white/20 p-2 rounded-xl transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <!-- ============================================================
                     MANUAL ADD/EDIT STUDENT FORM
                     Full student profile with all fields
                     ============================================================ -->
            <div id="manualAddSection" class="flex ">
                <form method="POST" id="manualStudentForm" class="space-y-6">
                    <input type="hidden" name="action_type" id="form_action_type" value="add_student">
                    <input type="hidden" name="edit_student_id" id="edit_student_id">

                    <!-- Personal Details Section -->
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-slate-100 px-4 py-3 border-b border-slate-200">
                            <h4 class="font-semibold text-slate-700 flex items-center gap-2">
                                <i data-lucide="user" class="w-4 h-4 text-slate-500"></i> Personal Details
                            </h4>
                        </div>
                        <div class="p-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Legal Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="legal_name" id="legal_name" required
                                        placeholder="Complete legal name"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Preferred Name</label>
                                    <input type="text" name="preferred_name" id="preferred_name"
                                        placeholder="Nickname or preferred name"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Date of Birth <span class="text-red-500">*</span></label>
                                    <input type="date" name="dob" id="dob" required
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Gender <span class="text-red-500">*</span></label>
                                    <select name="gender" id="gender" required
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all cursor-pointer">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Student ID</label>
                                    <input type="text" name="student_id" id="student_id"
                                        placeholder="School ID number"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Grade Level</label>
                                    <select name="grade_level" id="grade_level"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all cursor-pointer">
                                        <option value="">Select Grade</option>
                                        <option value="7">Grade 7</option>
                                        <option value="8">Grade 8</option>
                                        <option value="9">Grade 9</option>
                                        <option value="10">Grade 10</option>
                                        <option value="11">Grade 11</option>
                                        <option value="12">Grade 12</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Section <span class="text-red-500">*</span></label>
                                    <select name="section_id" id="section_id_select" required
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all cursor-pointer">
                                        <option value="">Select Section</option>
                                        <?php
                                        mysqli_data_seek($sections, 0);
                                        while ($sec = mysqli_fetch_assoc($sections)):
                                        ?>
                                            <option value="<?php echo $sec['sectionID']; ?>"><?php echo htmlspecialchars($sec['section_name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-slate-100 px-4 py-3 border-b border-slate-200">
                            <h4 class="font-semibold text-slate-700 flex items-center gap-2">
                                <i data-lucide="phone" class="w-4 h-4 text-slate-500"></i> Contact Information
                            </h4>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <label class="text-sm font-semibold text-slate-700 mb-2 block">Home Address</label>
                                <input type="text" name="address" id="address"
                                    placeholder="Complete home address"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Phone Number</label>
                                    <input type="text" name="phone" id="phone"
                                        placeholder="Contact number"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Email Address</label>
                                    <input type="email" name="email" id="email"
                                        placeholder="student@email.com"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Family & Emergency Contacts Section -->
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-slate-100 px-4 py-3 border-b border-slate-200">
                            <h4 class="font-semibold text-slate-700 flex items-center gap-2">
                                <i data-lucide="heart" class="w-4 h-4 text-slate-500"></i> Family & Emergency Contacts
                            </h4>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <label class="text-sm font-semibold text-slate-700 mb-2 block">Parent/Guardian Contacts</label>
                                <textarea name="parent_contacts" id="parent_contacts" rows="2"
                                    placeholder="Names and contact numbers of parents/guardians"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all resize-none"></textarea>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700 mb-2 block">Emergency Contact</label>
                                <textarea name="emergency_contacts" id="emergency_contacts" rows="2"
                                    placeholder="Secondary emergency contacts"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all resize-none"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Information Section -->
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-slate-100 px-4 py-3 border-b border-slate-200">
                            <h4 class="font-semibold text-slate-700 flex items-center gap-2">
                                <i data-lucide="stethoscope" class="w-4 h-4 text-slate-500"></i> Medical Information
                            </h4>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <label class="text-sm font-semibold text-slate-700 mb-2 block">Allergies</label>
                                <input type="text" name="allergies" id="allergies"
                                    placeholder="Food, medicine, or environmental allergies"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Chronic Conditions</label>
                                    <input type="text" name="chronic_issues" id="chronic_issues"
                                        placeholder="Asthma, diabetes, etc."
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Dietary Restrictions</label>
                                    <input type="text" name="dietary_restrictions" id="dietary_restrictions"
                                        placeholder="Vegetarian, halal, etc."
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Background Section -->
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-slate-100 px-4 py-3 border-b border-slate-200">
                            <h4 class="font-semibold text-slate-700 flex items-center gap-2">
                                <i data-lucide="graduation-cap" class="w-4 h-4 text-slate-500"></i> Academic Background
                            </h4>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <label class="text-sm font-semibold text-slate-700 mb-2 block">Previous School</label>
                                <input type="text" name="previous_school" id="previous_school"
                                    placeholder="Name of previously enrolled school"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Academic Interests</label>
                                    <input type="text" name="interests" id="interests"
                                        placeholder="Favorite subjects or areas of interest"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                        <button type="button" onclick="switchStudentTab('list')"
                            class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit"
                            id="formSubmitBtn"
                            class="px-6 py-2.5 bg-gradient-to-r  from-blue-500 to-cyan-500  hover:from-purple-500 hover:to-indigo-400 text-white font-bold rounded-xl shadow-lg shadow-purple-200 hover:shadow-purple-300 transition-all flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Add Student</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-slate-900/50 z-[60] hidden items-center justify-center backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-gradient-to-br from-red-100 to-red-200 mb-4">
                <i data-lucide="trash-2" class="h-6 w-6 text-red-600"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Delete Student?</h3>
            <p class="text-slate-500 mb-6 text-sm">This action cannot be undone. The student will be permanently removed.</p>
            <form method="POST" id="deleteForm" class="flex justify-center gap-3">
                <input type="hidden" name="action_type" value="delete_student">
                <input type="hidden" name="delete_id" id="deleteId" value="">
                <button type="button" onclick="closeDeleteModal()" class="bg-gray-100 hover:bg-gray-200 text-slate-700 font-semibold px-6 py-2.5 rounded-xl transition">Cancel</button>
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-semibold px-6 py-2.5 rounded-xl transition shadow-lg shadow-red-200">Delete</button>
            </form>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast toast-success hidden"></div>

    <script src="../assets/js/toast.js"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Re-load sections dropdown for form
        let sectionsData = [];

        // Load recommendations
        function loadRecommendations() {
            fetch('students.php?action_type=fetch_recommendations')
                .then(r => r.json())
                .then(data => {
                    // No section students
                    const noSectionList = document.getElementById('noSectionList');
                    if (data.no_section.length > 0) {
                        noSectionList.innerHTML = data.no_section.map(s => `
                            <div class="flex items-center gap-2 p-2 bg-white/60 rounded-lg">
                                <div class="w-8 h-8 bg-amber-200 rounded-full flex items-center justify-center text-amber-700 font-bold text-xs">
                                    ${s.legal_name.charAt(0)}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-amber-800 truncate">${s.legal_name}</p>
                                    <p class="text-xs text-amber-600">${s.student_id || 'No ID'}</p>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        noSectionList.innerHTML = '<p class="text-sm text-amber-600 italic">All students have sections</p>';
                    }

                    // Recent students
                    const recentList = document.getElementById('recentList');
                    recentList.innerHTML = data.recent.map(s => `
                        <div class="flex items-center gap-2 p-2 bg-white/60 rounded-lg">
                            <div class="w-8 h-8 bg-blue-200 rounded-full flex items-center justify-center text-blue-700 font-bold text-xs">
                                ${s.legal_name.charAt(0)}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-blue-800 truncate">${s.legal_name}</p>
                                <p class="text-xs text-blue-600">${s.section_name || 'No section'}</p>
                            </div>
                        </div>
                    `).join('');

                    // Medical notes
                    const medicalList = document.getElementById('medicalList');
                    if (data.high_risk.length > 0) {
                        medicalList.innerHTML = data.high_risk.map(s => `
                            <div class="flex items-center gap-2 p-2 bg-white/60 rounded-lg">
                                <div class="w-8 h-8 bg-red-200 rounded-full flex items-center justify-center text-red-700 font-bold text-xs">
                                    ${s.legal_name.charAt(0)}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-red-800 truncate">${s.legal_name}</p>
                                    <p class="text-xs text-red-600 truncate">${s.allergies || 'Has medical notes'}</p>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        medicalList.innerHTML = '<p class="text-sm text-red-600 italic">No medical alerts</p>';
                    }
                });
        }

        // Load students table
        function loadStudents() {
            const search = document.getElementById('searchInput').value;
            const section = document.getElementById('sectionFilter').value;
            const grade = document.getElementById('gradeFilter').value;
            const gender = document.getElementById('genderFilter').value;

            fetch(`students.php?action_type=fetch_students&search=${encodeURIComponent(search)}&section=${section}&grade=${grade}&gender=${gender}`)
                .then(r => r.json())
                .then(data => {
                    const tbody = document.getElementById('studentsTableBody');
                    const emptyState = document.getElementById('emptyState');
                    const studentCount = document.getElementById('studentCount');

                    studentCount.textContent = data.length;

                    if (data.length === 0) {
                        tbody.innerHTML = '';
                        emptyState.classList.remove('hidden');
                        return;
                    }

                    emptyState.classList.add('hidden');
                    tbody.innerHTML = data.map(s => `
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                        ${s.legal_name.charAt(0)}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800">${s.legal_name}</p>
                                        <p class="text-xs text-slate-500">${s.student_id || 'No ID'} ${s.preferred_name ? '• ' + s.preferred_name : ''}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full">
                                    ${s.section_name || 'Unassigned'}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">${s.grade_level ? 'Grade ' + s.grade_level : '-'}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">${s.gender || '-'}</td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-slate-600">${s.phone || '-'}</p>
                                <p class="text-xs text-slate-500">${s.email || '-'}</p>
                            </td>
                            <td class="px-6 py-4">
                                ${s.allergies ? `<span class="px-3 py-1 bg-red-50 text-red-600 text-xs font-medium rounded-full">${s.allergies}</span>` : '<span class="text-slate-400 text-xs">None</span>'}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="editStudent(${s.id})" class="p-2 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4 text-blue-600"></i>
                                    </button>
                                    <button onclick="confirmDelete(${s.id}, '${s.legal_name.replace(/'/g, "\\'")}')" class="p-2 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4 text-red-600"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');

                    lucide.createIcons();
                });
        }

        // Open Add Modal
        function openAddModal() {
            document.getElementById('form_action_type').value = 'add_student';
            document.getElementById('edit_student_id').value = '';
            document.getElementById('manualStudentForm').reset();
            document.getElementById('formSubmitBtn').innerHTML = '<i data-lucide="save" class="w-4 h-4"></i><span>Add Student</span>';
            lucide.createIcons();
            document.getElementById('addStudentsModal').classList.remove('hidden');
            document.getElementById('addStudentsModal').classList.add('flex');
        }

        // Edit Student
        function editStudent(id) {
            fetch(`students.php?action_type=get_student&id=${id}`)
                .then(r => r.json())
                .then(s => {
                    document.getElementById('form_action_type').value = 'update_student';
                    document.getElementById('edit_student_id').value = s.id;
                    document.getElementById('legal_name').value = s.legal_name || '';
                    document.getElementById('preferred_name').value = s.preferred_name || '';
                    document.getElementById('dob').value = s.dob || '';
                    document.getElementById('gender').value = s.gender || '';
                    document.getElementById('student_id').value = s.student_id || '';
                    document.getElementById('grade_level').value = s.grade_level || '';
                    document.getElementById('section_id_select').value = s.section_id || '';
                    document.getElementById('address').value = s.address || '';
                    document.getElementById('phone').value = s.phone || '';
                    document.getElementById('email').value = s.email || '';
                    document.getElementById('parent_contacts').value = s.parent_contacts || '';
                    document.getElementById('emergency_contacts').value = s.emergency_contacts || '';
                    document.getElementById('allergies').value = s.allergies || '';
                    document.getElementById('formSubmitBtn').innerHTML = '<i data-lucide="save" class="w-4 h-4"></i><span>Update Student</span>';
                    lucide.createIcons();
                    document.getElementById('addStudentsModal').classList.remove('hidden');
                    document.getElementById('addStudentsModal').classList.add('flex');
                });
        }

        // Close Modal
        function closeModal() {
            document.getElementById('addStudentsModal').classList.add('hidden');
            document.getElementById('addStudentsModal').classList.remove('flex');
        }

        // Delete Confirmation
        function confirmDelete(id, name) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }

        // Reset Filters
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('sectionFilter').value = '';
            document.getElementById('gradeFilter').value = '';
            document.getElementById('genderFilter').value = '';
            loadStudents();
        }

        // Event Listeners
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(loadStudents, 300);
        });
        document.getElementById('sectionFilter').addEventListener('change', loadStudents);
        document.getElementById('gradeFilter').addEventListener('change', loadStudents);
        document.getElementById('genderFilter').addEventListener('change', loadStudents);

        // Form Submit
        document.getElementById('manualStudentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('students.php', {
                    method: 'POST',
                    body: formData
                })
                .then(() => {
                    closeModal();
                    loadStudents();
                    loadRecommendations();
                });
        });

        // Delete Form Submit
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('students.php', {
                    method: 'POST',
                    body: formData
                })
                .then(() => {
                    closeDeleteModal();
                    loadStudents();
                    loadRecommendations();
                });
        });

        // Initial Load
        loadStudents();
        loadRecommendations();
    </script>
</body>

</html>