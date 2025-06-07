<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

$student = $_SESSION['user'];
$title = "Dashboard";

ob_start(); // Capture the page content
?>

<!-- For now this will be here -->
<div class="mx-5">
    <h2>Welcome to your dashboard, Student ID: <?= htmlspecialchars($student['id']) ?></h2>
    <p>Name: <?= htmlspecialchars($student['name']) ?></p>
</div>

<div class="content-container">
    <div class="group-container">
        <div class="group-wrapper">
            <div class="title d-flex justify-content-between">
                <h2 class="m-0">My Current Groups (3)</h2>
                <a href="group_create.php" class="text-decoration-none d-flex">
                    <img src="img/plus.svg" alt="Add Group" class="w-100 add-group" />
                </a>
            </div>
            <div class="group-item selected">
                <span class="group-name header">[24/25 T3]-ICT2216-P1-G4</span>
                <span class="module">ICT2116, Secure Software Development</span>
                <span class="acad-term">[24/25 T3]</span>
                <span class="member-count">5/7</span>
                <div class="img-wrapper leader">
                    <img class="leader-icon w-100" src="img/crown.svg" alt="Leader" />
                </div>
            </div>
            <div class="group-item">
                <span class="group-name header">[24/25 T3]-ICT2216-P1-G4</span>
                <span class="module">ICT2116, Secure Software Development</span>
                <span class="acad-term">[24/25 T3]</span>
                <span class="member-count">3/7</span>
            </div>
            <div class="group-item">
                <span class="group-name header">[24/25 T3]-ICT2216-P1-G4</span>
                <span class="module">ICT2116, Secure Software Development</span>
                <span class="acad-term">[24/25 T3]</span>
                <span class="member-count">5/7</span>
                <div class="img-wrapper leader">
                    <img class="leader-icon w-100" src="img/crown.svg" alt="Leader" />
                </div>
            </div>
        </div>
    </div>
    <div class="info-container">
        <div class="info-wrapper">
            <div class="title d-flex justify-content-between">
                <h2 class="m-0">Group Info</h2>
            </div>
            <div class="info-item showing">
                <span class="header">Pending Requests</span>
                <ol class="pending-request name-list">
                    <li>
                        <div class="request-wrapper">
                            <a class="profile-link text-decoration-none" href="#">Justin Goh</a>
                            <form class="pending-form" action="" method="post">
                                <button class="btn accept-btn" type="submit">
                                    <img src="img/reject.svg" alt="Reject" class="w-100 reject" />
                                </button>
                                <button class="btn reject-btn" type="submit">
                                    <img src="img/accept.svg" alt="Accept" class="w-100 accept" />
                                </button>
                            </form>
                        </div>
                    </li>
                    <li>
                        <div class="request-wrapper">
                            <a class="profile-link text-decoration-none" href="#">Chua Fang Yi</a>
                            <form class="pending-form" action="" method="post">
                                <button class="btn accept-btn" type="submit">
                                    <img src="img/reject.svg" alt="Reject" class="w-100 reject" />
                                </button>
                                <button class="btn reject-btn" type="submit">
                                    <img src="img/accept.svg" alt="Accept" class="w-100 accept" />
                                </button>
                            </form>
                        </div>
                    </li>
                </ol>

                <span class="header">Current Members</span>
                <ol class="current-members name-list">
                    <li class="user admin">
                        <a class="profile-link text-decoration-none" href="#">XXXXX, 2308888</a>
                        <div class="img-wrapper leader">
                            <img class="leader-icon w-100" src="img/crown.svg" alt="Leader">
                        </div>
                    </li>
                    <li><a class="profile-link text-decoration-none" href="#">XXXXX, 2302222</a></li>
                    <li><a class="profile-link text-decoration-none" href="#">XXXXX, 2303333</a></li>
                    <li><a class="profile-link text-decoration-none" href="#">XXXXX, 2304444</a></li>
                    <li><a class="profile-link text-decoration-none" href="#">XXXXX, 2305555</a></li>
                </ol>
            </div>
            <div class="info-item">
                <span class="header">Current Members</span>
                <ol class="current-members name-list">
                    <li class="admin">
                        <a class="profile-link text-decoration-none" href="#">XXXXX, 2301111</a>
                        <div class="img-wrapper leader">
                            <img class="leader-icon w-100" src="img/crown.svg" alt="Leader">
                        </div>
                    </li>
                    <li><a class="profile-link text-decoration-none" href="#">XXXXX, 2302222</a></li>
                    <li class="user"><a class="profile-link text-decoration-none" href="#">XXXXX, 2308888</a></li>
                </ol>
            </div>
            <div class="info-item">
                <span class="header">Pending Requests</span>
                <ol class="pending-request name-list">
                    <li>
                        <div class="request-wrapper">
                            <a class="profile-link text-decoration-none" href="#">Faith Wong</a>
                            <form class="pending-form" action="" method="post">
                                <button class="btn accept-btn" type="submit">
                                    <img src="img/reject.svg" alt="Reject" class="w-100 reject" />
                                </button>
                                <button class="btn reject-btn" type="submit">
                                    <img src="img/accept.svg" alt="Accept" class="w-100 accept" />
                                </button>
                            </form>
                        </div>
                    </li>
                    <li>
                        <div class="request-wrapper">
                            <a class="profile-link text-decoration-none" href="#">Estelle Lee</a>
                            <form class="pending-form" action="" method="post">
                                <button class="btn accept-btn" type="submit">
                                    <img src="img/reject.svg" alt="Reject" class="w-100 reject" />
                                </button>
                                <button class="btn reject-btn" type="submit">
                                    <img src="img/accept.svg" alt="Accept" class="w-100 accept" />
                                </button>
                            </form>
                        </div>
                    </li>
                </ol>

                <span class="header">Current Members</span>
                <ol class="current-members name-list">
                    <li class="user admin">
                        <a class="profile-link text-decoration-none" href="#">XXXXX, 2308888</a>
                        <div class="img-wrapper leader">
                            <img class="leader-icon w-100" src="img/crown.svg" alt="Leader">
                        </div>
                    </li>
                    <li><a class="profile-link text-decoration-none" href="#">XXXXX, 2302222</a></li>
                    <li><a class="profile-link text-decoration-none" href="#">XXXXX, 2303333</a></li>
                    <li><a class="profile-link text-decoration-none" href="#">XXXXX, 2304444</a></li>
                    <li><a class="profile-link text-decoration-none" href="#">XXXXX, 2305555</a></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean(); // Store the captured content
include '_layout.php';     // Inject it into the layout
?>