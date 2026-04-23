<?php include 'header.php'; ?>
<?php if (isset($_GET['erro'])): ?>
    <p style="color: #b60b0b; text-align: center;">Username ou password incorretos!</p>
    <p style="color: #b60b0b; text-align: center;">Tente novamente</p>
<?php endif; ?>
    <div class="contents login-container">
        <form class="login-form" action="check_login.php" method="post">
            <div class="login-field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username">
            </div>
            <div class="login-field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="login-button">
                <button type="submit">login</button>
            </div>
        </form>
    </div>

<?php include 'footer.php'; ?>