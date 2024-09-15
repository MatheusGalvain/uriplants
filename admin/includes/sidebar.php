<?php include('functions/user_logic.php'); ?>

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <a class="nav-link" href="welcome.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt text-light"></i></div>
                    Dashboard
                </a>
                <a class="nav-link" href="profile.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-user text-light"></i></div>
                    Meu Perfil
                </a>

                <li class="nav-item">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="collapse" href="#plantsMenu" aria-expanded="false" aria-controls="plantsMenu">
                        <div class="sb-nav-link-icon"><i class="fas fa-leaf text-success"></i></div>
                        Cadastro's Gerais
                    </a>
                    <div class="collapse" id="plantsMenu">
                        <ul class="nav flex-column l-2">
                            <li class="nav-item">
                                <a class="dropdown-item text-success ps-5" href="classes.php">
                                    Classes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-success ps-5" href="divisions.php">
                                    Divisões
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-success ps-5" href="familyes.php">
                                    Família
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-success ps-5" href="genus.php">
                                    Gênero
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-success ps-5" href="orders.php">
                                    Ordem
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-success ps-5" href="regions.php">
                                    Regiões
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-success ps-5" href="plants.php">
                                    Plantas
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <?php if ($isAdmin): ?>
                    <a class="nav-link" href="manage-users.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-users text-light"></i></div>
                        Gerenciar Usuários
                    </a>

                    <a class="nav-link" href="auditlogs.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-history text-light"></i></div>
                        Histórico de Logs
                    </a>
                <?php endif; ?>

                <a class="nav-link" href="logout.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt text-light"></i></div>
                    Sair
                </a>
            </div>
        </div>
    </nav>
</div>
