<?php include 'header.php' ?>
<?php if (isset($_GET['sucesso'])): ?>
    <p style="color: #4fbc4f; text-align: center;">Login bem sucedido! Bem-vindo, admin.</p>
<?php endif; ?>
    <div class="contents">
        <div class="contents-left">
            <h2 id="servicos">Serviços</h2>
            <ul>
                <li>Consultas</li>
                <li>Exames</li>
                <li>Análises</li>
                <li>Apoio ao diagnóstico avançado</li>
            </ul>

            <h2 id="equipa">Equipa</h2>
            <ul>
                <li>Doutor André</li>
                <li>Doutor José</li>
                <li>Doutor Mora</li>
                <li>Doutor Fonseca</li>
            </ul>
        </div>

        <div class="contents-right">
            <h2 id="contactos">Contactos</h2>
            <p>
                Clinica SIM<br>
                Campus da FCT<br>
                2820-516 Caparica
            </p>
        </div>
    </div>
<?php include 'footer.php' ?>