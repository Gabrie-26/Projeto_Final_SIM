<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['usuario']) || $_SESSION['role'] != 'M') {
    header('Location: index.php');
    exit;
}

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

$sql = "SELECT U.ID, U.NAME, U.USERNAME, U.CREATION_DATE, P.DISTRICT
        FROM USERS U
        INNER JOIN PATIENTS P ON U.ID = P.USER_ID
        WHERE U.ROLE = 'P'
        ORDER BY U.NAME";

$result = mysqli_query($connect, $sql)
or die('The query failed: ' . mysqli_error($connect));
?>

    <div class="contents user">
        <h2>Lista de Pacientes</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Username</th>
                <th>Distrito</th>
                <th>Data de Registo</th>
                <th>Ação</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['ID']; ?></td>
                    <td><?php echo $row['NAME']; ?></td>
                    <td><?php echo $row['USERNAME']; ?></td>
                    <td><?php echo $row['DISTRICT']; ?></td>
                    <td><?php echo $row['CREATION_DATE']; ?></td>
                    <td>
                        <button type="button" onclick="window.location.href='patient_detail.php?id=<?php echo $row['ID']; ?>'">Ver Ficha</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

<?php include 'footer.php'; ?>