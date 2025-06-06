<?php
// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user']);
?>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand logo-wrapper" href="index.php">
            <img class="logo" src="img/logo.svg" alt="Logo" />
            <span class="text">GROUPMATES</span>
        </a>

        <?php if ($isLoggedIn): ?>
            <div class="nav-group">
                <a class="nav-link" href="#">Past Groups</a>
                <form class="search-wrapper d-flex" role="search">
                    <input class="search form-control me-2" type="search" placeholder="Search for groups"
                        aria-label="Search" />
                    <button type="submit"><img class="w-100" src="img/search.svg" /></button>
                </form>
                <!-- <a class="nav-link profile" href="#">
                    <img class="w-100" src="img/profile.svg" alt="Profile" />
                </a> -->
                <div class="nav-link dropdown">
                    <div href="#" class="profile dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="img/profile.svg" alt="Profile" />
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <span class="dropdown-item-text">
                                <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                            </span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <div class="nav-group ms-auto">
                <a class="btn btn-outline-primary" href="register.php">No account? Register</a>
            </div>
        <?php endif; ?>
    </div>
</nav>