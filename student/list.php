<?php
include '../config/auth.php';
include '../config/db.php';

$result = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Students</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="sidebar">
    <h3>AMS</h3>
    <a href="../dashboard.php">Dashboard</a>
    <a href="list.php">Students</a>
    <a href="#">Attendance</a>
    <a href="#" onclick="openLogoutModal()">Logout</a>
</div>

<div class="content">
    <div class="topbar">
        Welcome, <?php echo $_SESSION['user']; ?>
    </div>

    <h2>Student List</h2>

    <button onclick="openAddModal()">+ Add Student</button>

    <br><br>

    <table border="1" width="100%" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Section</th>
            <th>Action</th>
        </tr>

        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['first_name'] . " " . $row['last_name']; ?></td>
            <td><?php echo $row['section']; ?></td>
          <td>
    <button onclick="openEditModal(
        <?php echo $row['id']; ?>,
        '<?php echo $row['first_name']; ?>',
        '<?php echo $row['last_name']; ?>',
        '<?php echo $row['section']; ?>'
    )">Edit</button>

    <button onclick="deleteStudent(<?php echo $row['id']; ?>)">Delete</button>
</td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <h3>Add Student</h3>

        <form id="addForm">
            <input type="text" name="first_name" placeholder="First Name" required><br><br>
            <input type="text" name="last_name" placeholder="Last Name" required><br><br>
            <input type="text" name="section" placeholder="Section" required><br><br>

            <button type="submit">Save</button>
            <button type="button" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>
<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Edit Student</h3>

        <form id="editForm">
            <input type="hidden" name="id" id="edit_id">

            <input type="text" name="first_name" id="edit_first" placeholder="First Name" required><br><br>
            <input type="text" name="last_name" id="edit_last" placeholder="Last Name" required><br><br>
            <input type="text" name="section" id="edit_section" placeholder="Section" required><br><br>

            <button type="submit">Update</button>
            <button type="button" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>
<!-- Logout Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <h3>Logout?</h3>
        <button onclick="confirmLogout()">Yes</button>
        <button onclick="closeModal()">Cancel</button>
    </div>
</div>

<div id="toast" class="toast"></div>

<script src="../assets/js/toast.js"></script>

<script>
// Modal controls
function openAddModal() {
    document.getElementById("addModal").style.display = "block";
}

function openLogoutModal() {
    document.getElementById("logoutModal").style.display = "block";
}
// Logout
function confirmLogout() {
    window.location.href = "../logout.php";
}

// Add student (AJAX)
document.getElementById("addForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch("add.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        showToast("Student added!");
        setTimeout(() => location.reload(), 1000);
    });
});

// Delete student
function deleteStudent(id) {
    if (confirm("Delete this student?")) {
        fetch("delete.php?id=" + id)
        .then(res => res.text())
        .then(() => {
            showToast("Deleted!");
            setTimeout(() => location.reload(), 1000);
        });
    }
}

// Open edit modal and fill data
function openEditModal(id, first, last, section) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_first").value = first;
    document.getElementById("edit_last").value = last;
    document.getElementById("edit_section").value = section;

    document.getElementById("editModal").style.display = "block";
}

// Update student (AJAX)
document.getElementById("editForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch("update.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(() => {
        showToast("Student updated!");
        setTimeout(() => location.reload(), 1000);
    });
});

// Update closeModal to include editModal
function closeModal() {
    document.getElementById("addModal").style.display = "none";
    document.getElementById("editModal").style.display = "none";
    document.getElementById("logoutModal").style.display = "none";
}
</script>

</body>
</html>