<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['usuario']) || $_SESSION['role'] != 'P') {
    header('Location: index.php');
    exit;
}

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

$user_id = $_SESSION['user_id'];

$sql = "SELECT M.NAME, M.LEAFLET, P.START_DATE, P.END_DATE, P.DOSAGE_VALID, U.NAME AS MEDIC_NAME
        FROM PRESCRIPTIONS P
        INNER JOIN MEDICATIONS M ON P.MEDICATION_ID = M.ID
        INNER JOIN CONSULTATIONS C ON P.CONSULTATION_ID = C.ID
        INNER JOIN USERS U ON C.MEDIC_ID = U.ID
        WHERE C.PATIENT_ID = '$user_id'
        AND (P.END_DATE >= CURDATE() OR P.END_DATE IS NULL)
        ORDER BY P.START_DATE DESC";

$result = mysqli_query($connect, $sql)
or die('The query failed: ' . mysqli_error($connect));
?>

    <div class="contents user">
        <h2>Medicação Ativa</h2>

        <?php if (mysqli_num_rows($result) == 0): ?>
            <p style="color: #888;">Não tens medicação ativa de momento.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Medicamento</th>
                    <th>Dosagem</th>
                    <th>Início</th>
                    <th>Fim</th>
                    <th>Médico</th>
                    <th>Folheto</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['NAME']; ?></td>
                        <td><?php echo $row['DOSAGE_VALID'] !== null ? $row['DOSAGE_VALID'] : '-'; ?></td>
                        <td><?php echo $row['START_DATE']; ?></td>
                        <td><?php echo $row['END_DATE'] !== null ? $row['END_DATE'] : 'Sem prazo definido'; ?></td>
                        <td><?php echo $row['MEDIC_NAME']; ?></td>
                        <td>
                            <?php if ($row['LEAFLET']): ?>
                                <a href="<?php echo $row['LEAFLET']; ?>" target="_blank">Ver Folheto</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>

<?php include 'footer.php'; ?>