<?php
    /******************************************************************
       admin.php
       Staff console: payments log, members, promotions and branches.
       PHP logic runs first so the header() redirect works correctly.
       ******************************************************************/

    include("Common.php");
    $userRole = "";
    if ($_GET) {
        $userRole = $_GET['role'];
    } else if ($_POST) {
        $userRole = $_POST['role'];
    } else {
        header("location: index.php");
        exit;
    }

    $pageTitle = "Ember Staff Console";
    $barSlot = "<span class='brand-tag--chip'>Staff Console</span>";
    include("partials/head.php");

    echo "<div class='container my-10 px-6 mx-auto'>";

    // Work out which tab is currently being viewed so we can highlight it.
    $currentAction = isset($_POST['action']) ? $_POST['action'] : '';
    $activeTab = 'payments';
    if (stripos($currentAction, 'member') !== false) {
        $activeTab = 'members';
    } else if (stripos($currentAction, 'promotion') !== false) {
        $activeTab = 'promotions';
    } else if (stripos($currentAction, 'branch') !== false || $currentAction == 'seeTables' || $currentAction == 'freeTable') {
        $activeTab = 'branches';
    }
    $activeClass = function ($tab) use ($activeTab) {
        return $activeTab === $tab ? ' is-active' : '';
    };

    // Admin top bar
    echo "<div class='admin-bar'>";
    echo sprintf("<span class='admin-title'>Dashboard <span class='role-chip'>%s</span></span>", $userRole);

    // Payments
    echo "<form action=admin.php method='post'>";
    echo sprintf("<input type='hidden' name='action' value=''><input class='btn btn-sm tab-btn%s' type='submit' value='Payments' />", $activeClass('payments'));
    echo sprintf("<input type='hidden' name='role' value='%s'>", $userRole);
    echo "</form>";
    // Members
    echo "<form action=admin.php method='post'>";
    echo sprintf("<input type='hidden' name='action' value='viewMembers'><input class='btn btn-sm tab-btn%s' type='submit' value='Members' />", $activeClass('members'));
    echo sprintf("<input type='hidden' name='role' value='%s'>", $userRole);
    echo "</form>";
    // Promotions
    echo "<form action=admin.php method='post'>";
    echo sprintf("<input type='hidden' name='action' value='viewPromotions'><input class='btn btn-sm tab-btn%s' type='submit' value='Promotions' />", $activeClass('promotions'));
    echo sprintf("<input type='hidden' name='role' value='%s'>", $userRole);
    echo sprintf("<input type='hidden' name='promotionId' value='%s'>", 0);
    echo "</form>";
    // Branches
    echo "<form action=admin.php method='post'>";
    echo sprintf("<input type='hidden' name='action' value='viewBranches'><input class='btn btn-sm tab-btn%s' type='submit' value='Branches' />", $activeClass('branches'));
    echo sprintf("<input type='hidden' name='role' value='%s'>", $userRole);
    echo sprintf("<input type='hidden' name='branchId' value='%s'>", 0);
    echo "</form>";
    // Logout (asks for confirmation first via an in-page modal)
    echo "<button type='button' class='btn btn-sm btn-error' data-logout-open>Logout</button>";
    echo "<form id='logoutForm' class='mb-0' action=index.php method='post'><input type='hidden' name='action'></form>";

    echo "</div>";

    // Logout confirmation modal
    echo "<div id='logoutModal' class='modal-overlay' hidden>";
    echo "<div class='modal-box'>";
    echo "<h3>Log out?</h3>";
    echo "<p class='section-sub' style='margin:6px 0 18px'>You will be returned to the login screen.</p>";
    echo "<div class='modal-actions'>";
    echo "<button type='button' class='btn btn-sm btn-ghost' data-logout-cancel>Cancel</button>";
    echo "<button type='button' class='btn btn-sm btn-error' data-logout-confirm>Log out</button>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

    if (!empty($_POST)) {
        switch ($_POST['action']) {
            case 'deletePayment':
                global $userRole;
                $paymentId = $_POST['paymentId'];
                $billId = $_POST['billId'];
                removePayment($paymentId);
                removeBill($billId);
                adminPayment($userRole);
                break;
            case 'viewPromotions':
                global $userRole;
                $promotionId = $_POST['promotionId'];
                adminPromotions($promotionId, false, $userRole);
                break;
            case 'updatePromotion':
                global $userRole;
                $promotionId = $_POST['promotionId'];
                adminPromotions($promotionId, false, $userRole);
                break;
            case 'cancelUpdatePromotion':
                global $userRole;
                adminPromotions(0, false, $userRole);
                break;
            case 'confirmUpdatePromotion':
                global $userRole;
                updatePromotions($_POST['promotionName'], $_POST['promotionCode'], $_POST['promotionValue'], $_POST['promotionId']);
                adminPromotions(0, false, $userRole);
                break;
            case 'deletePromotion':
                $promotionId = $_POST['promotionId'];
                global $userRole;
                removePromotion($promotionId);
                adminPromotions(0, false, $userRole);
                break;
            case 'newPromotion':
                global $userRole;
                adminPromotions(0, true, $userRole);
                break;
            case 'confirmNewPromotion':
                createPromotion($_POST['promotionName'], $_POST['promotionCode'], $_POST['promotionValue']);
                global $userRole;
                adminPromotions(0, false, $userRole);
                break;
            case 'cancelNewPromotion':
                global $userRole;
                adminPromotions(0, false, $userRole);
                break;
            case 'viewMembers':
                global $userRole;
                adminMembers(0, false, $userRole);
                break;
            case 'updateMember':
                global $userRole;
                $memberId = $_POST['memberId'];
                adminMembers($memberId, false, $userRole);
                break;
            case 'cancelUpdateMember':
                global $userRole;
                adminMembers(0, false, $userRole);
                break;
            case 'confirmUpdateMember':
                global $userRole;
                updateMemberDetails($_POST['memberFirstName'], $_POST['memberLastName'], $_POST['memberNumber'], $_POST['memberId']);
                adminMembers(0, false, $userRole);
                break;
            case 'deleteMember':
                $memberId = $_POST['memberId'];
                global $userRole;
                removeMember($memberId);
                adminPromotions(0, false, $userRole);
                break;
            case 'newMember':
                global $userRole;
                adminMembers(0, true, $userRole);
                break;
            case 'confirmNewMember':
                createMember($_POST['memberFirstName'], $_POST['memberLastName'], $_POST['memberNumber']);
                global $userRole;
                adminMembers(0, false, $userRole);
                break;
            case 'cancelNewMember':
                global $userRole;
                adminMembers(0, false, $userRole);
                break;
            case 'viewBranches':
                $branchId = $_POST['branchId'];
                global $userRole;
                adminBranches($branchId, $userRole);
                break;
            case 'updateBranch':
                $branchId = $_POST['branchId'];
                global $userRole;
                adminBranches($branchId, $userRole);
                break;
            case 'cancelUpdateBranch':
                global $userRole;
                adminBranches(0, $userRole);
                break;
            case 'confirmUpdateBranch':
                global $userRole;
                if (empty($_FILES['branchImage']['name'])) {
                    $img = $_POST['originalBranchImage'];
                } else {
                    $img = $_FILES['branchImage']['name'];
                    move_uploaded_file($_FILES['branchImage']['tmp_name'], "images/$img");
                }
                updateBranch($_POST['branchName'], $_POST['branchAddress'], $_POST['numberOfTables'], $img, $_POST['branchId']);
                adminBranches(0, $userRole);
                break;
            case 'seeTables':
                global $userRole;
                $branchId = $_POST['branchId'];
                adminTables($branchId, $userRole);
                break;
            case 'freeTable':
                global $userRole;
                $branchId = $_POST['branchId'];
                $tableId = $_POST['tableId'];
                freeTable($tableId);
                adminTables($branchId, $userRole);
                break;
            default:
                global $userRole;
                adminPayment($userRole);
                break;
        }
    } else {
        adminPayment($userRole);
    }

    function adminTables($branchId, $role)
    {
        $tables = getAllTables($branchId);
        $branch = getBranchFromTable($branchId);
        echo "<div class='pb-5'><span class='section-eyebrow'>Operations</span><h1 class='section-title'>Tables</h1>";
        echo sprintf("<p class='section-sub'>%s</p></div>", $branch['branchName']);
        echo "<div class='pt-2'>";
        echo "<div class='overflow-x-auto'>";
        echo "<table class='table table-zebra w-full'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Table Id</th>";
        echo "<th>Table Number</th>";
        echo "<th>Table Status</th>";
        echo "<th>Actions</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach ($tables as $table):
            echo "<tr>";
            echo sprintf("<td>%s</td>", $table['tableId']);
            echo sprintf("<td>%s</td>", $table['tableNo']);
            if ($table['isReserved'] == 0) {
                echo sprintf("<td><span class='status-pill is-available'>Available</span></td>");
                echo "<td><form class='mb-0' action=admin.php method='post'>";
                echo "<input type='hidden' name='action'>";
                echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                echo sprintf("<input class='btn btn-sm btn-disabled' type='submit' value='Set Available' />");
                echo "</form></td>";
                echo "</tr>";
            } else {
                echo sprintf("<td><span class='status-pill is-occupied'>Occupied</span></td>");
                echo "<td><form class='mb-0' action=admin.php method='post'>";
                echo "<input type='hidden' name='action' value='freeTable'>";
                echo sprintf("<input class='btn btn-sm btn-success' type='submit' value='Set Available' />");
                echo sprintf("<input type='hidden' name='tableId' value='%s'>", $table['tableId']);
                echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
                echo "</form></td>";
                echo "</tr>";
            }

        endforeach;

        echo "</tbody>";
        echo "</table>";
        echo "</div>";

        if (empty($tables)) {
            echo "<p><i>No tables found.</i></p>";
        }
    }

    function adminPromotions($promotionId, $isNew, $role)
    {

        $promotions = getAllPromotions();

        echo "<div class='pb-5'><span class='section-eyebrow'>Marketing</span><h1 class='section-title'>Promotions</h1></div>";
        echo "<div class='pt-2'>";

        echo "<div class='overflow-x-auto'>";
        echo "<table class='table table-zebra w-full'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Promotion Id</th>";
        echo "<th>Promotion Name</th>";
        echo "<th>Promotion Code</th>";
        echo "<th>Promotion Value</th>";
        echo "<th>Actions</th>";
        echo "<th></th>";
        echo "<th>";
        if ($role == 'admin') {
            echo "<form class='mb-0' action=admin.php method='post'>";
            echo "<input type='hidden' name='action' value='newPromotion'>";
            echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
            echo sprintf("<input class='btn btn-sm btn-success' type='submit' value='New Promotion' />");
            echo "</form>";
        }
        echo "</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach ($promotions as $promotion):
            if ($promotionId != $promotion['promotionId']) {
                echo "<tr>";
                echo sprintf("<td>%s</td>", $promotion['promotionId']);
                echo sprintf("<td>%s</td>", $promotion['promotionName']);
                echo sprintf("<td>%s</td>", $promotion['promotionCode']);
                echo sprintf("<td>%s</td>", $promotion['promotionValue']);

                if ($role == 'admin') {
                    echo "<td><form class='mb-0' action=admin.php method='post'>";
                    echo "<input type='hidden' name='action' value='updatePromotion'>";
                    echo sprintf("<input class='btn btn-sm btn-primary' type='submit' value='Update' />");
                    echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                    echo sprintf("<input type='hidden' name='promotionId' value='%s'>", $promotion['promotionId']);
                    echo "</form></td>";
                    echo "<td><form class='mb-0' action=admin.php method='post'>";
                    echo "<input type='hidden' name='action' value='deletePromotion'>";
                    echo sprintf("<input class='btn btn-sm btn-error' type='submit' value='Delete' />");
                    echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                    echo sprintf("<input type='hidden' name='promotionId' value='%s'>", $promotion['promotionId']);
                    echo "</form></td>";
                    echo "<td>";
                    echo sprintf("<input class='btn btn-sm btn-disabled' value='' />");
                    echo "</td>";
                } else {
                    echo "<td><form class='mb-0' action=admin.php method='post'>";
                    echo "<input type='hidden' name='action'>";
                    echo sprintf("<input class='btn btn-sm btn-disabled' value='Update' />");
                    echo "</form></td>";
                    echo "<td><form class='mb-0' action=admin.php method='post'>";
                    echo "<input type='hidden' name='action'>";
                    echo sprintf("<input class='btn btn-sm btn-disabled' value='Delete' />");
                    echo "</form></td>";
                    echo "<td>";
                    echo "</td>";
                }




                echo "</tr>";
            } else {
                echo "<tr>";
                echo sprintf("<td>%s</td>", $promotion['promotionId']);
                echo "<form class='mt-8' method='post''>";
                echo sprintf("<td><input type='text' placeholder='%s' class='input input-bordered w-full max-w-xs' name='promotionName' value='%s'/></td>", $promotion['promotionName'], $promotion['promotionName']);
                echo sprintf("<td><input type='text' placeholder='%s' class='input input-bordered w-full max-w-xs' name='promotionCode' value='%s'/></td>", $promotion['promotionCode'], $promotion['promotionCode']);
                echo sprintf("<td><input type='text' placeholder='%s' class='input input-bordered w-full max-w-xs' name='promotionValue' value='%s'/></td>", $promotion['promotionValue'], $promotion['promotionValue']);

                echo "<td><form class='mb-0' action=admin.php method='post'>";
                echo "<input type='hidden' name='action' value='confirmUpdatePromotion'>";
                echo sprintf("<input class='btn btn-sm btn-success' type='submit' value='Confirm' />");
                echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                echo sprintf("<input type='hidden' name='promotionId' value='%s'>", $promotionId);
                echo "</form></td>";
                echo "</form>";

                echo "<td><form class='mb-0' action=admin.php method='post'>";
                echo "<input type='hidden' name='action' value='cancelUpdatePromotion'>";
                echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                echo sprintf("<input class='btn btn-sm btn-error' type='submit' value='Cancel' />");
                echo "</form></td>";

                echo "<td>";
                echo sprintf("<input class='btn btn-sm btn-disabled' value='' />");
                echo "</td>";
                echo "</tr>";
            }

        endforeach;

        if ($isNew == true) {
            echo "<tr>";
            echo sprintf("<td></td>");
            echo "<form class='mt-8' method='post''>";
            echo sprintf("<td><input type='text' placeholder='Promotion Name' class='input input-bordered w-full max-w-xs' name='promotionName' data-vgroup='newPromotion' data-validate-field/></td>");
            echo sprintf("<td><input type='text' placeholder='Promotion Code' class='input input-bordered w-full max-w-xs' name='promotionCode' data-vgroup='newPromotion' data-validate-field/></td>");
            echo sprintf("<td><input type='number' step='0.01' placeholder='Promotion Value' class='input input-bordered w-full max-w-xs' name='promotionValue' data-vgroup='newPromotion' data-validate-field/></td>");

            echo "<td><form class='mb-0' action=admin.php method='post'>";
            echo "<input type='hidden' name='action' value='confirmNewPromotion'>";
            echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
            echo sprintf("<input class='btn btn-sm btn-success' type='submit' value='Confirm' data-vgroup='newPromotion' data-validate-btn/>");
            echo "</form></td>";
            echo "</form>";

            echo "<td><form class='mb-0' action=admin.php method='post'>";
            echo "<input type='hidden' name='action' value='cancelNewPromotion'>";
            echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
            echo sprintf("<input class='btn btn-sm btn-error' type='submit' value='Cancel' />");
            echo "</form></td>";

            echo "<td></td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";

        if (empty($promotions)) {
            echo "<p><i>No promotions found.</i></p>";
        }
    }

    function adminMembers($memberId, $isNew, $role)
    {

        $members = getAllMembers();

        echo "<div class='pb-5'><span class='section-eyebrow'>Loyalty</span><h1 class='section-title'>Members</h1></div>";
        echo "<div class='pt-2'>";

        echo "<div class='overflow-x-auto'>";
        echo "<table class='table table-zebra w-full'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Member Id</th>";
        echo "<th>First Name</th>";
        echo "<th>Last Name</th>";
        echo "<th>Phone Number</th>";
        echo "<th>Total Points</th>";
        echo "<th>Actions</th>";
        echo "<th></th>";
        echo "<th>";
        if ($role == 'admin') {
            echo "<form class='mb-0' action=admin.php method='post'>";
            echo "<input type='hidden' name='action' value='newMember'>";
            echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
            echo sprintf("<input class='btn btn-sm btn-success' type='submit' value='New Member' />");
            echo "</form>";
        }
        echo "</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach ($members as $member):
            if ($memberId != $member['memberId']) {
                echo "<tr>";
                echo sprintf("<td>%s</td>", $member['memberId']);
                echo sprintf("<td>%s</td>", $member['memberFirstName']);
                echo sprintf("<td>%s</td>", $member['memberLastName']);
                echo sprintf("<td>%s</td>", $member['memberNumber']);
                echo sprintf("<td>%s</td>", $member['totalPoints']);

                if ($role == 'admin') {
                    echo "<td><form class='mb-0' action=admin.php method='post'>";
                    echo "<input type='hidden' name='action' value='updateMember'>";
                    echo sprintf("<input class='btn btn-sm btn-primary' type='submit' value='Update' />");
                    echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                    echo sprintf("<input type='hidden' name='memberId' value='%s'>", $member['memberId']);
                    echo "</form></td>";
                    echo "<td><form class='mb-0' action=admin.php method='post'>";
                    echo "<input type='hidden' name='action' value='deleteMember'>";
                    echo sprintf("<input class='btn btn-sm btn-error' type='submit' value='Delete' />");
                    echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                    echo sprintf("<input type='hidden' name='memberId' value='%s'>", $member['memberId']);
                    echo "</form></td>";
                    echo "<td>";
                    echo sprintf("<input class='btn btn-sm btn-disabled' value='' />");
                    echo "</td>";
                } else {
                    echo "<td><form class='mb-0' action=admin.php method='post'>";
                    echo "<input type='hidden' name='action'>";
                    echo sprintf("<input class='btn btn-sm btn-disabled' value='Update' />");
                    echo "</form></td>";
                    echo "<td><form class='mb-0' action=admin.php method='post'>";
                    echo "<input type='hidden' name='action'>";
                    echo sprintf("<input class='btn btn-sm btn-disabled' value='Delete' />");
                    echo "</form></td>";
                    echo "<td>";
                    echo "</td>";
                }

                echo "</tr>";
            } else {
                echo "<tr>";
                echo sprintf("<td>%s</td>", $member['memberId']);
                echo "<form class='mt-8' method='post''>";
                echo sprintf("<td><input type='text' placeholder='%s' class='input input-bordered w-full max-w-xs' name='memberFirstName' value='%s'/></td>", $member['memberFirstName'], $member['memberFirstName']);
                echo sprintf("<td><input type='text' placeholder='%s' class='input input-bordered w-full max-w-xs' name='memberLastName' value='%s'/></td>", $member['memberLastName'], $member['memberLastName']);
                echo sprintf("<td><input type='text' placeholder='%s' class='input input-bordered w-full max-w-xs' name='memberNumber' value='%s'/></td>", $member['memberNumber'], $member['memberNumber']);
                echo sprintf("<td>%s</td>", $member['totalPoints']);

                echo "<td><form class='mb-0' action=admin.php method='post'>";
                echo "<input type='hidden' name='action' value='confirmUpdateMember'>";
                echo sprintf("<input class='btn btn-sm btn-success' type='submit' value='Confirm' />");
                echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                echo sprintf("<input type='hidden' name='memberId' value='%s'>", $memberId);
                echo "</form></td>";
                echo "</form>";

                echo "<td><form class='mb-0' action=admin.php method='post'>";
                echo "<input type='hidden' name='action' value='cancelUpdateMember'>";
                echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                echo sprintf("<input class='btn btn-sm btn-error' type='submit' value='Cancel' />");
                echo "</form></td>";

                echo "<td>";
                echo sprintf("<input class='btn btn-sm btn-disabled' value='' />");
                echo "</td>";
                echo "</tr>";
            }

        endforeach;

        if ($isNew == true) {
            echo "<tr>";
            echo sprintf("<td></td>");
            echo "<form class='mt-8' method='post''>";
            echo sprintf("<td><input type='text' placeholder='First Name' class='input input-bordered w-full max-w-xs' name='memberFirstName' data-vgroup='newMember' data-validate-field/></td>");
            echo sprintf("<td><input type='text' placeholder='Last Name' class='input input-bordered w-full max-w-xs' name='memberLastName' data-vgroup='newMember' data-validate-field/></td>");
            echo sprintf("<td><input type='text' placeholder='Phone Number' class='input input-bordered w-full max-w-xs' name='memberNumber' data-vgroup='newMember' data-validate-field/></td>");
            echo "<td></td>";

            echo "<td><form class='mb-0' action=admin.php method='post'>";
            echo "<input type='hidden' name='action' value='confirmNewMember'>";
            echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
            echo sprintf("<input class='btn btn-sm btn-success' type='submit' value='Confirm' data-vgroup='newMember' data-validate-btn/>");
            echo "</form></td>";
            echo "</form>";

            echo "<td><form class='mb-0' action=admin.php method='post'>";
            echo "<input type='hidden' name='action' value='cancelNewMember'>";
            echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
            echo sprintf("<input class='btn btn-sm btn-error' type='submit' value='Cancel' />");
            echo "</form></td>";

            echo "<td></td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";

        if (empty($members)) {
            echo "<p><i>No members found.</i></p>";
        }
    }

    function adminPayment($role)
    {
        $payments = getAllPayments();

        echo "<div class='pb-5'><span class='section-eyebrow'>Transactions</span><h1 class='section-title'>Payments</h1></div>";
        echo "<div class='pt-2'>";

        echo "<div class='overflow-x-auto'>";
        echo "<table class='table table-zebra w-full'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Payment Id</th>";
        echo "<th>Bill Id</th>";
        echo "<th>Total Amount</th>";
        echo "<th>DateTime</th>";
        echo "<th>Payment Method</th>";
        echo "<th>Items Ordered</th>";
        echo "<th>Order Type</th>";
        echo "<th>Outlet</th>";
        echo "<th>Actions</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach ($payments as $payment):
            echo "<tr>";
            echo sprintf("<td>%s</td>", $payment['paymentId']);
            echo sprintf("<td>%s</td>", $payment['billId']);
            echo sprintf("<td>$%s</td>", number_format((float) $payment['totalAmount'], 2, '.', ''));
            echo sprintf("<td>%s</td>", $payment['paymentDateTime']);
            echo sprintf("<td>%s</td>", $payment['paymentMethod']);

            $paymentBill = getBillFromPayment($payment['billId']);
            $billMenuItemsArray = array();
            $branchName = null;
            if (is_array($paymentBill)) {
                $billMenuItemsArray = explode(",", $paymentBill['menuIds']);
                $tableRow = getTable($paymentBill['tableId']);
                if (is_array($tableRow)) {
                    $branchRow = getBranchFromTable($tableRow['branchId']);
                    $branchName = is_array($branchRow) ? $branchRow['branchName'] : null;
                }
            }
            echo "<td>";
            foreach ($billMenuItemsArray as $billMenuItem):
                $menuItem = getMenuItemFromCart($billMenuItem);
                if (is_array($menuItem)) {
                    echo $menuItem['menuItemName'];
                    echo "<br>";
                }
            endforeach;
            echo "</td>";
            if (!empty($branchName)) {
                echo "<td>Dine-In</td>";
                echo sprintf("<td>%s</td>", $branchName);
            } else {
                $takeawayBranch = is_array($paymentBill) ? getBranchFromTable($paymentBill['branchId']) : null;
                $takeawayName = is_array($takeawayBranch) ? $takeawayBranch['branchName'] : "";
                echo "<td>Takeaway</td>";
                echo sprintf("<td>%s</td>", $takeawayName);
            }

            if ($role == 'admin') {
                echo "<td><form class='mb-0' action=admin.php method='post'>";
                echo "<input type='hidden' name='action' value='deletePayment'>";
                echo sprintf("<input class='btn btn-sm btn-error' type='submit' value='Delete' />");
                echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                echo sprintf("<input type='hidden' name='paymentId' value='%s'>", $payment['paymentId']);
                echo sprintf("<input type='hidden' name='billId' value='%s'>", $payment['billId']);
                echo "</form></td>";
            } else {
                echo "<td><form class='mb-0' action=admin.php method='post'>";
                echo "<input type='hidden' name='action'>";
                echo sprintf("<input class='btn btn-sm btn-disabled' value='Delete' />");
                echo "</form></td>";
            }

            echo "</tr>";
        endforeach;
        echo "</tbody>";
        echo "</table>";
        echo "</div>";

        if (empty($payment)) {
            echo "<p><i>No payment records found.</i></p>";
        }
    }

    function adminBranches($branchId, $role)
    {

        $branches = getAllBranches();

        echo "<div class='pb-5'><span class='section-eyebrow'>Operations</span><h1 class='section-title'>Branches</h1></div>";
        echo "<div class='pt-2'>";

        echo "<div class='overflow-x-auto'>";
        echo "<table class='table table-zebra w-full'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Branch Id</th>";
        echo "<th>Branch Name</th>";
        echo "<th>Branch Address</th>";
        echo "<th>Number of Tables</th>";
        echo "<th>Branch Image</th>";
        echo "<th>Actions</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach ($branches as $branch):
            if ($branchId != $branch['branchId']) {
                echo "<tr>";
                echo sprintf("<td>%s</td>", $branch['branchId']);
                echo sprintf("<td>%s</td>", $branch['branchName']);
                echo sprintf("<td>%s</td>", $branch['branchAddress']);
                echo "<td><div class='tables-cell'>";
                echo sprintf("<span class='tables-count'>%s</span>", $branch['numberOfTables']);
                echo "<form class='mb-0' action=admin.php method='post'>";
                echo "<input type='hidden' name='action' value='seeTables'>";
                echo sprintf("<input class='btn btn-sm btn-success' type='submit' value='View Tables' />");
                echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branch['branchId']);
                echo "</form></div></td>";
                echo sprintf("<td><img class='branch-thumb' src='images/" . $branch['branchImage'] . "' alt='" . htmlspecialchars($branch['branchName']) . "'/></td>");

                if ($role == 'admin') {
                    echo "<td><form class='mb-0' action=admin.php method='post'>";
                    echo "<input type='hidden' name='action' value='updateBranch'>";
                    echo sprintf("<input class='btn btn-sm btn-primary' type='submit' value='Update' />");
                    echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                    echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branch['branchId']);
                    echo "</form></td>";
                } else {
                    echo "<td><form class='mb-0' action=admin.php method='post'>";
                    echo "<input type='hidden' name='action'>";
                    echo sprintf("<input class='btn btn-sm btn-disabled' value='Update' />");
                    echo "</form></td>";
                }

                echo "</tr>";
            } else {
                echo "<tr>";
                echo sprintf("<td>%s</td>", $branch['branchId']);
                echo "<form class='mt-8' method='post' enctype='multipart/form-data'>";
                echo sprintf("<td><input type='text' placeholder='%s' class='input input-bordered w-full max-w-xs' name='branchName' value='%s'/></td>", $branch['branchName'], $branch['branchName']);
                echo sprintf("<td><input type='text' placeholder='%s' class='input input-bordered w-full max-w-xs' name='branchAddress' value='%s'/></td>", $branch['branchAddress'], $branch['branchAddress']);
                echo sprintf("<td><input type='text' placeholder='%s' class='input input-bordered w-full max-w-xs' name='numberOfTables' value='%s'/></td>", $branch['numberOfTables'], $branch['numberOfTables']);

                echo sprintf("<td><img class='branch-thumb mb-2' src='images/" . $branch['branchImage'] . "' alt='" . htmlspecialchars($branch['branchName']) . "'/><input class='w-32' type='file' name='branchImage'/></td>");

                echo "<td><form class='mb-0' action=admin.php method='post'>";
                echo "<input type='hidden' name='action' value='confirmUpdateBranch'>";
                echo sprintf("<input class='btn btn-sm btn-success' type='submit' value='Confirm' />");
                echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
                echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                echo sprintf("<input type='hidden' name='originalBranchImage' value='%s'>", $branch['branchImage']);
                echo "</form>";
                echo "</form>";

                echo "<form class='mb-0' style='display:inline-block;margin-left:6px' action=admin.php method='post'>";
                echo "<input type='hidden' name='action' value='cancelUpdateBranch'>";
                echo sprintf("<input type='hidden' name='role' value='%s'>", $role);
                echo sprintf("<input class='btn btn-sm btn-error' type='submit' value='Cancel' />");
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }

        endforeach;
        echo "</tbody>";
        echo "</table>";
        echo "</div>";

        if (empty($branches)) {
            echo "<p><i>No branches found.</i></p>";
        }
    }

    echo "</div>";
    include("partials/footer.php");
    ?>