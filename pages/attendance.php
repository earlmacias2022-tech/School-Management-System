<?php
include '../config/db.php';
include '../config/auth.php';

$current_page = 'attendance.php';
$title = 'Attendance Management';


// ============================
// FETCH STUDENTS BY SECTION (AJAX)
// ============================
if (isset($_GET['action_type']) && $_GET['action_type'] === 'fetch_students_by_section') {
    header('Content-Type: application/json');
    $section_id = intval($_GET['section_id']);
    $date = $_GET['date'] ?? date('Y-m-d');

    // Get students in this section
    $students = mysqli_query($conn, "
        SELECT id, student_id, legal_name, preferred_name
        FROM students
        WHERE section_id = $section_id
        ORDER BY legal_name ASC
    ");

    $studentList = [];
    while ($row = mysqli_fetch_assoc($students)) {
        $studentList[] = $row;
    }

    // Get existing attendance for this section + date
    $existing = [];
    $attResult = mysqli_query($conn, "
        SELECT student_id, status, remarks
        FROM attendance
        WHERE section_id = $section_id AND date = '$date'
    ");
    while ($row = mysqli_fetch_assoc($attResult)) {
        $existing[$row['student_id']] = [
            'status' => $row['status'],
            'remarks' => $row['remarks']
        ];
    }

    echo json_encode([
        'students' => $studentList,
        'existing_attendance' => $existing
    ]);
    exit;
}

// ============================
// SAVE ATTENDANCE (AJAX)
// ============================
if (isset($_POST['action_type']) && $_POST['action_type'] === 'save_attendance') {
    header('Content-Type: application/json');
    $section_id = intval($_POST['section_id']);
    $date = $_POST['date'];
    $recorded_by = $_SESSION['username'] ?? 'admin';
    $data = json_decode($_POST['data'], true);

    $saved = 0;
    foreach ($data as $item) {
        $student_id = intval($item['student_id']);
        $status = mysqli_real_escape_string($conn, $item['status']);
        $remarks = mysqli_real_escape_string($conn, $item['remarks'] ?? '');

        // Upsert: insert or update on duplicate
        $sql = "INSERT INTO attendance (student_id, section_id, date, status, remarks, recorded_by)
                VALUES ($student_id, $section_id, '$date', '$status', '$remarks', '$recorded_by')
                ON DUPLICATE KEY UPDATE status = '$status', remarks = '$remarks', recorded_by = '$recorded_by', updated_at = NOW()";
        if (mysqli_query($conn, $sql)) {
            $saved++;
        }
    }

    echo json_encode(['success' => true, 'saved_count' => $saved]);
    exit;
}

// ============================
// FETCH ATTENDANCE HISTORY (AJAX)
// ============================
if (isset($_GET['action_type']) && $_GET['action_type'] === 'fetch_attendance_history') {
    header('Content-Type: application/json');
    $section_id = $_GET['section_id'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $status_filter = $_GET['status'] ?? '';

    $query = "SELECT a.*, s.legal_name, s.student_id as stu_code, sec.section_name
              FROM attendance a
              JOIN students s ON a.student_id = s.id
              JOIN sections sec ON a.section_id = sec.sectionID
              WHERE 1=1";

    if (!empty($section_id)) {
        $query .= " AND a.section_id = " . intval($section_id);
    }
    if (!empty($date_from)) {
        $query .= " AND a.date >= '$date_from'";
    }
    if (!empty($date_to)) {
        $query .= " AND a.date <= '$date_to'";
    }
    if (!empty($status_filter)) {
        $query .= " AND a.status = '$status_filter'";
    }

    $query .= " ORDER BY a.date DESC, s.legal_name ASC";

    $result = mysqli_query($conn, $query);
    $records = [];
    $counts = ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'Excused' => 0];

    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
        $counts[$row['status']]++;
    }

    echo json_encode([
        'records' => $records,
        'summary' => $counts,
        'total' => count($records)
    ]);
    exit;
}

// Get sections for dropdown
$sections = mysqli_query($conn, "SELECT * FROM sections ORDER BY section_name ASC");

// Get all attendance history (initial load for preview)
$historyPreview = mysqli_query($conn, "
    SELECT a.*, s.legal_name, s.student_id as stu_code, sec.section_name
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    JOIN sections sec ON a.section_id = sec.sectionID
    ORDER BY a.date DESC, s.legal_name ASC
    LIMIT 50
");
?>
<!DOCTYPE html>
<html class="h-full">

<head>
    <title>Attendance | ClassOptima</title>
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

        .status-btn {
            transition: all 0.2s;
        }

        .status-btn.active {
            font-weight: 600;
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
                    <h1 class="text-3xl font-bold text-slate-800">Attendance Management</h1>
                    <p class="text-slate-500 text-sm mt-1">Mark and track student attendance by section.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-500">Today's Date</p>
                    <p class="text-lg font-bold text-slate-800" id="todayDate"></p>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="flex gap-2 mb-6 animate-fade-in-up animate-delay-100">
            <button onclick="switchTab('mark')" id="tabMark"
                class="px-6 py-3 rounded-xl font-semibold text-sm transition-all bg-blue-600 text-white shadow-lg shadow-blue-200">
                <i data-lucide="clipboard-check" class="w-4 h-4 inline mr-2"></i>Mark Attendance
            </button>
            <button onclick="switchTab('history')" id="tabHistory"
                class="px-6 py-3 rounded-xl font-semibold text-sm transition-all bg-white text-slate-600 hover:bg-slate-50 border border-slate-200">
                <i data-lucide="history" class="w-4 h-4 inline mr-2"></i>View History
            </button>
        </div>

        <!-- MARK ATTENDANCE TAB -->
        <div id="markTab">
            <!-- Section & Date Selector -->
            <div class="bg-white rounded-2xl p-6 shadow-lg shadow-blue-100/50 border border-blue-50 mb-6 animate-fade-in-up animate-delay-100">
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Section</label>
                        <select id="sectionSelect" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            <option value="">Select Section</option>
                            <?php while ($sec = mysqli_fetch_assoc($sections)): ?>
                                <option value="<?php echo $sec['sectionID']; ?>"><?php echo htmlspecialchars($sec['section_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="w-44">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Date</label>
                        <input type="date" id="attendanceDate" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <button onclick="loadStudentsForAttendance()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-semibold rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center gap-2">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            Load Students
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions (hidden until students loaded) -->
            <div id="bulkActionsBar" class="hidden bg-white rounded-2xl p-4 shadow-lg border border-blue-50 mb-6 animate-fade-in-up">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-700" id="studentCountLabel">Loading...</p>
                    <div class="flex gap-2">
                        <button onclick="bulkMarkAll('Present')" class="px-4 py-2 bg-green-100 hover:bg-green-200 text-green-700 font-medium rounded-lg text-sm transition-all">Mark All Present</button>
                        <button onclick="bulkMarkAll('Absent')" class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 font-medium rounded-lg text-sm transition-all">Mark All Absent</button>
                        <button onclick="bulkMarkAll('Late')" class="px-4 py-2 bg-amber-100 hover:bg-amber-200 text-amber-700 font-medium rounded-lg text-sm transition-all">Mark All Late</button>
                        <button onclick="bulkMarkAll('Excused')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium rounded-lg text-sm transition-all">Mark All Excused</button>
                        <button onclick="bulkMarkAll('none')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-slate-600 font-medium rounded-lg text-sm transition-all">Clear All</button>
                    </div>
                </div>
            </div>

            <!-- Students Attendance List -->
            <div id="attendanceListContainer" class="hidden animate-fade-in-up animate-delay-200">
                <div class="bg-white rounded-2xl shadow-lg shadow-blue-100/50 border border-blue-50 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-slate-800">Student Attendance</h2>
                    </div>
                    <div class="divide-y divide-gray-100" id="attendanceListBody">
                        <!-- Populated by JS -->
                    </div>
                    <!-- Empty State -->
                    <div id="emptyState" class="hidden p-12 text-center">
                        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="users" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-700">No students found</h3>
                        <p class="text-slate-500 text-sm mt-1">This section has no students enrolled.</p>
                    </div>
                </div>
            </div>

            <!-- Save Bar (fixed bottom) -->
            <div id="saveBar" class="hidden fixed bottom-6 left-0 right-0 px-8 z-40">
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-2xl p-4 shadow-2xl border border-blue-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div id="dirtyIndicator" class="w-3 h-3 rounded-full bg-amber-400 hidden"></div>
                            <span class="text-sm font-medium text-slate-600" id="saveBarLabel">No changes</span>
                        </div>
                        <div class="flex gap-3">
                            <span id="markedCount" class="px-4 py-2 bg-blue-50 text-blue-700 font-semibold rounded-lg text-sm"></span>
                            <button onclick="saveAttendance()" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-bold rounded-xl shadow-lg text-sm flex items-center gap-2 transition-all">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Save Attendance
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HISTORY TAB -->
        <div id="historyTab" class="hidden">
            <!-- History Filters -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-blue-50 mb-6 animate-fade-in-up">
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Section</label>
                        <select id="historySection" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            <option value="">All Sections</option>
                            <?php
                            mysqli_data_seek($sections, 0);
                            while ($sec = mysqli_fetch_assoc($sections)):
                            ?>
                                <option value="<?php echo $sec['sectionID']; ?>"><?php echo htmlspecialchars($sec['section_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">From</label>
                        <input type="date" id="historyDateFrom" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">To</label>
                        <input type="date" id="historyDateTo" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Status</label>
                        <select id="historyStatus" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            <option value="">All</option>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                            <option value="Excused">Excused</option>
                        </select>
                    </div>
                    <button onclick="loadHistory()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-semibold rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 animate-fade-in-up animate-delay-100">
                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-5 border border-blue-100">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-blue-500 rounded-xl shadow-lg shadow-blue-200">
                            <i data-lucide="list" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-blue-800" id="totalRecords">0</p>
                            <p class="text-xs text-blue-600 font-medium">Total Records</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-5 border border-green-100">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-green-500 rounded-xl shadow-lg shadow-green-200">
                            <i data-lucide="check-circle" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-800" id="presentCount">0</p>
                            <p class="text-xs text-green-600 font-medium">Present</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-2xl p-5 border border-red-100">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-red-500 rounded-xl shadow-lg shadow-red-200">
                            <i data-lucide="x-circle" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-red-800" id="absentCount">0</p>
                            <p class="text-xs text-red-600 font-medium">Absent</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-5 border border-amber-100">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-amber-500 rounded-xl shadow-lg shadow-amber-200">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-amber-800" id="lateCount">0</p>
                            <p class="text-xs text-amber-600 font-medium">Late / Excused</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="bg-white rounded-2xl shadow-lg border border-blue-50 overflow-hidden animate-fade-in-up animate-delay-200">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-slate-800">Attendance Records</h2>
                    <p class="text-sm text-slate-500">Showing <span id="historyCount">0</span> records</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Section</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Remarks</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody" class="divide-y divide-gray-100">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
                <!-- Empty State -->
                <div id="historyEmpty" class="hidden p-12 text-center">
                    <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="calendar-x" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-700">No records found</h3>
                    <p class="text-slate-500 text-sm mt-1">Try adjusting your filters or mark some attendance first.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Toast -->
    <div id="toast" class="toast toast-success hidden"></div>

    <script src="../assets/js/toast.js"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Set today's date
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('attendanceDate').value = today;
        document.getElementById('todayDate').textContent = new Date().toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        // Set default history date range (first of month to today)
        const now = new Date();
        const firstOfMonth = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
        document.getElementById('historyDateFrom').value = firstOfMonth;
        document.getElementById('historyDateTo').value = today;

        // State
        let state = {
            currentSection: null,
            currentDate: today,
            students: [],
            existingAttendance: {},
            dirty: false,
            currentTab: 'mark'
        };

        // Tab switching
        function switchTab(tab) {
            state.currentTab = tab;
            const markTab = document.getElementById('markTab');
            const historyTab = document.getElementById('historyTab');
            const tabMark = document.getElementById('tabMark');
            const tabHistory = document.getElementById('tabHistory');

            if (tab === 'mark') {
                markTab.classList.remove('hidden');
                historyTab.classList.add('hidden');
                tabMark.className = 'px-6 py-3 rounded-xl font-semibold text-sm transition-all bg-blue-600 text-white shadow-lg shadow-blue-200';
                tabHistory.className = 'px-6 py-3 rounded-xl font-semibold text-sm transition-all bg-white text-slate-600 hover:bg-slate-50 border border-slate-200';
            } else {
                markTab.classList.add('hidden');
                historyTab.classList.remove('hidden');
                tabMark.className = 'px-6 py-3 rounded-xl font-semibold text-sm transition-all bg-white text-slate-600 hover:bg-slate-50 border border-slate-200';
                tabHistory.className = 'px-6 py-3 rounded-xl font-semibold text-sm transition-all bg-blue-600 text-white shadow-lg shadow-blue-200';
                if (state.currentSection) loadHistory();
            }
        }

        // Load students for attendance marking
        function loadStudentsForAttendance() {
            const sectionId = document.getElementById('sectionSelect').value;
            const date = document.getElementById('attendanceDate').value;

            if (!sectionId) {
                showToast('Please select a section first', 'error');
                return;
            }

            state.currentSection = sectionId;
            state.currentDate = date;
            state.dirty = false;

            fetch(`attendance.php?action_type=fetch_students_by_section&section_id=${sectionId}&date=${date}`)
                .then(r => r.json())
                .then(data => {
                    state.students = data.students;
                    state.existingAttendance = data.existing_attendance || {};

                    // Show/hide empty state
                    const emptyState = document.getElementById('emptyState');
                    const listContainer = document.getElementById('attendanceListContainer');
                    const bulkBar = document.getElementById('bulkActionsBar');
                    const saveBar = document.getElementById('saveBar');

                    if (data.students.length === 0) {
                        listContainer.classList.remove('hidden');
                        emptyState.classList.remove('hidden');
                        document.getElementById('attendanceListBody').innerHTML = '';
                        bulkBar.classList.add('hidden');
                        saveBar.classList.add('hidden');
                    } else {
                        listContainer.classList.remove('hidden');
                        emptyState.classList.add('hidden');
                        bulkBar.classList.remove('hidden');
                        saveBar.classList.remove('hidden');
                        renderAttendanceList();
                    }

                    document.getElementById('studentCountLabel').textContent =
                        `Showing ${data.students.length} student${data.students.length !== 1 ? 's' : ''} in this section`;
                    updateSaveBarLabel();
                    lucide.createIcons();
                });
        }

        // Render attendance list
        function renderAttendanceList() {
            const container = document.getElementById('attendanceListBody');

            container.innerHTML = state.students.map(s => {
                const existing = state.existingAttendance[s.id] || {};
                const status = existing.status || '';
                const remarks = existing.remarks || '';

                return `
                <div class="flex items-center gap-4 p-4 hover:bg-slate-50 transition-colors student-row" data-id="${s.id}">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                        ${s.legal_name.charAt(0)}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-slate-800">${s.legal_name}</p>
                        <p class="text-xs text-slate-500">${s.student_id || 'No ID'} ${s.preferred_name ? '• ' + s.preferred_name : ''}</p>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <button onclick="setStatus(${s.id}, 'Present')" class="status-btn px-4 py-2 rounded-lg text-sm font-medium transition-all ${status === 'Present' ? 'bg-green-500 text-white active' : 'bg-gray-100 text-gray-600 hover:bg-green-100 hover:text-green-700'}">
                            Present
                        </button>
                        <button onclick="setStatus(${s.id}, 'Absent')" class="status-btn px-4 py-2 rounded-lg text-sm font-medium transition-all ${status === 'Absent' ? 'bg-red-500 text-white active' : 'bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-700'}">
                            Absent
                        </button>
                        <button onclick="setStatus(${s.id}, 'Late')" class="status-btn px-4 py-2 rounded-lg text-sm font-medium transition-all ${status === 'Late' ? 'bg-amber-500 text-white active' : 'bg-gray-100 text-gray-600 hover:bg-amber-100 hover:text-amber-700'}">
                            Late
                        </button>
                        <button onclick="setStatus(${s.id}, 'Excused')" class="status-btn px-4 py-2 rounded-lg text-sm font-medium transition-all ${status === 'Excused' ? 'bg-slate-600 text-white active' : 'bg-gray-100 text-gray-600 hover:bg-slate-200 hover:text-slate-800'}">
                            Excused
                        </button>
                    </div>
                    <div class="w-48">
                        <input type="text" id="remarks_${s.id}" placeholder="Remarks (optional)"
                            value="${remarks}"
                            oninput="markDirty()"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                    </div>
                </div>
                `;
            }).join('');

            updateMarkedCount();
        }

        // Set status for a student
        function setStatus(studentId, status) {
            if (!state.existingAttendance[studentId]) {
                state.existingAttendance[studentId] = {};
            }
            state.existingAttendance[studentId].status = status;
            state.dirty = true;

            // Update button visuals
            const row = document.querySelector(`.student-row[data-id="${studentId}"]`);
            if (row) {
                const buttons = row.querySelectorAll('.status-btn');
                const statusColors = {
                    'Present': ['bg-green-500 text-white', 'bg-gray-100 text-gray-600 hover:bg-green-100 hover:text-green-700'],
                    'Absent': ['bg-red-500 text-white', 'bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-700'],
                    'Late': ['bg-amber-500 text-white', 'bg-gray-100 text-gray-600 hover:bg-amber-100 hover:text-amber-700'],
                    'Excused': ['bg-slate-600 text-white', 'bg-gray-100 text-gray-600 hover:bg-slate-200 hover:text-slate-800']
                };

                buttons.forEach(btn => {
                    const btnStatus = btn.textContent.trim();
                    if (btnStatus === status) {
                        btn.className = `status-btn px-4 py-2 rounded-lg text-sm font-medium transition-all ${statusColors[status][0]} active`;
                    } else {
                        btn.className = `status-btn px-4 py-2 rounded-lg text-sm font-medium transition-all ${statusColors[btnStatus][1]}`;
                    }
                });
            }

            markDirty();
            updateMarkedCount();
        }

        function markDirty() {
            state.dirty = true;
            updateSaveBarLabel();
        }

        function updateSaveBarLabel() {
            const label = document.getElementById('saveBarLabel');
            const indicator = document.getElementById('dirtyIndicator');
            if (state.dirty) {
                label.textContent = 'Unsaved changes';
                indicator.classList.remove('hidden');
            } else {
                label.textContent = 'No changes';
                indicator.classList.add('hidden');
            }
        }

        function updateMarkedCount() {
            const marked = Object.keys(state.existingAttendance).filter(id =>
                state.existingAttendance[id].status && state.existingAttendance[id].status !== ''
            ).length;
            const total = state.students.length;
            const countEl = document.getElementById('markedCount');
            if (total > 0) {
                countEl.textContent = `${marked} / ${total} marked`;
                countEl.className = 'px-4 py-2 bg-blue-50 text-blue-700 font-semibold rounded-lg text-sm';
            } else {
                countEl.textContent = '';
            }
        }

        // Bulk mark all
        function bulkMarkAll(status) {
            if (status === 'none') {
                state.existingAttendance = {};
            } else {
                state.students.forEach(s => {
                    state.existingAttendance[s.id] = {
                        status: status,
                        remarks: state.existingAttendance[s.id]?.remarks || ''
                    };
                });
            }
            state.dirty = true;
            renderAttendanceList();
            updateSaveBarLabel();
        }

        // Save attendance
        function saveAttendance() {
            if (!state.currentSection) return;

            const data = [];
            Object.keys(state.existingAttendance).forEach(studentId => {
                const entry = state.existingAttendance[studentId];
                if (entry.status) {
                    data.push({
                        student_id: parseInt(studentId),
                        status: entry.status,
                        remarks: document.getElementById(`remarks_${studentId}`)?.value || ''
                    });
                }
            });

            if (data.length === 0) {
                showToast('No attendance marked to save', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action_type', 'save_attendance');
            formData.append('section_id', state.currentSection);
            formData.append('date', state.currentDate);
            formData.append('data', JSON.stringify(data));

            fetch('attendance.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        state.dirty = false;
                        updateSaveBarLabel();
                        showToast(`Attendance saved for ${res.saved_count} student(s)`);
                        // Reload to refresh existing attendance
                        loadStudentsForAttendance();
                    } else {
                        showToast('Error saving attendance', 'error');
                    }
                })
                .catch(() => showToast('Error saving attendance', 'error'));
        }

        // Load attendance history
        function loadHistory() {
            const sectionId = document.getElementById('historySection').value;
            const dateFrom = document.getElementById('historyDateFrom').value;
            const dateTo = document.getElementById('historyDateTo').value;
            const status = document.getElementById('historyStatus').value;

            let url = `attendance.php?action_type=fetch_attendance_history`;
            if (sectionId) url += `&section_id=${sectionId}`;
            if (dateFrom) url += `&date_from=${dateFrom}`;
            if (dateTo) url += `&date_to=${dateTo}`;
            if (status) url += `&status=${status}`;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    // Update summary cards
                    document.getElementById('totalRecords').textContent = data.total;
                    document.getElementById('presentCount').textContent = data.summary.Present;
                    document.getElementById('absentCount').textContent = data.summary.Absent;
                    document.getElementById('lateCount').textContent = (data.summary.Late || 0) + (data.summary.Excused || 0);
                    document.getElementById('historyCount').textContent = data.total;

                    const tbody = document.getElementById('historyTableBody');
                    const emptyState = document.getElementById('historyEmpty');

                    if (data.records.length === 0) {
                        tbody.innerHTML = '';
                        emptyState.classList.remove('hidden');
                    } else {
                        emptyState.classList.add('hidden');
                        tbody.innerHTML = data.records.map(r => {
                            const statusColors = {
                                'Present': 'bg-green-50 text-green-700',
                                'Absent': 'bg-red-50 text-red-700',
                                'Late': 'bg-amber-50 text-amber-700',
                                'Excused': 'bg-slate-100 text-slate-700'
                            };
                            const statusColor = statusColors[r.status] || 'bg-gray-100 text-gray-700';
                            return `
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-slate-600">${r.date}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                            ${r.legal_name.charAt(0)}
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800 text-sm">${r.legal_name}</p>
                                            <p class="text-xs text-slate-500">${r.stu_code || ''}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">${r.section_name || '-'}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 ${statusColor} text-xs font-semibold rounded-full">${r.status}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate">${r.remarks || '-'}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${r.recorded_by || '-'}</td>
                            </tr>
                            `;
                        }).join('');
                    }
                    lucide.createIcons();
                });
        }

        // Show toast (inline since toast.js may not be loaded)
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type === 'error' ? 'toast-error' : 'toast-success'}`;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 4000);
        }

        // Initial load - load history on page load
        loadHistory();
    </script>
</body>

</html>