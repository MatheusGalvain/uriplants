<?php include('functions/sidebar_logic.php'); ?>

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <a class="nav-link" href="welcome.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <a class="nav-link" href="profile.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                    Meu Perfil
                </a>

                <?php if ($isAdmin): ?>
                    <a class="nav-link" href="manage-users.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                        Gerenciar Usuários
                    </a>
                <?php endif; ?>

                <a class="nav-link" href="change-password.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-key"></i></div>
                    Mudar a Senha
                </a>

                <a class="nav-link" href="logout.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                    Sair
                </a>
            </div>
        </div>
    </nav>
</div>
