<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['usuario']) || $_SESSION['role'] != 'M') {
    header('Location: index.php');
    exit;
}

$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

$user_id = $_SESSION['user_id'];
$erro    = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];

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
        $sql = "UPDATE USERS SET NAME = '$name', EMAIL = '$email' WHERE ID = '$user_id'";
        if (mysqli_query($connect, $sql)) {
            $sucesso = 'Ficha atualizada com sucesso!';
        } else {
            $erro = 'Erro ao atualizar dados: ' . mysqli_error($connect);
        }
    }
}

// Buscar dados atuais do médico
$sql    = "SELECT ID, NAME, USERNAME, EMAIL, PHOTO, CREATION_DATE FROM USERS WHERE ID = '$user_id'";
$result = mysqli_query($connect, $sql);
$data   = mysqli_fetch_assoc($result);
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

    <form action="medic_profile.php" method="post" enctype="multipart/form-data">
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

        <div style="margin-top: 15px;">
            <button type="submit">Guardar Alterações</button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
