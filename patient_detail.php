<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['usuario']) || $_SESSION['role'] != 'M') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: patients_list.php');
    exit;
}

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

$patient_id = $_GET['id'];

$sql = "SELECT U.ID, U.NAME, U.USERNAME, U.EMAIL, U.PHOTO, U.CREATION_DATE,
               P.DATE_OF_BIRTH, P.GENDER, P.NIF, P.ADDRESS, P.DISTRICT, P.ALLERGIES, P.CHRONIC_DISEASES
        FROM USERS U
        INNER JOIN PATIENTS P ON U.ID = P.USER_ID
        WHERE U.ID = '$patient_id' AND U.ROLE = 'P'";

$result = mysqli_query($connect, $sql)
or die('The query failed: ' . mysqli_error($connect));

$data = mysqli_fetch_assoc($result);

if (!$data) {
    header('Location: patients_list.php');
    exit;
}

$gender_label = ['M' => 'Masculino', 'F' => 'Feminino', 'O' => 'Outro'];
?>

    <div class="contents user">
        <h2>Ficha do Paciente</h2>

        <?php if ($data['PHOTO']): ?>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($data['PHOTO']); ?>"
                 alt="Foto de perfil"
                 style="width:120px; height:120px; object-fit:cover; border-radius:50%; margin-bottom:15px;">
        <?php else: ?>
            <p style="color: #888;">Sem foto de perfil.</p>
        <?php endif; ?>

        <h3>Dados de Acesso</h3>
        <table>
            <tr><th>Campo</th><th>Valor</th></tr>
            <tr><td>Nome</td><td><?php echo $data['NAME']; ?></td></tr>
            <tr><td>Username</td><td><?php echo $data['USERNAME']; ?></td></tr>
            <tr><td>Email</td><td><?php echo $data['EMAIL']; ?></td></tr>
            <tr><td>Membro desde</td><td><?php echo $data['CREATION_DATE']; ?></td></tr>
        </table>

        <h3>Dados Clínicos</h3>
        <table>
            <tr><th>Campo</th><th>Valor</th></tr>
            <tr><td>Data de Nascimento</td><td><?php echo $data['DATE_OF_BIRTH']; ?></td></tr>
            <tr><td>Género</td><td><?php echo $gender_label[$data['GENDER']]; ?></td></tr>
            <tr><td>NIF</td><td><?php echo $data['NIF']; ?></td></tr>
            <tr><td>Morada</td><td><?php echo $data['ADDRESS']; ?></td></tr>
            <tr><td>Distrito</td><td><?php echo $data['DISTRICT']; ?></td></tr>
            <tr><td>Alergias</td><td><?php echo $data['ALLERGIES']; ?></td></tr>
            <tr><td>Doenças Crónicas</td><td><?php echo $data['CHRONIC_DISEASES']; ?></td></tr>
        </table>

        <div style="margin-top: 15px;">
            <button type="button" onclick="window.location.href='patients_list.php'">Voltar à Lista</button>
        </div>
    </div>

<?php include 'footer.php'; ?>