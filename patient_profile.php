<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['usuario']) || $_SESSION['role'] != 'P') {
    header('Location: index.php');
    exit;
}

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

$user_id = $_SESSION['user_id'];
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = $_POST['name'];
    $email     = $_POST['email'];
    $dob       = $_POST['date_of_birth'];
    $gender       = $_POST['GENDER'];
    $nif       = $_POST['nif'];
    $address   = $_POST['address'];
    $district  = $_POST['district'];
    $allergies = $_POST['allergies'];
    $chronic   = $_POST['chronic_diseases'];

    // Atualizar foto se foi enviada
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($_FILES['photo']['type'], $allowed_types)) {
            $erro = 'A foto deve ser JPG ou PNG.';
        } else {
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
            $photo = mysqli_real_escape_string($connect, $photo);
            mysqli_query($connect, "UPDATE USERS SET PHOTO = '$photo' WHERE ID = '$user_id'");
        }
    }

    // Atualizar password se foi preenchida
    if (!$erro && !empty($_POST['password'])) {
        $password = $_POST['password'];
        mysqli_query($connect, "UPDATE USERS SET PASSWORD = SHA2('$password', 256) WHERE ID = '$user_id'");
    }

    if (!$erro) {
        // Atualizar USERS
        $sql_user = "UPDATE USERS SET NAME = '$name', EMAIL = '$email' WHERE ID = '$user_id'";

        if (mysqli_query($connect, $sql_user)) {
            // Atualizar PATIENTS
            $sql_patient = "UPDATE PATIENTS SET 
                            DATE_OF_BIRTH = '$dob',
                            GENDER = '$gender',
                            NIF = '$nif',
                            ADDRESS = '$address',
                            DISTRICT = '$district',
                            ALLERGIES = '$allergies',
                            CHRONIC_DISEASES = '$chronic'
                            WHERE USER_ID = '$user_id'";

            if (mysqli_query($connect, $sql_patient)) {
                $sucesso = 'Ficha atualizada com sucesso!';
            } else {
                $erro = 'Erro ao atualizar ficha: ' . mysqli_error($connect);
            }
        } else {
            $erro = 'Erro ao atualizar dados: ' . mysqli_error($connect);
        }
    }
}

// Buscar dados atuais do paciente
$sql = "SELECT U.ID, U.NAME, U.USERNAME, U.EMAIL, U.PHOTO, U.CREATION_DATE,
               P.DATE_OF_BIRTH, P.GENDER, P.NIF, P.ADDRESS, P.DISTRICT, P.ALLERGIES, P.CHRONIC_DISEASES
        FROM USERS U
        INNER JOIN PATIENTS P ON U.ID = P.USER_ID
        WHERE U.ID = '$user_id'";

$result = mysqli_query($connect, $sql);
$data = mysqli_fetch_assoc($result);
?>

    <div class="contents user">
        <h2>A Minha Ficha</h2>

        <?php if ($erro): ?>
            <p style="color: #b60b0b;"><?php echo $erro; ?></p>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <p style="color: #4fbc4f;"><?php echo $sucesso; ?></p>
        <?php endif; ?>

        <?php if ($data['PHOTO']): ?>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($data['PHOTO']); ?>"
                 alt="Foto de perfil"
                 style="width:120px; height:120px; object-fit:cover; border-radius:50%; margin-bottom:15px;">
        <?php else: ?>
            <p style="color: #888;">Sem foto de perfil.</p>
        <?php endif; ?>

        <form action="patient_profile.php" method="post" enctype="multipart/form-data">

            <h3>Dados de Acesso</h3>
            <table>
                <tr><th>Campo</th><th>Valor</th></tr>
                <tr>
                    <td>Nome</td>
                    <td><input type="text" name="name" value="<?php echo $data['NAME']; ?>" required></td>
                </tr>
                <tr>
                    <td>Username</td>
                    <td><input type="text" value="<?php echo $data['USERNAME']; ?>" disabled></td>
                </tr>
                <tr>
                    <td>Nova Password</td>
                    <td><input type="password" name="password" placeholder="Deixa em branco para não alterar"></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><input type="email" name="email" value="<?php echo $data['EMAIL']; ?>"></td>
                </tr>
                <tr>
                    <td>Foto</td>
                    <td><input type="file" name="photo" accept=".jpg,.jpeg,.png"></td>
                </tr>
                <tr>
                    <td>Membro desde</td>
                    <td><input type="text" value="<?php echo $data['CREATION_DATE']; ?>" disabled></td>
                </tr>
            </table>

            <h3>Dados Clínicos</h3>
            <table>
                <tr><th>Campo</th><th>Valor</th></tr>
                <tr>
                    <td>Data de Nascimento</td>
                    <td><input type="date" name="date_of_birth" value="<?php echo $data['DATE_OF_BIRTH']; ?>"></td>
                </tr>
                <tr>
                    <td>Género</td>
                    <td>
                        <select name="gender">
                            <option value="M" <?php echo $data['GENDER'] == 'M' ? 'selected' : ''; ?>>Masculino</option>
                            <option value="F" <?php echo $data['GENDER'] == 'F' ? 'selected' : ''; ?>>Feminino</option>
                            <option value="O" <?php echo $data['GENDER'] == 'O' ? 'selected' : ''; ?>>Outro</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>NIF</td>
                    <td><input type="text" name="nif" maxlength="9" value="<?php echo $data['NIF']; ?>"></td>
                </tr>
                <tr>
                    <td>Morada</td>
                    <td><input type="text" name="address" value="<?php echo $data['ADDRESS']; ?>"></td>
                </tr>
                <tr>
                    <td>Distrito</td>
                    <td><input type="text" name="district" value="<?php echo $data['DISTRICT']; ?>"></td>
                </tr>
                <tr>
                    <td>Alergias</td>
                    <td><textarea name="allergies" rows="3"><?php echo $data['ALLERGIES']; ?></textarea></td>
                </tr>
                <tr>
                    <td>Doenças Crónicas</td>
                    <td><textarea name="chronic_diseases" rows="3"><?php echo $data['CHRONIC_DISEASES']; ?></textarea></td>
                </tr>
            </table>

            <div style="margin-top: 15px;">
                <button type="submit">Guardar Alterações</button>
            </div>

        </form>
    </div>

<?php include 'footer.php'; ?>