<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['usuario']) || $_SESSION['role'] != 'G') {
    header('Location: index.php');
    exit;
}

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

// 1. Prescrições por medicamento
$sql_meds = "SELECT M.NAME, COUNT(*) as TOTAL
             FROM PRESCRIPTIONS P
             INNER JOIN MEDICATIONS M ON P.MEDICATION_ID = M.ID
             GROUP BY M.ID, M.NAME
             ORDER BY TOTAL DESC";
$result_meds = mysqli_query($connect, $sql_meds);
$med_names   = [];
$med_totals  = [];
while ($row = mysqli_fetch_assoc($result_meds)) {
    $med_names[]  = $row['NAME'];
    $med_totals[] = $row['TOTAL'];
}

// 2. Pacientes por distrito
$sql_dist = "SELECT DISTRICT, COUNT(*) as TOTAL
             FROM PATIENTS
             WHERE DISTRICT IS NOT NULL AND DISTRICT != ''
             GROUP BY DISTRICT
             ORDER BY TOTAL DESC";
$result_dist = mysqli_query($connect, $sql_dist);
$dist_names  = [];
$dist_totals = [];
while ($row = mysqli_fetch_assoc($result_dist)) {
    $dist_names[]  = $row['DISTRICT'];
    $dist_totals[] = $row['TOTAL'];
}

// 3. Pacientes por faixa etária
$sql_age = "SELECT 
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, DATE_OF_BIRTH, CURDATE()) < 18 THEN 'Menos de 18'
                    WHEN TIMESTAMPDIFF(YEAR, DATE_OF_BIRTH, CURDATE()) BETWEEN 18 AND 35 THEN '18 - 35'
                    WHEN TIMESTAMPDIFF(YEAR, DATE_OF_BIRTH, CURDATE()) BETWEEN 36 AND 50 THEN '36 - 50'
                    WHEN TIMESTAMPDIFF(YEAR, DATE_OF_BIRTH, CURDATE()) BETWEEN 51 AND 65 THEN '51 - 65'
                    ELSE 'Mais de 65'
                END as FAIXA,
                COUNT(*) as TOTAL
            FROM PATIENTS
            WHERE DATE_OF_BIRTH IS NOT NULL
            GROUP BY FAIXA
            ORDER BY FAIXA";
$result_age = mysqli_query($connect, $sql_age);
$age_names  = [];
$age_totals = [];
while ($row = mysqli_fetch_assoc($result_age)) {
    $age_names[]  = $row['FAIXA'];
    $age_totals[] = $row['TOTAL'];
}

// 4. Consultas por mês (últimos 6 meses)
$sql_cons = "SELECT DATE_FORMAT(DATE, '%Y-%m') as MES, COUNT(*) as TOTAL
             FROM CONSULTATIONS
             WHERE DATE >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY MES
             ORDER BY MES";
$result_cons = mysqli_query($connect, $sql_cons);
$cons_months = [];
$cons_totals = [];
while ($row = mysqli_fetch_assoc($result_cons)) {
    $cons_months[] = $row['MES'];
    $cons_totals[] = $row['TOTAL'];
}

// Totais gerais
$total_users    = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as T FROM USERS"))['T'];
$total_patients = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as T FROM PATIENTS"))['T'];
$total_medics   = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as T FROM USERS WHERE ROLE = 'M'"))['T'];
$total_cons     = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as T FROM CONSULTATIONS"))['T'];
$total_pres     = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as T FROM PRESCRIPTIONS"))['T'];
?>

    <div class="contents user">
        <h2>Estatísticas</h2>

        <!-- Totais gerais -->
        <h3>Resumo Geral</h3>
        <table>
            <tr><th>Indicador</th><th>Total</th></tr>
            <tr><td>Total de Utilizadores</td><td><?php echo $total_users; ?></td></tr>
            <tr><td>Total de Pacientes</td><td><?php echo $total_patients; ?></td></tr>
            <tr><td>Total de Médicos</td><td><?php echo $total_medics; ?></td></tr>
            <tr><td>Total de Consultas</td><td><?php echo $total_cons; ?></td></tr>
            <tr><td>Total de Prescrições</td><td><?php echo $total_pres; ?></td></tr>
        </table>

        <!-- Gráficos -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

        <!-- Prescrições por medicamento -->
        <h3 style="margin-top:30px;">Prescrições por Medicamento</h3>
        <canvas id="chartMeds" style="max-height:300px;"></canvas>

        <!-- Pacientes por distrito -->
        <h3 style="margin-top:30px;">Pacientes por Distrito</h3>
        <canvas id="chartDist" style="max-height:300px;"></canvas>

        <!-- Pacientes por faixa etária -->
        <h3 style="margin-top:30px;">Pacientes por Faixa Etária</h3>
        <canvas id="chartAge" style="max-height:300px;"></canvas>

        <!-- Consultas por mês -->
        <h3 style="margin-top:30px;">Consultas nos Últimos 6 Meses</h3>
        <canvas id="chartCons" style="max-height:300px;"></canvas>

        <script>
            // Prescrições por medicamento — gráfico de barras
            new Chart(document.getElementById('chartMeds'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($med_names); ?>,
                    datasets: [{
                        label: 'Nº de Prescrições',
                        data: <?php echo json_encode($med_totals); ?>,
                        backgroundColor: 'rgba(0, 90, 142, 0.7)',
                        borderColor: 'rgba(0, 90, 142, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });

            // Pacientes por distrito — gráfico de barras
            new Chart(document.getElementById('chartDist'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($dist_names); ?>,
                    datasets: [{
                        label: 'Nº de Pacientes',
                        data: <?php echo json_encode($dist_totals); ?>,
                        backgroundColor: 'rgba(79, 188, 79, 0.7)',
                        borderColor: 'rgba(79, 188, 79, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });

            // Pacientes por faixa etária — gráfico de pizza
            new Chart(document.getElementById('chartAge'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($age_names); ?>,
                    datasets: [{
                        data: <?php echo json_encode($age_totals); ?>,
                        backgroundColor: [
                            'rgba(0, 90, 142, 0.7)',
                            'rgba(79, 188, 79, 0.7)',
                            'rgba(255, 193, 7, 0.7)',
                            'rgba(220, 53, 69, 0.7)',
                            'rgba(108, 117, 125, 0.7)'
                        ]
                    }]
                },
                options: { responsive: true }
            });

            // Consultas por mês — gráfico de linha
            new Chart(document.getElementById('chartCons'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($cons_months); ?>,
                    datasets: [{
                        label: 'Nº de Consultas',
                        data: <?php echo json_encode($cons_totals); ?>,
                        backgroundColor: 'rgba(0, 90, 142, 0.2)',
                        borderColor: 'rgba(0, 90, 142, 1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        </script>
    </div>

<?php include 'footer.php'; ?>