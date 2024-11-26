<?php include('functions/user_logic.php'); ?>

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu d-flex flex-column">
            <div class="nav flex-grow-1">
                <a class="nav-link text-white" href="welcome.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt text-light"></i></div>
                    Início
                </a>
                <a class="nav-link text-white" href="profile.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-user text-primary"></i></div>
                    Meu Perfil
                </a>

                <div class="nav-item">
                    <a class="nav-link dropdown-toggle text-white" data-bs-toggle="collapse" href="#plantsMenu" aria-expanded="false" aria-controls="plantsMenu">
                        <div class="sb-nav-link-icon"><i class="fas fa-leaf text-success"></i></div>
                        Gerenciar Plantas
                    </a>
                    <div class="collapse" id="plantsMenu">
                        <ul class="nav flex-column l-2">
                            <li class="nav-item">
                                <a class="dropdown-item text-white ps-5" href="plants.php">
                                    Plantas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-white ps-5" href="divisions.php">
                                    Divisões
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-white ps-5" href="classes.php">
                                    Classes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-white ps-5" href="orders.php">
                                    Ordem
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-white ps-5" href="families.php">
                                    Família
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-white ps-5" href="genus.php">
                                    Gênero
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item text-white ps-5" href="qrcode.php">
                                    QR Code URL
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <?php if ($isAdmin): ?>
                    <a class="nav-link text-white" href="manage-users.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-users text-primary"></i></div>
                        Gerenciar Usuários
                    </a>

                    <a class="nav-link text-white" href="auditlogs.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-history text-primary"></i></div>
                        Histórico de Logs
                    </a>
                <?php endif; ?>
            </div>
            <div class="nav mt-auto">
                <a class="nav-link text-white" href="logout.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt text-primary"></i></div>
                    Sair
                </a>
            </div>
        </div>
    </nav>
</div>