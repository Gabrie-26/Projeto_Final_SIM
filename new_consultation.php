<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['usuario']) || $_SESSION['role'] != 'M') {
    header('Location: index.php');
    exit;
}

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

$erro = '';
$sucesso = '';
$fase = isset($_POST['fase']) ? (int)$_POST['fase'] : 1;
$patient_id = isset($_POST['patient_id']) ? (int)$_POST['patient_id'] : 0;

// FASE 2 — Guardar consulta
if ($fase == 2 && isset($_POST['guardar'])) {
    $medic_id     = $_SESSION['user_id'];
    $weight       = $_POST['weight'];
    $height       = $_POST['height'];
    $temperature  = $_POST['temperature'];
    $blood_pressure = $_POST['blood_pressure'];
    $summary      = $_POST['summary'];
    $medications  = isset($_POST['medications']) ? $_POST['medications'] : [];

    // Processar imagem
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $erro = 'A imagem deve ser JPG ou PNG.';
        } else {
            $image = file_get_contents($_FILES['image']['tmp_name']);
            $image = mysqli_real_escape_string($connect, $image);
        }
    }

    if (!$erro) {
        if ($image) {
            $sql_cons = "INSERT INTO CONSULTATIONS (MEDIC_ID, PATIENT_ID, WEIGHT, HEIGHT, TEMPERATURE, BLOOD_PRESSURE, SUMMARY, IMAGE)
                         VALUES ('$medic_id', '$patient_id', '$weight', '$height', '$temperature', '$blood_pressure', '$summary', '$image')";
        } else {
            $sql_cons = "INSERT INTO CONSULTATIONS (MEDIC_ID, PATIENT_ID, WEIGHT, HEIGHT, TEMPERATURE, BLOOD_PRESSURE, SUMMARY)
                         VALUES ('$medic_id', '$patient_id', '$weight', '$height', '$temperature', '$blood_pressure', '$summary')";
        }

        if (mysqli_query($connect, $sql_cons)) {
            $consultation_id = mysqli_insert_id($connect);

            // Inserir medicamentos selecionados
            foreach ($medications as $med_id) {
                $med_id = (int)$med_id;
                $sql_pres = "INSERT INTO PRESCRIPTIONS (CONSULTATION_ID, MEDICATION_ID, START_DATE)
                             VALUES ('$consultation_id', '$med_id', CURDATE())";
                mysqli_query($connect, $sql_pres);
            }

            $sucesso = 'Consulta registada com sucesso!';
            $fase = 1; // Voltar à fase 1 após guardar
            $patient_id = 0;
        } else {
            $erro = 'Erro ao guardar consulta: ' . mysqli_error($connect);
        }
    }
}

// Buscar lista de pacientes para fase 1
$patients = mysqli_query($connect, "SELECT U.ID, U.NAME, U.USERNAME 
                                     FROM USERS U 
                                     INNER JOIN PATIENTS P ON U.ID = P.USER_ID 
                                     WHERE U.ROLE = 'P' 
                                     ORDER BY U.NAME");

// Buscar lista de medicamentos para fase 2
$medications_list = mysqli_query($connect, "SELECT ID, NAME, LEAFLET FROM MEDICATIONS ORDER BY NAME");
$medications_array = [];
while ($med = mysqli_fetch_assoc($medications_list)) {
    $medications_array[] = $med;
}

// Buscar dados do paciente selecionado para fase 2
$patient_data = null;
if ($patient_id > 0) {
    $sql_patient = "SELECT U.NAME, P.DATE_OF_BIRTH, P.GENDER, P.ALLERGIES, P.CHRONIC_DISEASES
                    FROM USERS U
                    INNER JOIN PATIENTS P ON U.ID = P.USER_ID
                    WHERE U.ID = '$patient_id'";
    $res = mysqli_query($connect, $sql_patient);
    $patient_data = mysqli_fetch_assoc($res);
}
?>

    <div class="contents user">
        <h2>Nova Consulta</h2>

        <?php if ($erro): ?>
            <p style="color: #b60b0b;"><?php echo $erro; ?></p>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <p style="color: #4fbc4f;"><?php echo $sucesso; ?></p>
        <?php endif; ?>

        <?php if ($fase == 1): ?>
            <form action="new_consultation.php" method="post">
                <input type="hidden" name="fase" value="2">
                <table>
                    <tr><th>Campo</th><th>Valor</th></tr>
                    <tr>
                        <td>Paciente</td>
                        <td>
                            <select name="patient_id" required>
                                <option value="">-- Seleciona um paciente --</option>
                                <?php while ($p = mysqli_fetch_assoc($patients)): ?>
                                    <option value="<?php echo $p['ID']; ?>">
                                        <?php echo $p['NAME'] . ' (' . $p['USERNAME'] . ')'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <div style="margin-top: 15px;">
                    <button type="submit">Seguinte »</button>
                    <button type="button" onclick="window.location.href='register_patient.php'">Novo Paciente</button>
                </div>
            </form>

        <?php elseif ($fase == 2 && $patient_id > 0 && $patient_data): ?>
            <p><strong>Paciente:</strong> <?php echo $patient_data['NAME']; ?></p>

            <?php if ($patient_data['ALLERGIES']): ?>
                <p style="color: #b60b0b;"><strong>⚠ Alergias:</strong> <?php echo $patient_data['ALLERGIES']; ?></p>
            <?php endif; ?>
            <?php if ($patient_data['CHRONIC_DISEASES']): ?>
                <p style="color: #b60b0b;"><strong>⚠ Doenças Crónicas:</strong> <?php echo $patient_data['CHRONIC_DISEASES']; ?></p>
            <?php endif; ?>

            <form action="new_consultation.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="fase" value="2">
                <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                <input type="hidden" name="guardar" value="1">

                <h3>Indicadores Médicos</h3>
                <table>
                    <tr><th>Campo</th><th>Valor</th></tr>
                    <tr>
                        <td>Peso (kg)</td>
                        <td><input type="number" step="0.1" name="weight"></td>
                    </tr>
                    <tr>
                        <td>Altura (cm)</td>
                        <td><input type="number" step="0.1" name="height"></td>
                    </tr>
                    <tr>
                        <td>Temperatura (°C)</td>
                        <td><input type="number" step="0.1" name="temperature"></td>
                    </tr>
                    <tr>
                        <td>Pressão Arterial</td>
                        <td><input type="text" name="blood_pressure" placeholder="ex: 120/80"></td>
                    </tr>
                    <tr>
                        <td>Imagem</td>
                        <td><input type="file" name="image" accept=".jpg,.jpeg,.png"></td>
                    </tr>
                </table>

                <h3>Medicamentos</h3>

                <!-- Formulário inline para adicionar medicamento -->
                <div id="novo-med-form" style="display:none; background:#e8f4e8; padding:10px; margin-bottom:10px; border-radius:4px;">
                    <strong>Novo Medicamento</strong><br><br>
                    <label>Nome: <input type="text" id="novo-med-nome" style="width:200px;"></label>
                    &nbsp;
                    <label>Link Folheto: <input type="url" id="novo-med-leaflet" placeholder="https://..." style="width:250px;"></label>
                    &nbsp;
                    <button type="button" onclick="adicionarMedicamento()">Adicionar</button>
                    <button type="button" onclick="document.getElementById('novo-med-form').style.display='none'">Cancelar</button>
                    <p id="novo-med-erro" style="color:#b60b0b; margin-top:5px;"></p>
                </div>

                <button type="button" onclick="document.getElementById('novo-med-form').style.display='block'">
                     Novo Medicamento
                </button>

                <table id="tabela-medicamentos" style="margin-top:10px;">
                    <tr><th>Selecionar</th><th>Nome</th><th>Folheto</th></tr>
                    <?php foreach ($medications_array as $med): ?>
                        <tr>
                            <td><input type="checkbox" name="medications[]" value="<?php echo $med['ID']; ?>"></td>
                            <td><?php echo $med['NAME']; ?></td>
                            <td>
                                <?php if ($med['LEAFLET']): ?>
                                    <a href="<?php echo $med['LEAFLET']; ?>" target="_blank">Ver Folheto</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <script>
                    function adicionarMedicamento() {
                        const nome    = document.getElementById('novo-med-nome').value.trim();
                        const leaflet = document.getElementById('novo-med-leaflet').value.trim();
                        const erro    = document.getElementById('novo-med-erro');

                        if (!nome) {
                            erro.textContent = 'O nome é obrigatório.';
                            return;
                        }

                        const formData = new FormData();
                        formData.append('name', nome);
                        formData.append('leaflet', leaflet);

                        fetch('add_medication.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.erro) {
                                    erro.textContent = data.erro;
                                } else {
                                    // Adiciona nova linha à tabela sem recarregar a página
                                    const tabela = document.getElementById('tabela-medicamentos');
                                    const novaLinha = tabela.insertRow(-1);
                                    novaLinha.innerHTML = `
                <td><input type="checkbox" name="medications[]" value="${data.id}" checked></td>
                <td>${data.name}</td>
                <td>${leaflet ? '<a href="' + leaflet + '" target="_blank">Ver Folheto</a>' : '-'}</td>
            `;

                                    // Limpar e fechar o formulário
                                    document.getElementById('novo-med-nome').value = '';
                                    document.getElementById('novo-med-leaflet').value = '';
                                    erro.textContent = '';
                                    document.getElementById('novo-med-form').style.display = 'none';
                                }
                            })
                            .catch(() => {
                                erro.textContent = 'Erro de comunicação com o servidor.';
                            });
                    }
                </script>

                <h3>Resumo da Consulta</h3>
                <table>
                    <tr><th>Campo</th><th>Valor</th></tr>
                    <tr>
                        <td>Resumo</td>
                        <td><textarea name="summary" rows="5" style="width:300px;"></textarea></td>
                    </tr>
                </table>

                <div style="margin-top: 15px;">
                    <button type="submit">Guardar Consulta</button>
                    <button type="button" onclick="window.location.href='new_consultation.php'">Cancelar</button>
                </div>
            </form>
        <?php endif; ?>

    </div>

<?php include 'footer.php'; ?>