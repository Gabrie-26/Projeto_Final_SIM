<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['role'] != 'M') {
    echo json_encode(['erro' => 'Acesso negado.']);
    exit;
}

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die(json_encode(['erro' => 'Erro de conexão.']));

$name    = $_POST['name'];
$leaflet = $_POST['leaflet'];

if (empty($name)) {
    echo json_encode(['erro' => 'O nome do medicamento é obrigatório.']);
    exit;
}

$check = mysqli_query($connect, "SELECT ID FROM MEDICATIONS WHERE NAME = '$name'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(['erro' => 'Este medicamento já existe.']);
    exit;
}

$sql = "INSERT INTO MEDICATIONS (NAME, LEAFLET) VALUES ('$name', '$leaflet')";
if (mysqli_query($connect, $sql)) {
    $new_id = mysqli_insert_id($connect);
    echo json_encode(['sucesso' => true, 'id' => $new_id, 'name' => $name]);
} else {
    echo json_encode(['erro' => 'Erro ao adicionar: ' . mysqli_error($connect)]);
}
?>
