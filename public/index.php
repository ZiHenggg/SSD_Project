<?php
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/auth_check.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.css">
    <script src="bootstrap-offline/js/jquery-3.6.0.js"></script>
    <script src="bootstrap-offline/js/bootstrap.js"></script>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/home.css">
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="content-container m-5">
        <div class="group-container">
            <div class="group-wrapper">
                <div class="title d-flex justify-content-between">
                    <h2 class="m-0">My Current Groups (3)</h2>
                    <a href="#" class="text-decoration-none d-flex">
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
                    <span class="member-count">5/7</span>
                    <div class="img-wrapper leader">
                        <img class="leader-icon w-100" src="img/crown.svg" alt="Leader" />
                    </div>
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
                        <li class="admin">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2301111</a>
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
                    <span class="header">Pending Requests</span>
                    <ol class="pending-request name-list">
                        <li>
                            <div class="request-wrapper">
                                <a class="profile-link text-decoration-none" href="#">Emily Tan</a>
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
                                <a class="profile-link text-decoration-none" href="#">Loh Yong Sheng</a>
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
                        <li class="admin">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2301111</a>
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
                        <li class="admin">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2301111</a>
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

</body>
<script src="js/home.js"></script>

</html>