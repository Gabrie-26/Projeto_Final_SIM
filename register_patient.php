<?php include 'header.php'; ?>
<?php
if (isset($_SESSION['usuario']) && $_SESSION['role'] != 'M') {
    header('Location: index.php');
    exit;
}

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = $_POST['name'];
    $username  = $_POST['username'];
    $password  = $_POST['password'];
    $email     = $_POST['email'];
    $dob       = $_POST['date_of_birth'];
    $sex       = $_POST['sex'];
    $nif       = $_POST['nif'];
    $address   = $_POST['address'];
    $district  = $_POST['district'];
    $allergies = $_POST['allergies'];
    $chronic   = $_POST['chronic_diseases'];

    // Processar foto
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $file_type = $_FILES['photo']['type'];

        if (!in_array($file_type, $allowed_types)) {
            $erro = 'A foto deve ser JPG ou PNG.';
        } else {
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
            $photo = mysqli_real_escape_string($connect, $photo);
        }
    }

    if (!$erro) {
        // Verificar se o username já existe
        $check = mysqli_query($connect, "SELECT ID FROM USERS WHERE USERNAME = '$username'");
        if (mysqli_num_rows($check) > 0) {
            $erro = 'Username já existe, escolhe outro.';
        } else {
            // Inserir em USERS
            if ($photo) {
                $sql_user = "INSERT INTO USERS (NAME, USERNAME, PASSWORD, EMAIL, ROLE, PHOTO) 
                             VALUES ('$name', '$username', SHA2('$password', 256), '$email', 'P', '$photo')";
            } else {
                $sql_user = "INSERT INTO USERS (NAME, USERNAME, PASSWORD, EMAIL, ROLE) 
                             VALUES ('$name', '$username', SHA2('$password', 256), '$email', 'P')";
            }

            if (mysqli_query($connect, $sql_user)) {
                $new_id = mysqli_insert_id($connect);

                // Inserir em PATIENTS
                $sql_patient = "INSERT INTO PATIENTS (USER_ID, DATE_OF_BIRTH, SEX, NIF, ADDRESS, DISTRICT, ALLERGIES, CHRONIC_DISEASES) 
                                VALUES ('$new_id', '$dob', '$sex', '$nif', '$address', '$district', '$allergies', '$chronic')";

                if (mysqli_query($connect, $sql_patient)) {
                    if (!isset($_SESSION['usuario'])) {
                        $_SESSION['usuario'] = $username;
                        $_SESSION['user_id'] = $new_id;
                        $_SESSION['role']    = 'P';
                        header('Location: index.php?sucesso=1');
                        exit;
                    } else {
                        $sucesso = 'Paciente registado com sucesso!';
                    }
                } else {
                    $erro = 'Erro ao criar ficha de paciente: ' . mysqli_error($connect);
                }
            } else {
                $erro = 'Erro ao criar utilizador: ' . mysqli_error($connect);
            }
        }
    }
}
?>

    <div class="contents user">
        <h2>Registar Novo Paciente</h2>

        <?php if ($erro): ?>
            <p style="color: #b60b0b;"><?php echo $erro; ?></p>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <p style="color: #4fbc4f;"><?php echo $sucesso; ?></p>
        <?php endif; ?>

        <form action="register_patient.php" method="post" enctype="multipart/form-data">

            <h3>Dados de Acesso</h3>
            <table>
                <tr><th>Campo</th><th>Valor</th></tr>
                <tr>
                    <td>Nome</td>
                    <td><input type="text" name="name" required></td>
                </tr>
                <tr>
                    <td>Username</td>
                    <td><input type="text" name="username" required></td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td><input type="password" name="password" required></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><input type="email" name="email"></td>
                </tr>
                <tr>
                    <td>Foto</td>
                    <td><input type="file" name="photo" accept=".jpg,.jpeg,.png"></td>
                </tr>
            </table>

            <h3>Dados Clínicos</h3>
            <table>
                <tr><th>Campo</th><th>Valor</th></tr>
                <tr>
                    <td>Data de Nascimento</td>
                    <td><input type="date" name="date_of_birth"></td>
                </tr>
                <tr>
                    <td>Sexo</td>
                    <td>
                        <select name="sex">
                            <option value="M">Masculino</option>
                            <option value="F">Feminino</option>
                            <option value="O">Outro</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>NIF</td>
                    <td><input type="text" name="nif" maxlength="9"></td>
                </tr>
                <tr>
                    <td>Morada</td>
                    <td><input type="text" name="address"></td>
                </tr>
                <tr>
                    <td>Distrito</td>
                    <td><input type="text" name="district"></td>
                </tr>
                <tr>
                    <td>Alergias</td>
                    <td><textarea name="allergies" rows="3"></textarea></td>
                </tr>
                <tr>
                    <td>Doenças Crónicas</td>
                    <td><textarea name="chronic_diseases" rows="3"></textarea></td>
                </tr>
            </table>

            <div style="margin-top: 15px;">
                <button type="submit">Registar Paciente</button>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <button type="button" onclick="window.location.href='users.php?pagina=1'">Cancelar</button>
                <?php else: ?>
                    <button type="button" onclick="window.location.href='login.php'">Cancelar</button>
                <?php endif; ?>
            </div>

        </form>
    </div>

<?php include 'footer.php'; ?>