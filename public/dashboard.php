<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Mapper\GroupMapper;
use App\Mapper\GroupJoinRequestsMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\StudentMapper;
use App\Mapper\ReviewMapper;
use App\Mapper\ModuleMapper;
use App\Mapper\StudentStatsMapper;
use App\Control\GroupControl;
use App\Control\GroupJoinRequestsControl;
use App\Control\GroupMembershipControl;
use App\Control\StudentControl;
use App\Control\ReviewControl;
use App\Boundary\GroupPageController;
use App\Boundary\GroupMembershipController;
use App\Boundary\StudentPageController;
use App\Boundary\ReviewPageController;

$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$studentRepo = new StudentMapper($pdo);
$reviewRepo = new ReviewMapper($pdo);
$moduleRepo = new moduleMapper($pdo);
$studentStatsRepo = new StudentStatsMapper($pdo);

$groupControl = new GroupControl($groupRepo, $groupMembershipRepo, $moduleRepo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$studentControl = new StudentControl($studentRepo);
$reviewControl = new ReviewControl($reviewRepo, $studentStatsRepo);
$groupController = new GroupPageController($groupControl, $groupMembershipControl, $pdo);
$groupMembershipController = new GroupMembershipController($groupMembershipControl, $pdo);
$studentPageController = new StudentPageController($studentControl);
$reviewPageController = new ReviewPageController($reviewControl, $pdo);

// Get user from session
$student = $_SESSION['user'];
$studentId = (int) $student['id'];

// Fetch groups for this student
$groups = $groupController->listActiveUserGroups($studentId);
$roles = $groupController->getUserRolesForGroups($studentId, $groups);

$title = "Dashboard";

ob_start(); // Capture the page content
?>

<div class="grayscreen"></div>
<div class="mx-5">
    <?php displayErrorMessage(); ?>
    <?php displaySuccessMessage(); ?>
    <h2>Welcome to your dashboard, Student ID: <?= htmlspecialchars($student['id']) ?></h2>
    <p>Name: <?= htmlspecialchars($student['name']) ?></p>
</div>

<div class="home content-container">
    <div class="group-container">
        <div class="group-wrapper">
            <div class="title d-flex justify-content-between">
                <h2 class="m-0">My Current Groups (<?= count($groups) ?>)</h2>
                <a href="group_create.php" class="text-decoration-none d-flex">
                    <img src="img/plus.svg" alt="Add Group" class="w-100 add-group" />
                </a>
            </div>
            <?php if (count($groups) > 0): ?>
                <?php foreach ($groups as $group): ?>
                    <?php
                    $module = $groupControl->getModuleByGroupId($group->getGroupId());
                    ?>
                    <div class="group-item" data-group-id="<?= $group->getGroupId() ?>">
                        <a href="group_info.php?groupId=<?= $group->getGroupId() ?>"
                            class="group-name text-decoration-none header"><?= htmlspecialchars($group->getGroupName()) ?></a>
                        <span class="module"><?= htmlspecialchars($group->getModuleCode()) ?>,
                            <?= htmlspecialchars($module->getModuleName()) ?></span>
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
    <div class="info-container">
        <div class="info-wrapper">
            <div class="title d-flex justify-content-between">
                <h2 class="m-0">Group Info</h2>
            </div>
            <?php if (count($groups) > 0): ?>
                <?php foreach ($groups as $group): ?>
                    <div class="info-item">
                        <?php if ($roles[$group->getGroupId()] === 'admin'): ?>
                            <?php
                            $joinRequestsResponse = $groupMembershipController->displayGroupJoinRequests($group->getGroupId());
                            $requests = $joinRequestsResponse['joinRequest'] ?? [];
                            ?>
                            <span class="header">Pending Requests</span>
                            <?php if (count($requests) > 0): ?>
                                <ol class="pending-request name-list">
                                    <?php foreach ($requests as $request):
                                        $requesterId = $request->getRequesterId();
                                        $requester = $studentPageController->showUserProfile($requesterId);
                                        ?>
                                        <li>
                                            <div class="request-wrapper">
                                                <a class="profile-link text-decoration-none"
                                                    href="profile.php?id=<?= htmlspecialchars($requesterId) ?>"><?= htmlspecialchars($requester->getStudentName()) ?>,
                                                    <?= htmlspecialchars($requesterId) ?></a>
                                                <!-- <form class="pending-form" action="" method="post">
                                                    <button class="btn accept-btn" type="submit">
                                                        <img src="img/reject.svg" alt="Reject" class="w-100 reject" />
                                                    </button>
                                                    <button class="btn reject-btn" type="submit">
                                                        <img src="img/accept.svg" alt="Accept" class="w-100 accept" />
                                                    </button>
                                                </form> -->
                                                <!-- Reject form -->
                                                <form class="pending-form" action="process_reject_request.php" method="post"
                                                    style="display:inline;">
                                                    <input type="hidden" name="requestId" value="<?= $request->getRequestId() ?>">
                                                    <input type="hidden" name="requesterId" value="<?= $requesterId ?>">
                                                    <button class="btn reject-btn" type="submit">
                                                        <img src="img/reject.svg" alt="Reject" class="w-100 reject" />
                                                    </button>
                                                </form>

                                                <!-- Accept form -->
                                                <form class="pending-form" action="process_accept_request.php" method="post"
                                                    style="display:inline;">
                                                    <input type="hidden" name="requestId" value="<?= $request->getRequestId() ?>">
                                                    <input type="hidden" name="requesterId" value="<?= $requesterId ?>">
                                                    <button class="btn accept-btn" type="submit">
                                                        <img src="img/accept.svg" alt="Accept" class="w-100 accept" />
                                                    </button>
                                                </form>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php else: ?>
                                <p>No pending requests.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                        <span class="header">Current Members</span>
                        <ol class="current-members name-list">
                            <?php
                            $groupData = $groupController->displayGroupDetails($group->getGroupId());
                            $groupMembers = $groupData['members'];
                            foreach ($groupMembers as $member):
                                if (($member->getRole() === 'admin') && ($member->getStudentId() == $studentId)): ?>
                                    <li class="user admin">
                                    <?php elseif ($member->getRole() === 'admin'): ?>
                                    <li class="admin">
                                    <?php elseif ($member->getStudentId() == $studentId): ?>
                                    <li class="user">
                                    <?php else: ?>
                                    <li>
                                    <?php endif; ?>
                                    <div class="member-wrapper">
                                        <a class="profile-link text-decoration-none"
                                            href="profile.php?id=<?= htmlspecialchars($member->getStudentId()) ?>"><?= htmlspecialchars($member->getStudentName()) ?>,
                                            <?= htmlspecialchars($member->getStudentId()) ?></a>
                                        <?php if ($member->getStudentId() != $studentId): ?>
                                            <div class="reply-section my-2 py-1">
                                                <form action="review_create.php" method="post">
                                                    <input type="hidden" name="group_id" value="<?= $group->getGroupId() ?>">
                                                    <input type="hidden" name="reviewee_id" value="<?= $member->getStudentId() ?>">
                                                    <?php if ($reviewPageController->onCheckIfReviewed($studentId, $member->getStudentId(), $group->getGroupId())): ?>
                                                        <button class="disabled reply-button">
                                                        <?php else: ?>
                                                            <button type="submit" class="reply-button">
                                                            <?php endif; ?>
                                                            Review
                                                        </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                        <?php if ($roles[$group->getGroupId()] === 'admin'): ?>
                            <div class="delete-section" data-group-id="<?= htmlspecialchars($group->getGroupId()) ?>">
                                <div
                                    class="text-decoration-none text-center delete-button d-flex align-items-center justify-content-center">
                                    Delete Group</div>
                            </div>
                            <div class="delete-modal" data-group-id="<?= htmlspecialchars($group->getGroupId()) ?>">
                                <div class="delete-group-wrapper">
                                    <form class="delete-form" action="process_group_delete.php" method="post">
                                        <input type="hidden" name="group_id" value="<?= htmlspecialchars($group->getGroupId()) ?>">
                                        <div class="delete-group-header">
                                            <h5 class="delete-group-title"><strong>Delete
                                                    “<?= htmlspecialchars($group->getGroupName()) ?>”?</strong></h5>
                                        </div>
                                        <div class="delete-group-body">
                                            This action cannot be undone. Current members will have to find another group.
                                        </div>
                                        <div class="my-4">
                                            <input required type="checkbox" class="delete-group-checkbox" id="placeholder-delete-id"
                                                name="delete_group" value="true">
                                            <span class="delete-group-ack">I acknowledge the above and agree to delete the
                                                group.</span>
                                        </div>
                                        <div class="delete-group-footer d-flex flex-row align-items-center justify-content-end">
                                            <div class="cancel-section mx-4">
                                                <span class="text-center"><small>Cancel</small></span>
                                            </div>
                                            <div class="confirm-section">
                                                <button type="submit"
                                                    class="text-decoration-none text-center delete-button d-flex align-items-center justify-content-center">Confirm</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You are not in any groups yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<?php
$content = ob_get_clean(); // Store the captured content
include '_layout.php';     // Inject it into the layout
?>