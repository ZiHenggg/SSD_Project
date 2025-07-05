<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';

use App\SessionManager;

// ===== SESSION STUFF =====
SessionManager::start();
$user = SessionManager::getUser();
$isLoggedIn = $user && isset($user['email']);
$queryValue = $_GET['query'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand logo-wrapper" href="dashboard.php">
            <img class="logo" src="img/logo.svg" alt="Logo" />
            <span class="text">GROUPMATES</span>
        </a>

        <?php if ($isLoggedIn): ?>
            <div class="nav-group">
                <form class="search-wrapper d-flex m-0" role="search" method="GET" action="groups.php">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CsrfManager::generateToken()) ?>" /> 
                    <input 
                        class="search form-control me-2" 
                        type="search" 
                        name="query" 
                        placeholder="Search by module name"
                        value="<?= htmlspecialchars($queryValue) ?>"
                        aria-label="Search"
                    />
                    <button type="submit">
                        <img class="w-100" src="img/search.svg" />
                    </button>
                </form>

                <div class="nav-link dropdown">
                    <div class="profile dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="img/profile.svg" alt="Profile" />
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <span class="dropdown-item-text">
                                <?= htmlspecialchars($user['name']) ?>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>

        <?php else: ?>
            <?php if ($currentPage === 'register.php'): ?>
                <div class="nav-group ms-auto">
                    <a class="btn btn-outline-primary" href="login.php">Login</a>
                </div>
            <?php elseif (!in_array($currentPage, ['setup_2fa.php', 'verify_2fa.php'])): ?>
                <div class="nav-group ms-auto">
                    <a class="btn btn-outline-primary" href="register.php">No account? Register</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</nav>
