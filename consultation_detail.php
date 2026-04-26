<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['usuario']) || ($_SESSION['role'] != 'M' && $_SESSION['role'] != 'P')) {
    header('Location: index.php');
    exit;
}

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

if (!isset($_GET['id'])) {
    header('Location: consultation_history.php');
    exit;
}

$consultation_id = (int)$_GET['id'];
$user_id         = $_SESSION['user_id'];
$role            = $_SESSION['role'];

// Buscar dados da consulta — garante que só vê as suas próprias consultas
if ($role == 'M') {
    $sql = "SELECT C.*, 
                   UP.NAME AS PATIENT_NAME,
                   UM.NAME AS MEDIC_NAME
            FROM CONSULTATIONS C
            INNER JOIN USERS UP ON C.PATIENT_ID = UP.ID
            INNER JOIN USERS UM ON C.MEDIC_ID = UM.ID
            WHERE C.ID = '$consultation_id' AND C.MEDIC_ID = '$user_id'";
} else {
    $sql = "SELECT C.*, 
                   UP.NAME AS PATIENT_NAME,
                   UM.NAME AS MEDIC_NAME
            FROM CONSULTATIONS C
            INNER JOIN USERS UP ON C.PATIENT_ID = UP.ID
            INNER JOIN USERS UM ON C.MEDIC_ID = UM.ID
            WHERE C.ID = '$consultation_id' AND C.PATIENT_ID = '$user_id'";
}

$result = mysqli_query($connect, $sql)
or die('The query failed: ' . mysqli_error($connect));

$data = mysqli_fetch_assoc($result);

if (!$data) {
    header('Location: consultation_history.php');
    exit;
}

// Buscar medicamentos prescritos nesta consulta
$sql_meds = "SELECT M.NAME, M.LEAFLET, P.START_DATE, P.END_DATE, P.DOSAGE_VALID
             FROM PRESCRIPTIONS P
             INNER JOIN MEDICATIONS M ON P.MEDICATION_ID = M.ID
             WHERE P.CONSULTATION_ID = '$consultation_id'";

$meds_result = mysqli_query($connect, $sql_meds);
?>

<div class="contents user">
    <h2>Detalhes da Consulta</h2>

    <h3>Informação Geral</h3>
    <table>
        <tr><th>Campo</th><th>Valor</th></tr>
        <tr><td>Data</td><td><?php echo $data['DATE']; ?></td></tr>
        <tr><td>Médico</td><td><?php echo $data['MEDIC_NAME']; ?></td></tr>
        <tr><td>Paciente</td><td><?php echo $data['PATIENT_NAME']; ?></td></tr>
        <tr><td>Especialidade</td><td><?php echo $data['SPECIALTY']; ?></td></tr>
    </table>

    <h3>Indicadores Médicos</h3>
    <table>
        <tr><th>Campo</th><th>Valor</th></tr>
        <tr><td>Peso (kg)</td><td><?php echo $data['WEIGHT']; ?></td></tr>
        <tr><td>Altura (cm)</td><td><?php echo $data['HEIGHT']; ?></td></tr>
        <tr><td>Temperatura (°C)</td><td><?php echo $data['TEMPERATURE']; ?></td></tr>
        <tr><td>Pressão Arterial</td><td><?php echo $data['BLOOD_PRESSURE']; ?></td></tr>
    </table>

    <?php if ($data['IMAGE']): ?>
        <h3>Imagem da Consulta</h3>
        <img src="data:image/jpeg;base64,<?php echo base64_encode($data['IMAGE']); ?>"
             alt="Imagem da consulta"
             style="max-width:400px; margin-top:10px;">
    <?php endif; ?>

    <h3>Resumo</h3>
    <p style="padding: 10px; background:#f0f0f0; border-radius:4px;">
        <?php echo nl2br($data['SUMMARY'] !== null ? $data['SUMMARY'] : ''); ?>
    </p>

    <h3>Medicamentos Prescritos</h3>
    <table>
        <tr>
            <th>Nome</th>
            <th>Dosagem</th>
            <th>Início</th>
            <th>Fim</th>
            <th>Folheto</th>
        </tr>
        <?php while ($med = mysqli_fetch_assoc($meds_result)): ?>
            <tr>
                <td><?php echo $med['NAME']; ?></td>
                <td><?php echo $med['DOSAGE_VALID'] ?: '-'; ?></td>
                <td><?php echo $med['START_DATE']; ?></td>
                <td><?php echo $med['END_DATE'] ?: '-'; ?></td>
                <td>
                    <?php if ($med['LEAFLET']): ?>
                        <a href="<?php echo $med['LEAFLET']; ?>" target="_blank">Ver Folheto</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <div style="margin-top: 15px;">
        <button type="button" onclick="window.location.href='consultation_history.php'">Voltar ao Histórico</button>
    </div>
</div>

<?php include 'footer.php'; ?>
