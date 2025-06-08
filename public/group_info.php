<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

$title = "Group Info";
ob_start();
?>

<div class="container">
    <div class="group-header">
        <div class="group-title">
            <h2>[24/25 T3]-ICT2216-P1-G4</h2>
            <p>ICT2216, Secure Software Development [24/25 T3]</p>
        </div>
        <button class="join-button">Request to Join</button>
    </div>

    <div class="members-section">
        <h3>Members (5/7)</h3>
        <ul class="members-list">
            <li>
                <span>1. XXXXX, 2301111</span>
            </li>
            <li>2. XXXXX, 2302222</li>
            <li>3. XXXXX, 2303333</li>
            <li>4. XXXXX, 2304444</li>
            <li>5. XXXXX, 2305555</li>
        </ul>
    </div>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
?>