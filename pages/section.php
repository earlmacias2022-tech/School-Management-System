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

// ============================================================
// STUDENT MANAGEMENT LOGIC - Full CRUD Operations
// Database assumed: students table has all fields listed below
// ============================================================
// ============================================================
// POLISHED STUDENT MANAGEMENT LOGIC - FULL CRUD + IMPORT/EXPORT
// ============================================================

// --------------------------------------
// HELPER FUNCTION: Generate Unique Student ID
// --------------------------------------
function generateUniqueStudentID($conn)
{
    do {
        $new_id = 'STU-' . str_pad(rand(1, 9999), 3, '0', STR_PAD_LEFT);
        $check = mysqli_query($conn, "SELECT id FROM students WHERE student_id='$new_id'");
    } while (mysqli_num_rows($check) > 0);
    return $new_id;
}

// ============================================================
// ADD STUDENT - Manual Entry
// ============================================================
if (isset($_POST['action_type']) && $_POST['action_type'] === 'add_student') {
    $section_id = intval($_POST['student_section_id']);

    // Personal Details
    $legal_name = trim($_POST['legal_name']);
    $preferred_name = trim($_POST['preferred_name']);
    $dob = $_POST['dob'];
    $gender = trim($_POST['gender']);
    $student_id = trim($_POST['student_id']) ?: generateUniqueStudentID($conn);

    // Contact Info
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    // Family & Emergency Contacts
    $parent_contacts = trim($_POST['parent_contacts']);
    $emergency_contacts = trim($_POST['emergency_contacts']);

    // Medical Info
    $allergies = trim($_POST['allergies']);
    $chronic_issues = trim($_POST['chronic_issues']);
    $dietary_restrictions = trim($_POST['dietary_restrictions']);

    // Academic Background
    $previous_school = trim($_POST['previous_school']);
    $grade_level = trim($_POST['grade_level']);
    $interests = trim($_POST['interests']);

    // ---------------------
    // Validation
    // ---------------------
    if (empty($legal_name) || empty($dob) || empty($gender) || empty($section_id)) {
        echo "<script>showToast('Legal name, birthdate, gender, and section are required', 'error');</script>";
        exit;
    }

    // Ensure unique student_id
    $check = mysqli_query($conn, "SELECT id FROM students WHERE student_id='$student_id'");
    if (mysqli_num_rows($check) > 0) {
        $student_id = generateUniqueStudentID($conn);
    }

    // Insert student
    $query = "INSERT INTO students 
        (legal_name, preferred_name, dob, gender, student_id, address, phone, email, parent_contacts, emergency_contacts, allergies, chronic_issues, dietary_restrictions, previous_school, grade_level, interests, section_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        echo "<script>showToast('Database error: " . mysqli_error($conn) . "', 'error');</script>";
        exit;
    }
    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssssssssssi",
        $legal_name,
        $preferred_name,
        $dob,
        $gender,
        $student_id,
        $address,
        $phone,
        $email,
        $parent_contacts,
        $emergency_contacts,
        $allergies,
        $chronic_issues,
        $dietary_restrictions,
        $previous_school,
        $grade_level,
        $interests,
        $section_id
    );
    if (mysqli_stmt_execute($stmt)) {
        $section_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT section_name FROM sections WHERE sectionID = $section_id"))['section_name'] ?? 'Unknown Section';
        echo "<script>showToast('Student added successfully to {$section_name}');</script>";
    } else {
        echo "<script>showToast('Error adding student: " . mysqli_stmt_error($stmt) . "', 'error');</script>";
    }
    mysqli_stmt_close($stmt);
}

// ============================================================
// EDIT STUDENT
// ============================================================
if (isset($_POST['action_type']) && $_POST['action_type'] === 'edit_student') {
    $student_id_db = intval($_POST['edit_student_id']);
    $section_id = intval($_POST['student_section_id']);

    // Personal Details
    $legal_name = trim($_POST['legal_name']);
    $preferred_name = trim($_POST['preferred_name']);
    $dob = $_POST['dob'];
    $gender = trim($_POST['gender']);
    $student_id = trim($_POST['student_id']) ?: generateUniqueStudentID($conn);

    // Contact Info
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    // Family & Emergency Contacts
    $parent_contacts = trim($_POST['parent_contacts']);
    $emergency_contacts = trim($_POST['emergency_contacts']);

    // Medical Info
    $allergies = trim($_POST['allergies']);
    $chronic_issues = trim($_POST['chronic_issues']);
    $dietary_restrictions = trim($_POST['dietary_restrictions']);

    // Academic Background
    $previous_school = trim($_POST['previous_school']);
    $grade_level = trim($_POST['grade_level']);
    $interests = trim($_POST['interests']);

    $query = "UPDATE students SET
        legal_name=?, preferred_name=?, dob=?, gender=?, student_id=?,
        address=?, phone=?, email=?,
        parent_contacts=?, emergency_contacts=?,
        allergies=?, chronic_issues=?, dietary_restrictions=?,
        previous_school=?, grade_level=?, interests=?, section_id=?
        WHERE id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssssssssssii",
        $legal_name,
        $preferred_name,
        $dob,
        $gender,
        $student_id,
        $address,
        $phone,
        $email,
        $parent_contacts,
        $emergency_contacts,
        $allergies,
        $chronic_issues,
        $dietary_restrictions,
        $previous_school,
        $grade_level,
        $interests,
        $section_id,
        $student_id_db
    );
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>showToast('Student updated successfully');</script>";
    } else {
        echo "<script>showToast('Error updating student: " . mysqli_stmt_error($stmt) . "', 'error');</script>";
    }
    mysqli_stmt_close($stmt);
}

// ============================================================
// DELETE STUDENT
// ============================================================
if (isset($_POST['action_type']) && $_POST['action_type'] === 'delete_student') {
    $student_id_db = intval($_POST['delete_student_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $student_id_db);
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>showToast('Student deleted successfully');</script>";
    } else {
        echo "<script>showToast('Error deleting student: " . mysqli_stmt_error($stmt) . "', 'error');</script>";
    }
    mysqli_stmt_close($stmt);
}

// ============================================================
// MOVE STUDENT TO ANOTHER SECTION
// ============================================================
if (isset($_POST['action_type']) && $_POST['action_type'] === 'move_student') {
    $student_id_db = intval($_POST['move_student_id']);
    $new_section_id = intval($_POST['new_section_id']);

    if ($new_section_id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE students SET section_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $new_section_id, $student_id_db);
        if (mysqli_stmt_execute($stmt)) {
            $section_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT section_name FROM sections WHERE sectionID = $new_section_id"))['section_name'] ?? 'Unknown Section';
            echo "<script>showToast('Student moved to {$section_name}');</script>";
        } else {
            echo "<script>showToast('Error moving student: " . mysqli_stmt_error($stmt) . "', 'error');</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

// ============================================================
// EXPORT STUDENTS TO CSV
// ============================================================
if (isset($_POST['action_type']) && $_POST['action_type'] === 'export_students') {
    $section_id = intval($_POST['export_section_id']);
    $section_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT section_name FROM sections WHERE sectionID = $section_id"))['section_name'] ?? 'Section';
    $result = mysqli_query($conn, "SELECT * FROM students WHERE section_id = $section_id");

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_' . preg_replace('/[^a-zA-Z0-9]/', '_', $section_name) . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, [
        'Student ID',
        'Legal Name',
        'Preferred Name',
        'Date of Birth',
        'Gender',
        'Address',
        'Phone',
        'Email',
        'Parent Contacts',
        'Emergency Contacts',
        'Allergies',
        'Chronic Issues',
        'Dietary Restrictions',
        'Previous School',
        'Grade Level',
        'Interests'
    ]);

    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['student_id'],
            $row['legal_name'],
            $row['preferred_name'],
            $row['dob'],
            $row['gender'],
            $row['address'],
            $row['phone'],
            $row['email'],
            $row['parent_contacts'],
            $row['emergency_contacts'],
            $row['allergies'],
            $row['chronic_issues'],
            $row['dietary_restrictions'],
            $row['previous_school'],
            $row['grade_level'],
            $row['interests']
        ]);
    }
    fclose($output);
    exit;
}

// ============================================================
// IMPORT STUDENTS - Bulk CSV/Excel
// ============================================================
if (isset($_POST['action_type']) && $_POST['action_type'] === 'import_students') {
    $section_id = intval($_POST['import_section_id']);
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] != 0) {
        echo "<script>showToast('No file uploaded', 'error');</script>";
        exit;
    }

    $file_tmp = $_FILES['excel_file']['tmp_name'];
    $file_ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, ['csv', 'xlsx'])) {
        echo "<script>showToast('Invalid file format', 'error');</script>";
        exit;
    }

    $handle = fopen($file_tmp, 'r');
    $header = fgetcsv($handle); // Skip header

    $imported = 0;
    $errors = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) >= 5) {
            $student_id = trim($row[0]) ?: generateUniqueStudentID($conn);
            $legal_name = trim($row[1]);
            if (empty($legal_name)) {
                $errors++;
                continue;
            }
            $preferred_name = $row[2] ?? '';
            $dob = $row[3] ?? '';
            $gender = in_array(strtolower($row[4]), ['male', 'm', 'female', 'f']) ?
                (in_array(strtolower($row[4]), ['male', 'm']) ? 'Male' : 'Female') : 'Male';
            $address = $row[5] ?? '';
            $phone = $row[6] ?? '';
            $email = $row[7] ?? '';
            $parent_contacts = $row[8] ?? '';
            $emergency_contacts = $row[9] ?? '';
            $allergies = $row[10] ?? '';
            $chronic_issues = $row[11] ?? '';
            $dietary_restrictions = $row[12] ?? '';
            $previous_school = $row[13] ?? '';
            $grade_level = $row[14] ?? '';
            $interests = $row[15] ?? '';

            $stmt = mysqli_prepare($conn, "INSERT INTO students 
                (student_id, legal_name, preferred_name, dob, gender, address, phone, email, parent_contacts, emergency_contacts, allergies, chronic_issues, dietary_restrictions, previous_school, grade_level, interests, section_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssssssssssi",
                $student_id,
                $legal_name,
                $preferred_name,
                $dob,
                $gender,
                $address,
                $phone,
                $email,
                $parent_contacts,
                $emergency_contacts,
                $allergies,
                $chronic_issues,
                $dietary_restrictions,
                $previous_school,
                $grade_level,
                $interests,
                $section_id
            );
            if (mysqli_stmt_execute($stmt)) {
                $imported++;
            } else {
                $errors++;
            }
            mysqli_stmt_close($stmt);
        }
    }
    fclose($handle);

    $section_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT section_name FROM sections WHERE sectionID = $section_id"))['section_name'] ?? 'Section';
    if ($imported) echo "<script>showToast('{$imported} student(s) imported to {$section_name}');</script>";
    if ($errors) echo "<script>showToast('{$errors} row(s) failed to import', 'error');</script>";
}

// ============================================================
// FETCH STUDENTS (AJAX endpoint) - Returns plain array, not JSON object
// ============================================================
if (isset($_GET['action_type']) && $_GET['action_type'] === 'fetch_students') {
    $section_id = intval($_GET['section_id']);
    $result = mysqli_query($conn, "SELECT * FROM students WHERE section_id = $section_id ORDER BY legal_name ASC");

    $students = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }

    header('Content-Type: application/json'); // optional for AJAX
    echo json_encode($students);
    exit;
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

        .toast.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen relative overflow-x-hidden">
    <!-- Decorative Background -->
    <div class="bg-float-1"></div>
    <div class="bg-float-2"></div>

    <?php include '../includes/side-top-bar.php'; ?>

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
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    </div>

                    <input type="text" name="search" id="search"
                        class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all"
                        placeholder="Search sections here..."
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
            <div class="p-4 space-y-3 max-h-82 overflow-y-auto">
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
                                <!-- ADD STUDENTS BUTTON -->
                                <button onclick='openAddStudentsModal(<?php echo json_encode($row); ?>)' class="p-2.5 hover:bg-purple-50 rounded-xl border border-transparent hover:border-purple-100 text-purple-600 cursor-pointer transition-all" title="Add Students">
                                    <i data-lucide="user-plus" class="w-4 h-4"></i>
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
                                <option>-- Select --</option>
                                <option>2025-2026</option>
                                <option>2026-2027</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-2 block">Grade Level</label>
                            <select name="sec_grade"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all cursor-pointer">
                                <option>-- Select --</option>
                                <option>Grade 11</option>
                                <option>Grade 12</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-2 block">Strand</label>
                            <select name="sec_strand"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/15 transition-all cursor-pointer">
                                <option>-- Select --</option>
                                <option>TVL</option>
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

    <!-- ============================================================
         FULL STUDENT MANAGEMENT MODAL
         Features: List View, Add/Edit/Import/Export/Move Students
         ============================================================ -->
    <div id="addStudentsModal" class="fixed inset-0 bg-slate-900/50 z-50 hidden items-center justify-center backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden animate-fade-in-up flex flex-col">

            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-500 p-5 text-white flex justify-between items-center flex-shrink-0">
                <h3 class="font-bold flex items-center gap-2 text-lg">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>Student Management</span>
                </h3>
                <button onclick="closeAddStudentsModal()" class="hover:bg-white/20 p-2 rounded-xl transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Section Context Banner -->
            <div class="bg-purple-50 border-b border-purple-100 px-5 py-3 flex-shrink-0">
                <p class="text-sm text-purple-700">
                    <i data-lucide="book-open" class="w-4 h-4 inline mr-1"></i>
                    Section: <span id="selectedSectionName" class="font-bold">Section Name</span>
                    <span class="mx-2">•</span>
                    <span id="studentCount" class="text-purple-600 font-medium">0 students</span>
                </p>
            </div>

            <!-- Action Buttons Bar -->
            <div class="px-5 py-3 border-b border-slate-200 bg-slate-50 flex flex-wrap gap-3 items-center flex-shrink-0">
                <button onclick="showAddStudentForm()"
                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-500 hover:from-purple-500 hover:to-indigo-400 text-white font-semibold rounded-xl shadow-sm hover:shadow transition-all flex items-center gap-2 text-sm">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Manually
                </button>
                <button onclick="switchStudentTab('import')"
                    class="px-4 py-2 bg-white border border-slate-200 hover:border-purple-300 hover:bg-purple-50 text-slate-700 font-semibold rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm">
                    <i data-lucide="file-up" class="w-4 h-4"></i> Import Excel
                </button>
                <button onclick="exportStudents()"
                    class="px-4 py-2 bg-white border border-slate-200 hover:border-green-300 hover:bg-green-50 text-slate-700 font-semibold rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm">
                    <i data-lucide="file-down" class="w-4 h-4"></i> Export Students
                </button>
                <div class="flex-1"></div>
                <!-- Search within modal -->
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="searchStudents" placeholder="Search students..."
                        class="pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all w-48"
                        oninput="filterStudents(this.value)">
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex border-b border-slate-200 flex-shrink-0">
                <button onclick="switchStudentTab('list')" id="tabList"
                    class="flex-1 px-4 py-3 text-sm font-semibold text-blue-600 border-b-2 border-blue-600 bg-blue-50/50 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="list" class="w-4 h-4"></i> Student List
                </button>
                <button onclick="switchStudentTab('manual')" id="tabManual"
                    class="flex-1 px-4 py-3 text-sm font-semibold text-slate-500 border-b-2 border-transparent hover:text-slate-700 hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i> Manual Add
                </button>
                <button onclick="switchStudentTab('import')" id="tabImport"
                    class="flex-1 px-4 py-3 text-sm font-semibold text-slate-500 border-b-2 border-transparent hover:text-slate-700 hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="file-up" class="w-4 h-4"></i> Import Excel
                </button>
            </div>

            <!-- Modal Body - Scrollable -->
            <div class="flex-1 overflow-y-auto p-5">

                <!-- ============================================================
                     STUDENT LIST VIEW - Shows all students in collapsible cards
                     ============================================================ -->
                <div id="studentListSection">
                    <!-- Empty State -->
                    <div id="emptyStudentsState" class="hidden text-center py-16">
                        <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="users" class="w-10 h-10 text-purple-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700 mb-2">No students in this section yet</h3>
                        <p class="text-slate-500 mb-6 max-w-sm mx-auto">Start by adding students manually or import from an Excel file.</p>
                        <button onclick="showAddStudentForm()" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-500 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                            <i data-lucide="plus-circle" class="w-4 h-4 inline mr-2"></i> Add First Student
                        </button>
                    </div>

                    <!-- Student Cards Container -->
                    <div id="studentCardsContainer" class="space-y-4">
                        <!-- Student cards will be dynamically inserted here -->
                    </div>
                </div>

                <!-- ============================================================
                     MANUAL ADD/EDIT STUDENT FORM
                     Full student profile with all fields
                     ============================================================ -->
                <div id="manualAddSection" class="hidden">
                    <form method="POST" id="manualStudentForm" class="space-y-6">
                        <input type="hidden" name="action_type" id="form_action_type" value="add_student">
                        <input type="hidden" name="student_section_id" id="student_section_id">
                        <input type="hidden" name="edit_student_id" id="edit_student_id">

                        <!-- Section Context in Form -->
                        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
                            <p class="text-sm text-purple-700">
                                <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                                Adding students to: <span id="formSectionName" class="font-bold"></span>
                            </p>
                        </div>

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
                                        <label class="text-sm font-semibold text-slate-700 mb-2 block">Current Grade Level</label>
                                        <input type="text" name="grade_level" id="grade_level"
                                            placeholder="Grade 11 or Year 1"
                                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                    </div>
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
                                class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-500 hover:from-purple-500 hover:to-indigo-400 text-white font-bold rounded-xl shadow-lg shadow-purple-200 hover:shadow-purple-300 transition-all flex items-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                <span>Add Student</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ============================================================
                     IMPORT EXCEL SECTION
                     ============================================================ -->
                <div id="importExcelSection" class="hidden">
                    <!-- Excel Upload Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <i data-lucide="file-spreadsheet" class="w-6 h-6 text-blue-600 mt-0.5"></i>
                            <div>
                                <h4 class="text-sm font-bold text-blue-800">Excel Template Format</h4>
                                <p class="text-xs text-blue-600 mt-1">Your Excel file should have these columns in order:</p>
                                <div class="mt-2 bg-blue-100 rounded-lg p-3 space-y-1">
                                    <code class="text-xs text-blue-700 block">A: Student ID | B: Legal Name | C: Preferred Name | D: DOB | E: Gender</code>
                                    <code class="text-xs text-blue-700 block">F: Address | G: Phone | H: Email | I: Parent Contacts | J: Emergency Contacts</code>
                                    <code class="text-xs text-blue-700 block">K: Allergies | L: Chronic Issues | M: Dietary Restrictions</code>
                                    <code class="text-xs text-blue-700 block">N: Previous School | O: Grade Level | P: Interests</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Download Template Button -->
                    <button onclick="downloadTemplate()"
                        class="w-full mb-4 py-3 px-4 bg-green-50 hover:bg-green-100 text-green-700 font-semibold rounded-xl border border-green-200 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="download" class="w-5 h-5"></i>
                        Download Template (.xlsx)
                    </button>

                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-200"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="px-4 text-sm text-slate-400 bg-white">or</span>
                        </div>
                    </div>

                    <!-- Excel Upload Form -->
                    <form method="POST" id="importStudentForm" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="action_type" value="import_students">
                        <input type="hidden" name="import_section_id" id="import_section_id">

                        <!-- File Input -->
                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-2 block">Upload Excel File</label>
                            <div class="relative">
                                <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.csv" required
                                    class="hidden"
                                    onchange="updateFileName(this)">
                                <label for="excel_file"
                                    class="flex items-center justify-center gap-4 w-full py-10 px-4 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-purple-400 hover:bg-purple-50/30 transition-all group">
                                    <div class="p-4 bg-slate-100 rounded-xl group-hover:bg-purple-100 transition-colors">
                                        <i data-lucide="upload-cloud" class="w-8 h-8 text-slate-500 group-hover:text-purple-600"></i>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-base font-semibold text-slate-700">Click to upload or drag file here</p>
                                        <p class="text-sm text-slate-400 mt-1">Supports .xlsx and .csv files</p>
                                    </div>
                                </label>
                            </div>
                            <!-- Selected File Name -->
                            <p id="selectedFileName" class="text-sm text-slate-500 mt-3 hidden">
                                <i data-lucide="file" class="w-4 h-4 inline mr-1"></i>
                                <span></span>
                            </p>
                        </div>

                        <!-- Upload Button -->
                        <div class="pt-4">
                            <button type="submit"
                                class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-500 hover:from-purple-500 hover:to-indigo-400 text-white font-bold rounded-xl shadow-lg shadow-purple-200 hover:shadow-purple-300 transition-all flex items-center justify-center gap-2">
                                <i data-lucide="file-up" class="w-5 h-5"></i>
                                <span>Import Students</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- ============================================================
         MOVE STUDENT MODAL - Transfer student to different section
         ============================================================ -->
    <div id="moveStudentModal" class="fixed inset-0 bg-slate-900/50 z-[60] hidden items-center justify-center backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-in-up">
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 p-5 text-white flex justify-between items-center">
                <h3 class="font-bold flex items-center gap-2 text-lg">
                    <i data-lucide="arrow-right-circle" class="w-5 h-5"></i>
                    <span>Move Student</span>
                </h3>
                <button onclick="closeMoveModal()" class="hover:bg-white/20 p-2 rounded-xl transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action_type" value="move_student">
                <input type="hidden" name="move_student_id" id="move_student_id">

                <div class="text-center mb-4">
                    <p class="text-slate-600">Transfer <span id="moveStudentName" class="font-bold text-slate-800"></span> to:</p>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-2 block">Select New Section</label>
                    <select name="new_section_id" id="new_section_select" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all cursor-pointer">
                        <option value="">-- Select Section --</option>
                        <?php
                        $all_sections = mysqli_query($conn, "SELECT sectionID, section_name, strand, grade_level FROM sections ORDER BY section_name");
                        while ($sec = mysqli_fetch_assoc($all_sections)) {
                            echo "<option value=\"{$sec['sectionID']}\">{$sec['section_name']} - {$sec['strand']} {$sec['grade_level']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeMoveModal()"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-white font-bold rounded-xl shadow-lg transition-all">
                        Move Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================
         DELETE STUDENT CONFIRMATION MODAL
         ============================================================ -->
    <div id="deleteStudentModal" class="fixed inset-0 bg-slate-900/50 z-[60] hidden items-center justify-center backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 overflow-hidden animate-fade-in-up text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-gradient-to-br from-red-100 to-red-200 mb-4">
                <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Delete Student?</h3>
            <p class="text-slate-500 mb-6 text-sm">You are about to delete <span id="deleteStudentName" class="font-semibold text-slate-700"></span>. This action cannot be undone.</p>
            <form method="POST">
                <input type="hidden" name="action_type" value="delete_student">
                <input type="hidden" name="delete_student_id" id="delete_student_id">
                <div class="flex justify-center gap-3">
                    <button type="button" onclick="closeDeleteStudentModal()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl transition">Cancel</button>
                    <button type="submit" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2.5 rounded-xl transition shadow-lg shadow-red-200">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        lucide.createIcons();

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type + ' show';
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

        // ============================================================
        // FULL STUDENT MANAGEMENT MODAL FUNCTIONS
        // ============================================================

        // Current state
        let currentSectionId = null;
        let currentStudents = [];
        let allStudents = [];

        /**
         * Opens the Student Management modal for a specific section
         * @param {Object} data - Section data object
         */
        function openAddStudentsModal(data) {
            const modal = document.getElementById('addStudentsModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            currentSectionId = data.sectionID;

            // Set section context
            const sectionName = `${data.section_name} - ${data.strand} ${data.grade_level}`;
            document.getElementById('selectedSectionName').textContent = sectionName;
            document.getElementById('formSectionName').textContent = sectionName;
            document.getElementById('student_section_id').value = data.sectionID;
            document.getElementById('import_section_id').value = data.sectionID;

            // Reset forms
            resetStudentForm();

            // Switch to list view by default and fetch students
            switchStudentTab('list');
            fetchStudents(data.sectionID);

            lucide.createIcons();
        }

        /**
         * Closes the Student Management modal
         */
        function closeAddStudentsModal() {
            const modal = document.getElementById('addStudentsModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentSectionId = null;
            currentStudents = [];
        }

        /**
         * Fetches students from the database via AJAX
         * @param {number} sectionId - The section ID to fetch students for
         */
        function fetchStudents(sectionId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `section.php?action_type=fetch_students&section_id=${sectionId}`, true);
            xhr.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    try {
                        allStudents = JSON.parse(this.responseText);
                        currentStudents = [...allStudents];
                        renderStudentList();
                    } catch (e) {
                        console.error('Error parsing students:', e);
                    }
                }
            };
            xhr.send();
        }

        /**
         * Renders the student list as collapsible cards
         */
        function renderStudentList() {
            const container = document.getElementById('studentCardsContainer');
            const emptyState = document.getElementById('emptyStudentsState');
            const countEl = document.getElementById('studentCount');

            countEl.textContent = `${currentStudents.length} student${currentStudents.length !== 1 ? 's' : ''}`;

            if (currentStudents.length === 0) {
                container.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            container.innerHTML = currentStudents.map(student => createStudentCard(student)).join('');

            // Re-initialize Lucide icons for new content
            lucide.createIcons();
        }

        /**
         * Creates HTML for a single student card (collapsible)
         * @param {Object} student - Student data object
         * @returns {string} HTML string
         */
        function createStudentCard(student) {
            const initials = (student.legal_name || 'NA').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            const genderColor = student.gender === 'Male' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700';
            const genderIcon = student.gender === 'Male' ?
                '<i data-lucide="user" class="w-4 h-4"></i>' :
                '<i data-lucide="user" class="w-4 h-4"></i>';

            return `
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-md transition-all student-card" data-student-id="${student.id}">
                    <!-- Card Header (Always Visible) -->
                    <div class="flex items-center justify-between p-4 cursor-pointer bg-white hover:bg-slate-50 transition-colors"
                         onclick="toggleStudentCard(${student.id})">
                        <div class="flex items-center gap-4">
                            <!-- Avatar -->
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center text-white font-bold text-sm shadow">
                                ${initials}
                            </div>
                            <!-- Basic Info -->
                            <div>
                                <h4 class="font-bold text-slate-800 flex items-center gap-2">
                                    ${student.legal_name || 'N/A'}
                                    <span class="px-2 py-0.5 ${genderColor} text-xs font-semibold rounded-full">${student.gender || 'N/A'}</span>
                                </h4>
                                <p class="text-sm text-slate-500">
                                    ${student.student_id ? 'ID: ' + student.student_id + ' • ' : ''}
                                    ${student.preferred_name ? ' aka ' + student.preferred_name : ''}
                                </p>
                            </div>
                        </div>
                        <!-- Actions & Expand Icon -->
                        <div class="flex items-center gap-2">
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-all student-actions" onclick="event.stopPropagation()">
                                <button onclick="editStudent(${student.id})"
                                    class="p-2 hover:bg-blue-50 rounded-lg border border-transparent hover:border-blue-100 text-blue-600 transition-all"
                                    title="Edit Student">
                                    <i data-lucide="pen" class="w-4 h-4"></i>
                                </button>
                                <button onclick="openMoveModal(${student.id}, '${escapeHtml(student.legal_name)}')"
                                    class="p-2 hover:bg-amber-50 rounded-lg border border-transparent hover:border-amber-100 text-amber-600 transition-all"
                                    title="Move to Section">
                                    <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>
                                </button>
                                <button onclick="openDeleteStudentModal(${student.id}, '${escapeHtml(student.legal_name)}')"
                                    class="p-2 hover:bg-red-50 rounded-lg border border-transparent hover:border-red-100 text-red-500 transition-all"
                                    title="Delete Student">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform student-chevron" id="chevron-${student.id}"></i>
                        </div>
                    </div>

                    <!-- Collapsible Details Section -->
                    <div class="hidden student-details border-t border-slate-100" id="details-${student.id}">
                        <div class="p-4 bg-slate-50 space-y-4">
                            <!-- Personal Details -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wide">Date of Birth</p>
                                    <p class="text-sm font-medium text-slate-700">${student.dob ? formatDate(student.dob) : 'N/A'}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wide">Student ID</p>
                                    <p class="text-sm font-medium text-slate-700">${student.student_id || 'N/A'}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wide">Grade Level</p>
                                    <p class="text-sm font-medium text-slate-700">${student.grade_level || 'N/A'}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wide">Interests</p>
                                    <p class="text-sm font-medium text-slate-700">${student.interests || 'N/A'}</p>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="border-t border-slate-200 pt-4">
                                <p class="text-xs text-slate-400 uppercase tracking-wide mb-2 flex items-center gap-1">
                                    <i data-lucide="phone" class="w-3 h-3"></i> Contact Information
                                </p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-xs text-slate-400">Address</p>
                                        <p class="text-sm text-slate-700">${student.address || 'N/A'}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400">Phone</p>
                                        <p class="text-sm text-slate-700">${student.phone || 'N/A'}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400">Email</p>
                                        <p class="text-sm text-slate-700">${student.email || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Family & Emergency -->
                            <div class="border-t border-slate-200 pt-4">
                                <p class="text-xs text-slate-400 uppercase tracking-wide mb-2 flex items-center gap-1">
                                    <i data-lucide="heart" class="w-3 h-3"></i> Family & Emergency Contacts
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-slate-400">Parent/Guardian</p>
                                        <p class="text-sm text-slate-700">${student.parent_contacts || 'N/A'}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400">Emergency Contact</p>
                                        <p class="text-sm text-slate-700">${student.emergency_contacts || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Medical Information -->
                            <div class="border-t border-slate-200 pt-4">
                                <p class="text-xs text-slate-400 uppercase tracking-wide mb-2 flex items-center gap-1">
                                    <i data-lucide="stethoscope" class="w-3 h-3"></i> Medical Information
                                </p>
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-xs text-slate-400">Allergies</p>
                                        <p class="text-sm text-slate-700">${student.allergies || 'None'}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400">Chronic Issues</p>
                                        <p class="text-sm text-slate-700">${student.chronic_issues || 'None'}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400">Dietary Restrictions</p>
                                        <p class="text-sm text-slate-700">${student.dietary_restrictions || 'None'}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Background -->
                            <div class="border-t border-slate-200 pt-4">
                                <p class="text-xs text-slate-400 uppercase tracking-wide mb-2 flex items-center gap-1">
                                    <i data-lucide="graduation-cap" class="w-3 h-3"></i> Academic Background
                                </p>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-slate-400">Previous School</p>
                                        <p class="text-sm text-slate-700">${student.previous_school || 'N/A'}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400">Academic Interests</p>
                                        <p class="text-sm text-slate-700">${student.interests || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Toggles the visibility of a student's detailed information
         * @param {number} studentId - Student ID
         */
        function toggleStudentCard(studentId) {
            const details = document.getElementById(`details-${studentId}`);
            const chevron = document.getElementById(`chevron-${studentId}`);
            const card = document.querySelector(`[data-student-id="${studentId}"]`);
            const actions = card.querySelector('.student-actions');

            // Toggle visibility
            details.classList.toggle('hidden');

            // Rotate chevron
            if (details.classList.contains('hidden')) {
                chevron.style.transform = 'rotate(0deg)';
                actions.classList.remove('opacity-100');
                actions.classList.add('opacity-0');
            } else {
                chevron.style.transform = 'rotate(180deg)';
                actions.classList.remove('opacity-0');
                actions.classList.add('opacity-100');
            }
        }

        /**
         * Shows the Add Student form
         */
        function showAddStudentForm() {
            resetStudentForm();
            switchStudentTab('manual');
        }

        /**
         * Resets the student form to its default state
         */
        function resetStudentForm() {
            document.getElementById('manualStudentForm').reset();
            document.getElementById('form_action_type').value = 'add_student';
            document.getElementById('edit_student_id').value = '';
            document.getElementById('formSubmitBtn').innerHTML = '<i data-lucide="save" class="w-4 h-4"></i><span>Add Student</span>';
            lucide.createIcons();
        }

        /**
         * Switches between tabs in the student management modal
         * @param {string} tab - 'list', 'manual', or 'import'
         */
        function switchStudentTab(tab) {
            const tabList = document.getElementById('tabList');
            const tabManual = document.getElementById('tabManual');
            const tabImport = document.getElementById('tabImport');
            const listSection = document.getElementById('studentListSection');
            const manualSection = document.getElementById('manualAddSection');
            const importSection = document.getElementById('importExcelSection');

            // Reset tab styles
            [tabList, tabManual, tabImport].forEach(t => {
                t.classList.remove('text-blue-600', 'border-blue-600', 'bg-blue-50/50');
                t.classList.add('text-slate-500', 'border-transparent');
            });

            // Hide all sections
            [listSection, manualSection, importSection].forEach(s => s.classList.add('hidden'));

            // Activate selected tab
            if (tab === 'list') {
                tabList.classList.remove('text-slate-500', 'border-transparent');
                tabList.classList.add('text-blue-600', 'border-blue-600', 'bg-blue-50/50');
                listSection.classList.remove('hidden');
                if (currentSectionId) fetchStudents(currentSectionId);
            } else if (tab === 'manual') {
                tabManual.classList.remove('text-slate-500', 'border-transparent');
                tabManual.classList.add('text-blue-600', 'border-blue-600', 'bg-blue-50/50');
                manualSection.classList.remove('hidden');
            } else if (tab === 'import') {
                tabImport.classList.remove('text-slate-500', 'border-transparent');
                tabImport.classList.add('text-blue-600', 'border-blue-600', 'bg-blue-50/50');
                importSection.classList.remove('hidden');
            }

            lucide.createIcons();
        }

        /**
         * Filters students based on search input
         * @param {string} query - Search query
         */
        function filterStudents(query) {
            query = query.toLowerCase().trim();
            if (!query) {
                currentStudents = [...allStudents];
            } else {
                currentStudents = allStudents.filter(s =>
                    (s.legal_name && s.legal_name.toLowerCase().includes(query)) ||
                    (s.student_id && s.student_id.toLowerCase().includes(query)) ||
                    (s.preferred_name && s.preferred_name.toLowerCase().includes(query)) ||
                    (s.email && s.email.toLowerCase().includes(query))
                );
            }
            renderStudentList();
        }

        /**
         * Populates the edit form with student data
         * @param {number} studentId - Student ID to edit
         */
        function editStudent(studentId) {
            const student = allStudents.find(s => s.id == studentId);
            if (!student) return;

            switchStudentTab('manual');

            // Set form mode to edit
            document.getElementById('form_action_type').value = 'edit_student';
            document.getElementById('edit_student_id').value = student.id;
            document.getElementById('formSubmitBtn').innerHTML = '<i data-lucide="save" class="w-4 h-4"></i><span>Update Student</span>';

            // Populate form fields
            document.getElementById('legal_name').value = student.legal_name || '';
            document.getElementById('preferred_name').value = student.preferred_name || '';
            document.getElementById('dob').value = student.dob || '';
            document.getElementById('gender').value = student.gender || '';
            document.getElementById('student_id').value = student.student_id || '';
            document.getElementById('address').value = student.address || '';
            document.getElementById('phone').value = student.phone || '';
            document.getElementById('email').value = student.email || '';
            document.getElementById('parent_contacts').value = student.parent_contacts || '';
            document.getElementById('emergency_contacts').value = student.emergency_contacts || '';
            document.getElementById('allergies').value = student.allergies || '';
            document.getElementById('chronic_issues').value = student.chronic_issues || '';
            document.getElementById('dietary_restrictions').value = student.dietary_restrictions || '';
            document.getElementById('previous_school').value = student.previous_school || '';
            document.getElementById('grade_level').value = student.grade_level || '';
            document.getElementById('interests').value = student.interests || '';

            lucide.createIcons();
        }

        /**
         * Opens the Move Student modal
         * @param {number} studentId - Student ID
         * @param {string} studentName - Student name for display
         */
        function openMoveModal(studentId, studentName) {
            const modal = document.getElementById('moveStudentModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('move_student_id').value = studentId;
            document.getElementById('moveStudentName').textContent = studentName;
            document.getElementById('new_section_select').value = '';
            lucide.createIcons();
        }

        /**
         * Closes the Move Student modal
         */
        function closeMoveModal() {
            const modal = document.getElementById('moveStudentModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        /**
         * Opens the Delete Student confirmation modal
         * @param {number} studentId - Student ID
         * @param {string} studentName - Student name for display
         */
        function openDeleteStudentModal(studentId, studentName) {
            const modal = document.getElementById('deleteStudentModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('delete_student_id').value = studentId;
            document.getElementById('deleteStudentName').textContent = studentName;
            lucide.createIcons();
        }

        /**
         * Closes the Delete Student confirmation modal
         */
        function closeDeleteStudentModal() {
            const modal = document.getElementById('deleteStudentModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        /**
         * Exports students to CSV
         */
        function exportStudents() {
            if (!currentSectionId) return;

            // Create a temporary form and submit to download
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'section.php';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action_type';
            actionInput.value = 'export_students';
            form.appendChild(actionInput);

            const sectionInput = document.createElement('input');
            sectionInput.type = 'hidden';
            sectionInput.name = 'export_section_id';
            sectionInput.value = currentSectionId;
            form.appendChild(sectionInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        /**
         * Downloads the Excel template for student import
         */
        function downloadTemplate() {
            // Create CSV content for template
            const headers = [
                'Student ID', 'Legal Name', 'Preferred Name', 'Date of Birth', 'Gender',
                'Address', 'Phone', 'Email',
                'Parent Contacts', 'Emergency Contacts',
                'Allergies', 'Chronic Issues', 'Dietary Restrictions',
                'Previous School', 'Grade Level', 'Interests'
            ];

            const sampleRow = [
                'STU-001', 'Juan Dela Cruz', 'Juan', '2010-05-15', 'Male',
                '123 Main Street, City', '09123456789', 'juan@email.com',
                'Maria Dela Cruz - 09987654321', 'Pedro Dela Cruz - 09876543210',
                'Peanuts', 'Asthma', 'None',
                'Previous High School', 'Grade 11', 'Mathematics, Science'
            ];

            let csvContent = headers.join(',') + '\n';
            csvContent += sampleRow.join(',');

            const blob = new Blob([csvContent], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'student_import_template.csv';
            link.click();
        }

        /**
         * Updates the file name display when a file is selected
         * @param {HTMLInputElement} input - File input element
         */
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('selectedFileName');
            if (input.files && input.files.length > 0) {
                fileNameDisplay.classList.remove('hidden');
                fileNameDisplay.querySelector('span').textContent = input.files[0].name;
            } else {
                fileNameDisplay.classList.add('hidden');
            }
        }

        /**
         * Formats a date string for display
         * @param {string} dateStr - Date string (YYYY-MM-DD)
         * @returns {string} Formatted date
         */
        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        /**
         * Escapes HTML to prevent XSS
         * @param {string} str - String to escape
         * @returns {string} Escaped string
         */
        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML.replace(/'/g, "\\'").replace(/"/g, '\\"');
        }

        // Close modals on backdrop click
        document.getElementById('addStudentsModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddStudentsModal();
        });

        document.getElementById('moveStudentModal').addEventListener('click', function(e) {
            if (e.target === this) closeMoveModal();
        });

        document.getElementById('deleteStudentModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteStudentModal();
        });
    </script>
</body>

</html>