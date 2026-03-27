<?php
session_start();

// If user is NOT logged in
if (!isset($_SESSION['user'])) {
    header("Location: /Attendance_Management_System/auth/login.php");
    exit();
}
