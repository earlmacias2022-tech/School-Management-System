<?php
include '../config/db.php';

$id = $_POST['id'];
$first = $_POST['first_name'];
$last = $_POST['last_name'];
$section = $_POST['section'];

$conn->query("UPDATE students 
SET first_name='$first', last_name='$last', section='$section' 
WHERE id=$id");
?>