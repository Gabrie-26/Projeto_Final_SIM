<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['usuario']) || ($_SESSION['role'] != 'M' && $_SESSION['role'] != 'P')) {
    header('Location: index.php');
    exit;
}

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

// Médico vê as suas consultas, paciente vê as suas
if ($role == 'M') {
    $sql = "SELECT C.ID, C.DATE,
                   U.NAME AS PATIENT_NAME
            FROM CONSULTATIONS C
            INNER JOIN USERS U ON C.PATIENT_ID = U.ID
            WHERE C.MEDIC_ID = '$user_id'
            ORDER BY C.DATE DESC";
} else {
    $sql = "SELECT C.ID, C.DATE,
                   U.NAME AS MEDIC_NAME
            FROM CONSULTATIONS C
            INNER JOIN USERS U ON C.MEDIC_ID = U.ID
            WHERE C.PATIENT_ID = '$user_id'
            ORDER BY C.DATE DESC";
}

$result = mysqli_query($connect, $sql)
or die('The query failed: ' . mysqli_error($connect));
?>

    <div class="contents user">
        <h2>Histórico de Consultas</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Data</th>
                <?php if ($role == 'M'): ?>
                    <th>Paciente</th>
                <?php else: ?>
                    <th>Médico</th>
                <?php endif; ?>
                <th>Ação</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['ID']; ?></td>
                    <td><?php echo $row['DATE']; ?></td>
                    <td>
                        <?php if ($role == 'M'): ?>
                            <?php echo $row['PATIENT_NAME']; ?>
                        <?php else: ?>
                            <?php echo $row['MEDIC_NAME']; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="button" onclick="window.location.href='consultation_detail.php?id=<?php echo $row['ID']; ?>'">Ver Detalhes</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

<?php include 'footer.php'; ?>