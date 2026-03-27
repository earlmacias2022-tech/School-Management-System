<?php
include '../config/db.php';

$first = $_POST['first_name'];
$last = $_POST['last_name'];
$section = $_POST['section'];

$conn->query("INSERT INTO students (first_name,last_name,section)
VALUES ('$first','$last','$section')");
