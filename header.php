<?php
if (session_status() == PHP_SESSION_NONE)
    session_start(); ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Clínica SIM-FCT</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div style="width: 100%;max-width:750px; margin:auto;">
    <div class="logo">
        <a href="https://www.fct.unl.pt" >
            <img src="logo_fctunl.png" alt="Logótipo FCT" height="56" width="344" style="float:left;" class="fct-logo">
        </a>
        <h1>Clínica SIM-FCT</h1>
    </div>
<?php
     if (isset($_SESSION['usuario'])){
         if ($_SESSION['role'] == 'G') {
             include 'nav_gestor.php';
         }
         elseif ($_SESSION['role'] == 'M') {
             include 'nav_medico.php';
         }
         elseif ($_SESSION['role'] == 'P') {
             include 'nav_paciente.php';
         }
     }
     else {
         include 'nav_public.php';
     }
?>
