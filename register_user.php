<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['usuario']) || $_SESSION['role'] != 'G') {
    header('Location: index.php');
    exit;
}

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email    = $_POST['email'];
    $role     = $_POST['role'];

    // Foto
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($_FILES['photo']['type'], $allowed_types)) {
            $erro = 'A foto deve ser JPG ou PNG.';
        } else {
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
            $photo = mysqli_real_escape_string($connect, $photo);
        }
    }

    if (!$erro) {
        $check = mysqli_query($connect, "SELECT ID FROM USERS WHERE USERNAME = '$username'");
        if (mysqli_num_rows($check) > 0) {
            $erro = 'Username já existe, escolhe outro.';
        } else {
            if ($photo) {
                $sql = "INSERT INTO USERS (NAME, USERNAME, PASSWORD, EMAIL, ROLE, PHOTO)
                        VALUES ('$name', '$username', SHA2('$password', 256), '$email', '$role', '$photo')";
            } else {
                $sql = "INSERT INTO USERS (NAME, USERNAME, PASSWORD, EMAIL, ROLE)
                        VALUES ('$name', '$username', SHA2('$password', 256), '$email', '$role')";
            }

            if (mysqli_query($connect, $sql)) {
                $sucesso = 'Utilizador registado com sucesso!';
            } else {
                $erro = 'Erro ao criar utilizador: ' . mysqli_error($connect);
            }
        }
    }
}
?>

    <div class="contents user">
        <h2>Registar Novo Utilizador</h2>

        <?php if ($erro): ?>
            <p style="color: #b60b0b;"><?php echo $erro; ?></p>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <p style="color: #4fbc4f;"><?php echo $sucesso; ?></p>
        <?php endif; ?>

        <form action="register_user.php" method="post" enctype="multipart/form-data">
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
                    <td>Perfil</td>
                    <td>
                        <select name="role">
                            <option value="M">Médico</option>
                            <option value="G">Gestor</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Foto</td>
                    <td><input type="file" name="photo" accept=".jpg,.jpeg,.png"></td>
                </tr>
            </table>

            <div style="margin-top: 15px;">
                <button type="submit">Registar Utilizador</button>
                <button type="button" onclick="window.location.href='users.php?pagina=1'">Cancelar</button>
            </div>
        </form>
    </div>

<?php include 'footer.php'; ?>