<?php include 'header.php' ?>

<?php
// Só o Gestor pode aceder - redireciona os outros
if (!isset($_SESSION['usuario']) || $_SESSION['role'] != 'G') {
    header('Location: index.php');
    exit;
}
?>

<?php
$connect = mysqli_connect('localhost', 'root', '', 'proj_sim')
or die('Error connecting to the server: ' . mysqli_error($connect));

$por_pagina = 10;
$maximo = 100;

// Contar quantos utilizadores existem realmente (máximo 100)
$count_result = mysqli_query($connect, "SELECT COUNT(*) as total FROM USERS");
$count_row = mysqli_fetch_assoc($count_result);
$total_users = min($count_row['total'], $maximo);

// Calcular total de páginas com base nos utilizadores reais
$pagina_total = ceil($total_users / $por_pagina);

// Página atual
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_atual < 1) $pagina_atual = 1;
if ($pagina_atual > $pagina_total) $pagina_atual = $pagina_total;

// Offset
$offset = ($pagina_atual - 1) * $por_pagina;

$sql = "SELECT ID, NAME, USERNAME, ROLE, CREATION_DATE FROM USERS
        LIMIT $por_pagina OFFSET $offset";

$result = mysqli_query($connect, $sql)
or die('The query failed: ' . mysqli_error($connect));
?>

    <div class="contents user">

        <h2>Lista de Utilizadores</h2>

        <table  >
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Username</th>
                <th>Função</th>
                <th>Data de Registo</th>
            </tr>

            <?php while ($row = mysqli_fetch_array($result)) { ?>
                <tr>
                    <td><?php echo $row['ID']; ?></td>
                    <td><?php echo $row['NAME']; ?></td>
                    <td><?php echo $row['USERNAME']; ?></td>
                    <?php
                    $role_label = ['M' => 'Médico', 'P' => 'Paciente', 'G' => 'Gestor'];
                    ?>
                    <td><?php echo $role_label[$row['ROLE']] ?></td>
                    <td><?php echo $row['CREATION_DATE']; ?></td>
                </tr>
            <?php } ?>

        </table>

        <?php if ($pagina_total > 1): ?>
            <div class="paginacao">
                <?php if ($pagina_atual > 1): ?>
                    <a href="?pagina=<?php echo $pagina_atual - 1; ?>">« Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagina_total; $i++): ?>
                    <a href="?pagina=<?php echo $i; ?>"
                       class="<?php echo $i == $pagina_atual ? 'pagina-ativa' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($pagina_atual < $pagina_total): ?>
                    <a href="?pagina=<?php echo $pagina_atual + 1; ?>">Próximo »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

<?php include 'footer.php' ?>