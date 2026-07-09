<?php
$servername = "localhost";
$username = "bestsotj_skolshl";
$password = "WXw^g?.s,hMY";
$dbname = "bestsotj_skooling";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// sql to delete a record
$a=$_GET['randomid'];
$sql = "DELETE FROM manage_student WHERE randomid='$a'";

if ($conn->query($sql) === TRUE) {
  echo "Record deleted successfully";
  header("location:move_student_to_nextTerm.php");
} else {
  echo "Error deleting record: " . $conn->error;
}

$conn->close();
?>