<?php
    /******************************************************************
       View.php
       This file is where the user interacts only and checks all the POST actions
       PHP can be combined with HTML codes.
       ******************************************************************/

    include("Common.php");

    $branches = getAllBranches();

    $pageTitle = "Ember Order";
    include("partials/head.php");

    if (!empty($_POST)):
        // debug message to check the post actions
        //printArray($_POST);
    
        // based on the action, display the right information
        switch ($_POST['action']):
            case "selectBranch":
                // get the item being added 
                $branchId = $_POST['branchId'];
                displayDineOptions($branchId);
                break;
            case "selectDineOptions":
                $branchId = $_POST['branchId'];
                $isTakeaway = $_POST['isTakeaway'];
                if ($isTakeaway == false) {
                    displayTables($branchId, $isTakeaway);
                } else {
                    $tableId = "null";
                    displayMenu($branchId, $tableId, $isTakeaway, 0);
                }
                break;
            case "selectTable":
                $branchId = $_POST['branchId'];
                $isTakeaway = $_POST['isTakeaway'];
                $tableId = $_POST['tableId'];
                displayMenu($branchId, $tableId, $isTakeaway, 0);
                break;
            case "viewItemAddOns":
                $menuItemId = $_POST['menuItemId'];
                $tableId = $_POST['tableId'];
                $branchId = $_POST['branchId'];
                $isTakeaway = $_POST['isTakeaway'];
                $newCust = $_POST['newCust'];
                displayItemAddOns($menuItemId, $tableId, $branchId, $isTakeaway, $newCust);
                break;
            case "selectAddOns":
                if (!empty($_POST['itemTypes'])) {
                    $itemAddOns = implode(",", $_POST['itemTypes']);
                } else {
                    $itemAddOns = null;
                }
                $menuItemId = $_POST['menuItemId'];
                $tableId = $_POST['tableId'];
                $branchId = $_POST['branchId'];
                $isTakeaway = $_POST['isTakeaway'];
                $newCust = $_POST['newCust'];
                addToCart($menuItemId, $tableId, $itemAddOns);
                displayMenu($branchId, $tableId, $isTakeaway, $newCust);
                displayPopUp();
                break;
            case "addToCart":
                $menuItemId = $_POST['menuItemId'];
                $tableId = $_POST['tableId'];
                $branchId = $_POST['branchId'];
                $newCust = $_POST['newCust'];
                $isTakeaway = $_POST['isTakeaway'];
                addToCart($menuItemId, $tableId, null);
                displayMenu($branchId, $tableId, $isTakeaway, $newCust);
                displayPopup();
                break;
            case "viewCart":
                $tableId = $_POST['tableId'];
                $isTakeaway = $_POST['isTakeaway'];
                $branchId = $_POST['branchId'];
                $newCust = $_POST['newCust'];
                displayCart($branchId, 0, $tableId, $isTakeaway, $newCust);
                break;
            case "applyPromotionCode":
                $tableId = $_POST['tableId'];
                $promotionCode = $_POST['promotionCode'];
                $isTakeaway = $_POST['isTakeaway'];
                $newCust = $_POST['newCust'];
                $branchId = $_POST['branchId'];
                $promotion = checkPromoCode($promotionCode);
                if (!empty($promotion['promotionValue'])) {
                    $discount = $promotion['promotionValue'];
                    displayCart($branchId, $discount, $tableId, $isTakeaway, $newCust);
                    displayDiscountPopup();
                } else {
                    $discount = 0;
                    displayCart($branchId, $discount, $tableId, $isTakeaway, $newCust, true);
                }
                break;
            case "removeItemFromCart":
                $cartId = $_POST['cartId'];
                $tableId = $_POST['tableId'];
                $isTakeaway = $_POST['isTakeaway'];
                $newCust = $_POST['newCust'];
                $branchId = $_POST['branchId'];
                removeCartItem($cartId);
                displayCart($branchId, 0, $tableId, $isTakeaway, $newCust);
                break;
            case "removeAllItemsFromCart":
                $tableId = $_POST['tableId'];
                $isTakeaway = $_POST['isTakeaway'];
                $newCust = $_POST['newCust'];
                $branchId = $_POST['branchId'];
                removeAllCartItems();
                displayCart($branchId, 0, $tableId, $isTakeaway, $newCust);
                break;
            case "goBack":
                removeAllCartItems();
                displayBranches($branches);
                break;
            case "goBackFromTables":
                $branchId = $_POST['branchId'];
                removeAllCartItems();
                displayDineOptions($branchId);
                break;
            case "goBackFromMenu":
                $branchId = $_POST['branchId'];
                $isTakeaway = $_POST['isTakeaway'];
                $currentTableId = isset($_POST['tableId']) ? $_POST['tableId'] : null;
                removeAllCartItems();
                displayTables($branchId, $isTakeaway, $currentTableId);
                break;
            case "goBackFromCart":
                $branchId = $_POST['branchId'];
                $isTakeaway = $_POST['isTakeaway'];
                $tableId = $_POST['tableId'];
                $newCust = $_POST['newCust'];
                displayMenu($branchId, $tableId, $isTakeaway, $newCust);
                break;
            case "goBackFromPay":
                $isTakeaway = $_POST['isTakeaway'];
                $branchId = $_POST['branchId'];
                $newCust = $_POST['newCust'];
                $tableId = $_POST['tableId'];
                displayCart($branchId, 0, $tableId, $isTakeaway, $newCust);
                break;
            case "payCart":
                $branchId = $_POST['branchId'];
                $isTakeaway = $_POST['isTakeaway'];
                $tableId = $_POST['tableId'];
                $newCust = $_POST['newCust'];
                $sum = $_POST['sum'];
                $billItemIds = $_POST['billItemIds'];
                displayPay($newCust, $branchId, $sum, $billItemIds, $tableId, $isTakeaway);
                break;
            case "checkMember":
                $branchId = $_POST['branchId'];
                $isTakeaway = $_POST['isTakeaway'];
                $tableId = $_POST['tableId'];
                $newCust = $_POST['newCust'];
                $sum = $_POST['sum'];
                $billItemIds = $_POST['billItemIds'];
                $member = getMember($_POST['memberNumber']);
                if (is_array($member) && !empty($member['memberNumber'])) {
                    displayPay($newCust, $branchId, $sum, $billItemIds, $tableId, $isTakeaway, $_POST['memberNumber']);
                } else {
                    displayPay($newCust, $branchId, $sum, $billItemIds, $tableId, $isTakeaway, null, false, true);
                }
                break;
            case "redeemPoints":
                $branchId = $_POST['branchId'];
                $isTakeaway = $_POST['isTakeaway'];
                $tableId = $_POST['tableId'];
                $newCust = $_POST['newCust'];
                $memberNumber = $_POST['memberNumber'];
                $sum = $_POST['sum'];
                $sum -= 2;
                $billItemIds = $_POST['billItemIds'];
                displayPay($newCust, $branchId, $sum, $billItemIds, $tableId, $isTakeaway, $memberNumber, true);
                break;
            case "cancelRedemption":
                $branchId = $_POST['branchId'];
                $isTakeaway = $_POST['isTakeaway'];
                $newCust = $_POST['newCust'];
                $tableId = $_POST['tableId'];
                $memberNumber = $_POST['memberNumber'];
                $sum = $_POST['sum'];
                $sum += 2;
                $billItemIds = $_POST['billItemIds'];
                displayPay($newCust, $branchId, $sum, $billItemIds, $tableId, $isTakeaway, $memberNumber, false);
                break;
            case "submitPayment":
                if (!empty($_POST['memberNumber'])) {
                    $memberNumber = $_POST['memberNumber'];
                    $pointsGotten = $_POST['pointsGotten'];
                    $pointsDeducted = $_POST['pointsDeducted'];
                    updateMember($memberNumber, $pointsGotten, $pointsDeducted);
                }
                $tableId = $_POST['tableId'];
                $isTakeaway = $_POST['isTakeaway'];
                $newCust = $_POST['newCust'];
                $branchId = $_POST['branchId'];
                $sum = $_POST['sum'];
                $billItemIds = $_POST['billItemIds'];
                $paymentMethod = $_POST['payment'];
                if ($newCust == 0) {
                    createBill($sum, $billItemIds, $paymentMethod, $tableId, $branchId);
                    $newCust = 1;
                } else if ($newCust == 1) {
                    createBillSameOrder($sum, $billItemIds, $paymentMethod, $tableId, $branchId);
                }
                $billId = getLatestBill()['billId'];
                createPayment($paymentMethod, $sum, $billId);
                removeAllCartItems();
                if ($isTakeaway == false) {
                    updateTable($tableId);
                }
                displayPaymentReceipt($branchId, $tableId, $isTakeaway, $newCust);
                break;
            default:
                removeAllCartItems();
                displayBranches($branches);

        endswitch;
    else:
        // if there are no action posted, it will always display menu
        removeAllCartItems();
        displayBranches($branches);
    endif;

    include("partials/footer.php");
    ?>