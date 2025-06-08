<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Mapper\GroupMapper;
use App\Mapper\GroupMembershipMapper;
use App\Control\GroupControl;
use App\Control\GroupMembershipControl;
use App\Boundary\GroupPageController;

$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);

$groupControl = new GroupControl($groupRepo, $groupMembershipRepo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo);

$controller = new GroupPageController($groupControl, $groupMembershipControl, $pdo);

// Get user from session
$student = $_SESSION['user'];
$studentId = (int) $student['id'];

// Fetch groups for this student
$groups = $controller->listUserGroups($studentId);
$roles = $controller->getUserRolesForGroups($studentId, $groups);

$title = "Dashboard";

ob_start(); // Capture the page content
?>

<!-- For now this will be here -->
<div class="grayscreen"></div>
<div class="mx-5">
    <h2>Welcome to your dashboard, Student ID: <?= htmlspecialchars($student['id']) ?></h2>
    <p>Name: <?= htmlspecialchars($student['name']) ?></p>
</div>

<div class="home content-container">
    <div class="group-container">
        <div class="group-wrapper">
            <div class="title d-flex justify-content-between">
                <!-- <h2 class="m-0">My Current Groups (3)</h2> -->
                <h2 class="m-0">My Current Groups (<?= count($groups) ?>)</h2>
                <a href="group_create.php" class="text-decoration-none d-flex">
                    <img src="img/plus.svg" alt="Add Group" class="w-100 add-group" />
                </a>
            </div>
            <!-- <div class="group-item selected" data-group-id="group1">
                <span class="group-name header">[24/25 T3]-ICT2216-P1-G4 (TRIAL)</span>
                <span class="module">ICT2116, Secure Software Development</span>
                <span class="acad-term">[24/25 T3]</span>
                <span class="member-count">5/7</span>
                <div class="img-wrapper leader">
                    <img class="leader-icon w-100" src="img/crown.svg" alt="Leader" />
                </div>
            </div>
            <div class="group-item" data-group-id="group2">
                <span class="group-name header">[24/25 T3]-ICT2216-P1-G4</span>
                <span class="module">ICT2116, Secure Software Development</span>
                <span class="acad-term">[24/25 T3]</span>
                <span class="member-count">3/7</span>
            </div>
            <div class="group-item" data-group-id="group3">
                <span class="group-name header">[24/25 T3]-ICT2216-P1-G4</span>
                <span class="module">ICT2116, Secure Software Development</span>
                <span class="acad-term">[24/25 T3]</span>
                <span class="member-count">5/7</span>
                <div class="img-wrapper leader">
                    <img class="leader-icon w-100" src="img/crown.svg" alt="Leader" />
                </div>
            </div> -->
            <?php if (count($groups) > 0): ?>
                <?php foreach ($groups as $group): ?>
                    <div class="group-item" data-group-id="<?= $group->getGroupId() ?>">
                        <span class="group-name header"><?= htmlspecialchars($group->getGroupName()) ?></span>
                        <span class="module"><?= htmlspecialchars($group->getModuleCode()) ?></span>
                        <span class="acad-term">[<?= htmlspecialchars($group->getAcadYear()) ?>
                            T<?= htmlspecialchars($group->getTrimester()) ?>]</span>
                        <span class="member-count"><?= $group->getNoOfMembers() ?>/<?= $group->getMaxMembers() ?></span>
                        <?php
                        if ($roles[$group->getGroupId()] === 'admin'): ?>
                            <div class="img-wrapper leader">
                                <img class="leader-icon w-100" src="img/crown.svg" alt="Leader" />
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You are not in any groups yet.</p>
            <?php endif; ?>

        </div>
    </div>
    <!-- Pending - TO BE ADDED -->
    <div class="info-container">
        <!-- All content inside here for now is static or placeholder -->
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
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2308888</a>
                        </div>
                    </li>
                    <li>
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2302222</a>
                            <div class="reply-section my-2 py-1">
                                <a href="#"
                                    class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                    Review
                                </a>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2303333</a>
                            <div class="reply-section my-2 py-1">
                                <a href="#"
                                    class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                    Review
                                </a>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2304444</a>
                            <div class="reply-section my-2 py-1">
                                <a href="#"
                                    class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                    Review
                                </a>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2305555</a>
                            <div class="reply-section my-2 py-1">
                                <a href="#"
                                    class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                    Review
                                </a>
                            </div>
                        </div>
                    </li>
                </ol>
                <div class="delete-section" data-group-id="group1">
                    <div
                        class="text-decoration-none text-center delete-button d-flex align-items-center justify-content-center">
                        Delete Group
                    </div>
                </div>
                <div class="delete-modal" data-group-id="group1">
                    <div class="delete-group-wrapper">
                        <form class="delete-form" action="" method="post">
                            <div class="delete-group-header">
                                <h5 class="delete-group-title"><strong>Delete “[24/25 T3]-ICT2216-P1-G4”?</strong></h5>
                            </div>
                            <div class="delete-group-body">
                                This action cannot be undone. Current members will have to find another group.
                            </div>
                            <div class="my-4">
                                <input required type="checkbox" class="delete-group-checkbox" id="placeholder-delete-id"
                                    name="delete group" value="true">
                                <span class="delete-group-ack">I acknowledge the above and agree to delete the
                                    group.</span>
                            </div>
                            <div class="delete-group-footer d-flex flex-row align-items-center justify-content-end">
                                <div class="cancel-section mx-4">
                                    <span class="text-center"><small>Cancel</small></span>
                                </div>
                                <div class="confirm-section">
                                    <button type="submit"
                                        class="text-decoration-none text-center delete-button d-flex align-items-center justify-content-center">
                                        Confirm
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="info-item">
                <span class="header">Current Members</span>
                <ol class="current-members name-list">
                    <li class="admin">
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2301111</a>
                            <div class="reply-section my-2 py-1">
                                <a href="#"
                                    class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                    Review
                                </a>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2302222</a>
                            <div class="reply-section my-2 py-1">
                                <a href="#"
                                    class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                    Review
                                </a>
                            </div>
                        </div>
                    </li>
                    <li class="user">
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2308888</a>
                        </div>
                    </li>
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
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2308888</a>
                        </div>
                    </li>
                    <li>
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2302222</a>
                            <div class="reply-section my-2 py-1">
                                <a href="#"
                                    class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                    Review
                                </a>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2303333</a>
                            <div class="reply-section my-2 py-1">
                                <a href="#"
                                    class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                    Review
                                </a>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2304444</a>
                            <div class="reply-section my-2 py-1">
                                <a href="#"
                                    class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                    Review
                                </a>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="#">XXXXX, 2305555</a>
                            <div class="reply-section my-2 py-1">
                                <a href="#"
                                    class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                    Review
                                </a>
                            </div>
                        </div>
                    </li>
                </ol>
                <div class="delete-section" data-group-id="group3">
                    <div
                        class="text-decoration-none text-center delete-button d-flex align-items-center justify-content-center">
                        Delete Group
                    </div>
                </div>
                <div class="delete-modal" data-group-id="group3">
                    <div class="delete-group-wrapper">
                        <form class="delete-form" action="" method="post">
                            <div class="delete-group-header">
                                <h5 class="delete-group-title"><strong>Delete “[24/25 T3]-ICT2216-P1-G4”?</strong></h5>
                            </div>
                            <div class="delete-group-body">
                                This action cannot be undone. Current members will have to find another group.
                            </div>
                            <div class="my-4">
                                <input required type="checkbox" class="delete-group-checkbox" id="placeholder-delete-id"
                                    name="delete group" value="true">
                                <span class="delete-group-ack">I acknowledge the above and agree to delete the
                                    group.</span>
                            </div>
                            <div class="delete-group-footer d-flex flex-row align-items-center justify-content-end">
                                <div class="cancel-section mx-4">
                                    <span class="text-center"><small>Cancel</small></span>
                                </div>
                                <div class="confirm-section">
                                    <button type="submit"
                                        class="text-decoration-none text-center delete-button d-flex align-items-center justify-content-center">
                                        Confirm
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean(); // Store the captured content
include '_layout.php';     // Inject it into the layout
?>