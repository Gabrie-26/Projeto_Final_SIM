<?php
session_start();
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header('Location: login.php');
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

$sql = "SELECT ID, USERNAME, ROLE FROM USERS  
        WHERE USERNAME = '$username' 
        AND PASSWORD = SHA2('$password', 256)";

$result = mysqli_query($connect, $sql)
or die('The query failed: ' . mysqli_error($connect));

$number = mysqli_num_rows($result);

if ($number == 1) {
    $row = mysqli_fetch_assoc($result);
    $_SESSION['usuario'] = $row['USERNAME'];
    $_SESSION['id'] = $row['ID'];
    $_SESSION['role'] = $row['ROLE'];
    header('Location: index.php?sucesso=1');
    exit;
} else {
    header('Location: login.php?erro=1');
    exit;
}
?>