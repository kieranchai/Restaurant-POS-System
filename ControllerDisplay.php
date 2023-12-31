<?php
/******************************************************************
   ControllerDisplay.php
   This file communicates with View.php and responsible for all the display output
   ******************************************************************/
function displayPaymentReceipt($branchId, $tableId, $isTakeaway, $newCust)
{
    $payment = getLatestPayment();
    $cartItems = getLatestBill()['menuIds'];
    $cartItem = explode(",", $cartItems);
    $table = getTable($tableId);

    echo "<div class='container my-16 px-6 mx-auto'>";

    echo "<div class='pb-16 navbar text-neutral-content'>";
    // Go Back Button
    echo "<div class='navbar-start'><form class='mb-0' action=view.php method='post'>";
    echo "<input type='hidden' name='action' value='goBackFromCart'><input class='btn btn-primary' type='submit' value='Back to Menu' />";
    echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
    echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
    echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
    echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
    echo "</form></div></div>";

    echo "<div class='w-1/2 float-left'><b>Payment Receipt</b><div class='divider'></div>";
    echo sprintf("Transaction ID: %s", $payment['paymentId']);
    echo "<br>";
    echo sprintf("Payment Method: %s", $payment['paymentMethod']);
    echo "<br>";
    echo sprintf("Amount Paid: $%s", $payment['totalAmount']);
    echo "<br>";
    echo sprintf("Transaction Date: %s", $payment['paymentDateTime']);
    echo "<br>";
    if ($isTakeaway == false) {
        echo sprintf("Table Number : %s", $table['tableNo']);
    }
    echo "</div>";

    echo "<div class='w-1/2 float-right'><b>Order Receipt</b><div class='divider'></div>";
    foreach ($cartItem as $item):
        $menuItem = getMenuItemFromCart($item);
        echo $menuItem['menuItemName'];
        echo "<br>";
    endforeach;
    echo "</div>";

    echo "</div>";
}

function displayItemAddOns($menuItemId, $tableId, $branchId, $isTakeaway, $newCust)
{
    echo "<div class='container my-16 px-6 mx-auto'>";

    echo "<div class='pb-16 navbar text-neutral-content'>";
    // Go Back Button
    echo "<div class='navbar-start'><form class='mb-0' action=view.php method='post'>";
    echo "<input type='hidden' name='action' value='goBackFromCart'><input class='btn btn-primary' type='submit' value='Back to Menu' />";
    echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
    echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
    echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
    echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
    echo "</form></div></div>";

    $menuItem = getMenuItemFromCart($menuItemId);

    echo sprintf("<b>%s Add-Ons</b><div class='divider'> </div>", $menuItem['menuItemName']);

    $itemTypeAddOns = getItemTypeAddOns($menuItem['itemTypeId']);

    echo "<form action='view.php' method='post'>";
    if ($menuItem['itemTypeId'] == 1) {
        foreach ($itemTypeAddOns as $itemTypeAddOn):
            echo "<div class='form-control w-1/4'><label class='label cursor-pointer'>";
            echo sprintf("<label class='label-text' for='%s'>%s<br>+<b>$%s</b></label>", $itemTypeAddOn['itemTypeAddOnsId'], $itemTypeAddOn['itemTypeAddOnsName'], $itemTypeAddOn['itemTypeAddOnsPriceModifier']);
            echo sprintf("<input class='checkbox checkbox-primary' type='checkbox' name='itemTypes[]' value='%s' id='%s'>", $itemTypeAddOn['itemTypeAddOnsId'], $itemTypeAddOn['itemTypeAddOnsId']);
            echo "</label></div><br><br>";
        endforeach;
    } else if ($menuItem['itemTypeId'] == 2) {
        foreach ($itemTypeAddOns as $itemTypeAddOn):
            echo "<div class='form-control w-1/4'><label class='label cursor-pointer'>";
            echo sprintf("<label class='label-text' for='%s' required>%s<br>+<b>$%s</b></label>", $itemTypeAddOn['itemTypeAddOnsId'], $itemTypeAddOn['itemTypeAddOnsName'], $itemTypeAddOn['itemTypeAddOnsPriceModifier']);
            echo sprintf("<input class='radio radio-primary' type='radio' name='itemTypes[]' value='%s' id='%s' checked>", $itemTypeAddOn['itemTypeAddOnsId'], $itemTypeAddOn['itemTypeAddOnsId']);
            echo "</label></div><br><br>";
        endforeach;
    }
    echo "<input type='hidden' name='action' value='selectAddOns'><input class='btn btn-primary' type='submit' value='Add To Cart' />";
    echo sprintf("<input type='hidden' name='menuItemId' value='%s'>", $menuItemId);
    echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
    echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
    echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
    echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
    echo "</form>";

    echo "</div>";
}

function displayCart($branchId, $promotionValue, $tableId, $isTakeaway, $newCust)
{
    echo "<div class='container my-16 px-6 mx-auto'>";

    echo "<div class='pb-16 navbar text-neutral-content'>";
    // Go Back Button
    echo "<div class='navbar-start'><form class='mb-0' action=view.php method='post'>";
    echo "<input type='hidden' name='action' value='goBackFromCart'><input class='btn btn-primary' type='submit' value='Back to Menu' />";
    echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
    echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
    echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
    echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
    echo "</form></div></div>";

    $sum = 0;
    $items = getCartItems();
    $gstTax = getPriceConstants(1)['priceModifier'];
    $itemIds = array();
    $counter = 1;
    $totalAddOnPrice = 0;

    echo "<b>Your Cart</b><br><br>";

    if (!empty($items)) {
        echo "<div class='overflow-x-auto'>";
        echo "<table class='table table-zebra w-full'>";
        echo "<thead><tr><th></th><th class='w-1/2'>Item Name</th><th class='w-1/2'>Action</th><th>Price</th></tr></thead><tbody>";
        foreach ($items as $item):
            echo "<tr>";
            echo sprintf("<td>%s</td>", $counter);
            echo sprintf("<td>%s", $item['itemName']);

            if (!empty($item['itemAddOns'])) {
                $itemTypeIdsArray = explode(",", $item['itemAddOns']);

                foreach ($itemTypeIdsArray as $itemTypeId):
                    $itemTypeAddOnsData = getItemTypeAddOnsFromPK($itemTypeId);
                    echo sprintf("<br>%s", $itemTypeAddOnsData['itemTypeAddOnsName']);
                endforeach;
                echo "</td>";
            }

            echo "<td><form class='mb-0' action=view.php method='post'>";
            echo "<input type='hidden' name='action' value='removeItemFromCart'>";
            echo sprintf("<input class='btn btn-sm btn-error' type='submit' value='Remove' />");
            echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
            echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
            echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
            echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
            echo sprintf("<input type='hidden' name='cartId' value='%s'>", $item['cartId']);
            echo "</form></td>";

            echo sprintf("<td>$%s", $item['itemPrice']);
            if (!empty($item['itemAddOns'])) {
                $itemTypeIdsArray = explode(",", $item['itemAddOns']);
                foreach ($itemTypeIdsArray as $itemTypeId):
                    $itemTypeAddOnsData = getItemTypeAddOnsFromPK($itemTypeId);
                    echo sprintf("<br>$%s", $itemTypeAddOnsData['itemTypeAddOnsPriceModifier']);
                    $totalAddOnPrice += $itemTypeAddOnsData['itemTypeAddOnsPriceModifier'];
                endforeach;
                echo "</td>";
            }

            echo "</td>";

            $sum += $item['itemPrice'];
            array_push($itemIds, $item['menuItemId']);
            echo "</tr>";
            $counter++;
        endforeach;
        $billItemIds = implode(",", $itemIds);
        echo "</tbody></table>";
        echo "</div>";
    } else {
        echo "<div><p><i>Your cart is empty.</i></p></div>";
    }
    $sum += $totalAddOnPrice;
    $gstTaxValue = $sum * $gstTax;
    $totalSum = $sum + $gstTaxValue + $promotionValue;
    echo "<div class='divider'></div>";

    if (!empty($items)) {
        echo "<div class='w-full flex justify-end'><div class='text-right'>";

        echo "<form class='mb-8' action=view.php method='post'>";
        echo "<input type='text' placeholder='Promotion Code' class='input input-bordered w-1/2 max-w-xs' name='promotionCode'/>";
        echo "<input type='hidden' name='action' value='applyPromotionCode'><input class='btn btn-primary w-1/2 max-w-xs' type='submit' value='Apply' />";
        echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
        echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
        echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
        echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
        echo "</form>";

        echo sprintf("<b>Price: <u>$%s</u></b>", number_format((float) $sum, 2, '.', ''));
        echo "<br>";
        echo sprintf("<b>GST: <u>$%s</u></b>", number_format((float) $gstTaxValue, 2, '.', ''));
        echo "<br>";

        if ($promotionValue != 0) {
            echo sprintf("<b>Promotion: <u>%s</u></b>", number_format((float) $promotionValue, 2, '.', ''));
            echo "<br>";
        }

        if ($isTakeaway == true) {
            $takeawayFee = getPriceConstants(2)['priceModifier'];
            echo sprintf("<b>Takeaway Fee: <u>$%s</u></b>", number_format((float) $takeawayFee, 2, '.', ''));
            echo "<br>";
            $totalSum += $takeawayFee;
        }

        echo sprintf("<b>Total Price: <u>$%s</u></b>", number_format((float) $totalSum, 2, '.', ''));



        echo "</div></div>";

        // Remove All Items from Cart Button
        echo "<div class='pt-5 w-full flex justify-end gap-6'>";

        echo "<div><form action=view.php method='post'>";
        echo "<input type='hidden' name='action' value='removeAllItemsFromCart'><input class='btn btn-error' type='submit' value='Remove all items' />";
        echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
        echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
        echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
        echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
        echo "</form></div>";

        // Pay Button
        echo "<div><form action=view.php method='post'>";
        echo "<input type='hidden' name='action' value='payCart'><input class='btn btn-success' type='submit' value='Proceed to Payment' />";
        echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
        echo sprintf("<input type='hidden' name='sum' value='%s'>", $totalSum);
        echo sprintf("<input type='hidden' name='billItemIds' value='%s'>", $billItemIds);
        echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
        echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
        echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
        echo "</form></div>";

        echo "</div>";
    }

    echo "</div>";
}

function displayPay($newCust, $branchId, $sum, $billItemIds, $tableId, $isTakeaway, $memberNumber = null, $usePoints = false)
{
    $pointsGotten = 0;
    $pointsDeducted = 0;
    $member = getMember($memberNumber);
    echo "<div class='container my-16 px-6 mx-auto'>";

    echo "<div class='pb-16 navbar text-neutral-content'>";
    // Go Back Button
    echo "<div class='navbar-start'><form class='mb-0' action=view.php method='post'>";
    echo "<input type='hidden' name='action' value='goBackFromPay'><input class='btn btn-primary' type='submit' value='Back to Cart' />";
    echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
    echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
    echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
    echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
    echo "</form></div></div>";

    echo "<div class='pt-16 grid justify-center items-center'>";
    echo sprintf("<div class='text-center stat-value'><b>$%s</b></div>", number_format((float) $sum, 2, '.', ''));

    if (!empty($member['memberNumber'])) {
        echo sprintf("<p class='text-center pt-10'>You have %s points.<p><br>", $member['totalPoints']);

        if ($member['totalPoints'] >= 10 && $usePoints == false) {
            echo "<p class='text-center'>Redeem 10 points for $2 off.<p><br>";
            echo "<form class='mb-0' action=view.php method='post'>";
            echo "<input type='hidden' name='action' value='redeemPoints'><input class='w-full btn btn-primary' type='submit' value='Redeem' />";
            echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
            echo sprintf("<input type='hidden' name='memberNumber' value='%s'>", $memberNumber);
            echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
            echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
            echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
            echo sprintf("<input type='hidden' name='billItemIds' value='%s'>", $billItemIds);
            echo sprintf("<input type='hidden' name='sum' value='%s'>", $sum);
            echo "</form>";
        } else if ($usePoints == true) {
            echo "<form class='mb-0' action=view.php method='post'>";
            echo "<input type='hidden' name='action' value='cancelRedemption'><input class='w-full btn btn-primary' type='submit' value='Cancel Redemption' /><br><br>";
            echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
            echo sprintf("<input type='hidden' name='memberNumber' value='%s'>", $memberNumber);
            echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
            echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
            echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
            echo sprintf("<input type='hidden' name='billItemIds' value='%s'>", $billItemIds);
            echo sprintf("<input type='hidden' name='sum' value='%s'>", $sum);
            $pointsDeducted = 10;
            echo "</form>";
        }

        $pointsGotten = floor($sum / 5);
        echo sprintf("<p class='text-center pt-10'>You will gain %s points from this transaction.</p>", $pointsGotten);
    } else {
        echo "<form class='pt-10 mb-0' action=view.php method='post'>";
        echo "<input type='text' placeholder='Member Number' class='input input-bordered w-1/2 max-w-xs' name='memberNumber'/>";
        echo "<input type='hidden' name='action' value='checkMember'><input class='btn btn-primary w-1/2 max-w-xs' type='submit' value='Confirm' />";
        echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
        echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
        echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
        echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
        echo sprintf("<input type='hidden' name='billItemIds' value='%s'>", $billItemIds);
        echo sprintf("<input type='hidden' name='sum' value='%s'>", $sum);
        echo "</form>";
    }

    echo "<div class='pt-10 form-control w-full max-w-xs'><form action=view.php method='post'>";
    echo "
    <select class='text-center w-full select select-bordered' id='payment' name='payment'>
      <option value='VISA/MASTERCARD'>VISA/MASTERCARD</option>
      <option value='AMEX'>AMEX</option>
    </select><br><br>";
    echo "<input type='hidden' name='action' value='submitPayment'><input class='w-full btn btn-success' type='submit' value='Pay' />";
    if (!empty($member['memberNumber'])) {
        echo sprintf("<input type='hidden' name='memberNumber' value='%s'>", $member['memberNumber']);
        echo sprintf("<input type='hidden' name='pointsGotten' value='%s'>", $pointsGotten);
        echo sprintf("<input type='hidden' name='pointsDeducted' value='%s'>", $pointsDeducted);
    }
    echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
    echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
    echo sprintf("<input type='hidden' name='sum' value='%s'>", $sum);
    echo sprintf("<input type='hidden' name='billItemIds' value='%s'>", $billItemIds);
    echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
    echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
    echo "</form></div>";
    echo "</div>";

    echo "</div>";
}

function displayPopUp()
{
    $cartItem = getLatestCart();
    $menuItem = getMenuItemFromCart($cartItem['menuItemId']);

    echo "<div class='toast toast-end'>";
    echo "<div class='alert alert-success'>";
    echo "<div>";
    echo sprintf("<p><b>%s</b> has been added to your cart.</p>", $menuItem['menuItemName']);
    echo "</div>";
    echo "</div>";
}

function displayDiscountPopUp()
{
    echo "<div class='toast toast-end'>";
    echo "<div class='alert alert-success'>";
    echo "<div>";
    echo sprintf("<p>Promotion Code has been applied.</p>");
    echo "</div>";
    echo "</div>";
}

function displayMenu($branchId, $tableId, $isTakeaway, $newCust)
{

    $cartCount = getAllCart();
    echo "<div class='container my-16 px-6 mx-auto'>";

    echo "<div class='pb-16 navbar text-neutral-content'>";
    if ($isTakeaway == false) {
        // Go Back Button
        echo "<div class='navbar-start'><form class='mb-0' action=view.php method='post'>";
        echo "<input type='hidden' name='action' value='goBackFromMenu'><input class='btn btn-primary' type='submit' value='Back to Tables' />";
        echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
        echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
        echo "</form></div>";
    } else {
        // Go Back Button
        echo "<div class='navbar-start'><form class='mb-0' action=view.php method='post'>";
        echo "<input type='hidden' name='action' value='goBackFromTables'><input class='btn btn-primary' type='submit' value='Back to Dining Options' />";
        echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
        echo "</form></div>";
    }

    // View Cart Button
    echo "<div class='navbar-end'><div class='indicator'><form class='mb-0' action=view.php method='post'>";
    echo "<span class='indicator-item badge badge-secondary'>" . sizeof($cartCount) . "</span><input type='hidden' name='action' value='viewCart'><input class='btn btn-success' type='submit' value='View Cart' />";
    echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
    echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
    echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
    echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
    echo "</form></div></div>";
    echo "</div>";
    $branch = getBranchFromTable($branchId);

    echo sprintf("<p class='text-lg'>Branch: <b>%s</b></p>", $branch['branchName']);
    if ($isTakeaway == false) {
        $table = getTable($tableId);
        echo sprintf("<p class='text-lg'>Table Number: <b>%s</b></p><br>", $table['tableNo']);
    } else {
        echo "<br>";
    }

    $menuId = (getMenu($branchId))['menuId'];
    $menuCategories = getMenuCategories($menuId);

    echo "<div class='text-center'>";
    foreach ($menuCategories as $menuCategory):
        echo sprintf("<a class='btn btn-sm ml-5' href='#%s'>%s</a>", $menuCategory['menuCategoryName'], $menuCategory['menuCategoryName']);
    endforeach;
    echo "</div>";

    foreach ($menuCategories as $menuCategory):
        echo sprintf("<div id='%s'>", $menuCategory['menuCategoryName']);
        echo sprintf("<b>%s</b>", $menuCategory['menuCategoryName']);
        echo "</div>";
        echo "<div class='divider'></div>";
        echo "<div class='grid xs:grid-cols-1 lg:grid-cols-5 gap-6'>";
        $menuItems = getMenuItem($menuCategory['menuCategoryId']);
        foreach ($menuItems as $menuItem):
            echo "<div class='card bg-neutral mb-5'>";

            echo "<div class='card-body items-center text-center'>";
            echo sprintf("<div class='stat-title'><b>%s</b></div>", $menuItem['menuItemName']);
            echo sprintf("<div class='stat-value'>$%s</div>", $menuItem['price']);
            echo sprintf("<div class='stat-desc'>%s</div>", $menuItem['menuItemDescription']);

            if (!empty($menuItem['itemTypeId'])) {
                echo "<div class='stat-actions'><form action=view.php method='post'>";
                echo "<input type='hidden' name='action' value='viewItemAddOns'>";
                // input button to add item into the cart and hidden value to keep track on the itemId added 
                echo sprintf("<input class='btn btn-sm btn-primary' type='submit' value='Add to Cart' />");
                echo sprintf("<input type='hidden' name='menuItemId' value='%s'>", $menuItem['menuItemId']);
                echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
                echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
                echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
                echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
                echo "</form></div>";
                echo "</div>";
            } else {
                echo "<div class='stat-actions'><form action=view.php method='post'>";
                echo "<input type='hidden' name='action' value='addToCart'>";
                // input button to add item into the cart and hidden value to keep track on the itemId added 
                echo sprintf("<input class='btn btn-sm btn-primary' type='submit' value='Add to Cart' />");
                echo sprintf("<input type='hidden' name='menuItemId' value='%s'>", $menuItem['menuItemId']);
                echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
                echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
                echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
                echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
                echo "</form></div>";
                echo "</div>";
            }
            echo "</div>";
        endforeach;
        echo "</div>";
    endforeach;
    echo "</div>";
}

function displayTables($branchId, $isTakeaway)
{
    echo "<div class='container my-16 px-6 mx-auto'>";
    echo "<div class='navbar text-neutral-content'>";
    // Go Back Button
    echo "<div class='navbar-start'><form class='mb-0' action=view.php method='post'>";
    echo "<input type='hidden' name='action' value='goBackFromTables'><input class='btn btn-primary' type='submit' value='Back to Dining Options' />";
    echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
    echo "</form></div>";
    echo "</div>";

    $tables = getAllTables($branchId);
    $branch = getBranchFromTable($branchId);
    echo sprintf("<p class='pt-16 text-lg'>Branch: <b>%s</b></p><div class='divider'></div>", $branch['branchName']);
    echo "<div class='grid lg:grid-cols-3 gap-6'>";
    foreach ($tables as $table):
        echo "<div class='card card-side mb-5 bg-neutral text-neutral-content'>";

        echo "<div class='card-body'>";
        echo "<form action=view.php method='post'>";
        echo "<input type='hidden' name='action' value='selectTable'>";
        echo sprintf("<p class='card-text text-lg pb-5'>Table <b>%s</b></p>", $table['tableNo']);
        if ($table['isReserved'] == 1) {
            echo sprintf("<input class='btn btn-disabled card-actions' value='Occupied' />");
        } else {
            echo sprintf("<input class='btn btn-primary card-actions' type='submit' value='Select Table' />");
            echo sprintf("<input type='hidden' name='tableId' value='%s'>", $table['tableId']);
            echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
            echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
        }
        echo "</form>";
        echo "</div>";

        echo "</div>";
    endforeach;
    echo "</div>";
    echo "</div>";
}

function displayDineOptions($branchId)
{
    echo "<div class='container my-16 px-6 mx-auto'>";
    echo "<div class='navbar text-neutral-content'>";
    // Go Back Button
    echo "<div class='navbar-start'><form class='mb-0' action=view.php method='post'>";
    echo "<input type='hidden' name='action' value='goBack'><input class='btn btn-primary' type='submit' value='Back to Branches' />";
    echo "</form></div>";
    echo "</div>";
    $branch = getBranchFromTable($branchId);
    echo sprintf("<p class='pt-16 text-lg'>Branch: <b>%s</b></p><div class='divider'></div>", $branch['branchName']);
    echo "<div class='grid lg:grid-cols-3 gap-6'>";

    echo "<div class='card card-side mb-5 bg-neutral text-neutral-content'>";
    echo "<div class='card-body'>";
    echo "<form action=view.php method='post'>";
    echo "<input type='hidden' name='action' value='selectDineOptions'>";
    echo sprintf("<input class='btn btn-primary card-actions' type='submit' value='Dine-In' />");
    echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
    echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", false);
    echo "</form>";
    echo "</div>";
    echo "</div>";

    echo "<div class='card card-side mb-5 bg-neutral text-neutral-content'>";
    echo "<div class='card-body'>";
    echo "<form action=view.php method='post'>";
    echo "<input type='hidden' name='action' value='selectDineOptions'>";
    echo sprintf("<input class='btn btn-primary card-actions' type='submit' value='Takeaway' />");
    echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
    echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", true);
    echo "</form>";
    echo "</div>";
    echo "</div>";

    echo "</div>";
    echo "</div>";
}

function displayBranches($branches)
{
    echo "<div class='container my-16 px-6 mx-auto'>";
    echo "<div class='navbar text-neutral-content'>";
    // Home Button
    echo "<form class='mb-0' action=index.php method='post'>";
    echo "<input type='hidden' name='action'><input class='btn btn-primary' type='submit' value='Home' />";
    echo "</form>";
    echo "</div>";

    echo "<div class='pt-16 grid lg:grid-cols-3 gap-6'>";
    foreach ($branches as $branch):
        echo "<div class='card card-side mb-5 bg-neutral text-neutral-content'>";

        echo "<figure><img class='w-80 h-full' src='images/" . $branch['branchImage'] . "'/></figure>";

        echo "<div class='card-body'>";
        echo sprintf("<div class='card-title'><b>%s</b></div>", $branch['branchName']);
        echo "<form action=view.php method='post'>";
        echo "<input type='hidden' name='action' value='selectBranch'>";
        echo sprintf("<p class='card-text text-lg pb-5'>%s</p>", $branch['branchAddress']);
        echo sprintf("<input class='btn btn-primary card-actions' type='submit' value='View Branch' />");
        echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branch['branchId']);
        echo "</form>";
        echo "</div>";

        echo "</div>";
    endforeach;
    echo "</div>";
    echo "</div>";
}

// function displayBranches($branches)
// {
//     displayPageHeader("Branches");

//     echo "<table border=1px;solid cellpadding=5 cellspacing=0;>";

//     displayTableHeaders($branches, 1);

//     foreach ($branches as $branch):
//         echo "<form action=view.php method='post'>";
//         echo "<input type='hidden' name='action' value='selectBranch'>";

//         echo "<tr>";
//         echo sprintf("<td>%s</td>", $branch['branchId']);
//         echo sprintf("<td>%s</td>", $branch['branchName']);
//         echo sprintf("<td>%s</td>", $branch['branchAddress']);
//         echo sprintf("<td>%s</td>", $branch['numberOfTables']);

//         echo sprintf("<td><input type='submit' value='View Branch' /></td>");
//         echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branch['branchId']);

//         echo "</tr>";
//         echo "</form>";
//     endforeach;

//     echo "</table>";
// }

// display table headers
// $extraColumn is for adding extra column at the end for buttons
// // $color is for changing the header colors, default is orange header
// function displayTableHeaders($headerArray, $extraColumn, $color = 'orange')
// {

//     // to get the item variable names
//     $headerRow = array_keys($headerArray[0]);

//     echo sprintf("<tr bgcolor='%s'>", $color);
//     foreach ($headerRow as $column):
//         echo sprintf("<th>%s</th>", $column);
//     endforeach;

//     // if extraColumn is more than 0, it will get ($extraColumn) amount of columns
//     if ($extraColumn != 0):
//         for ($i = 0; $i < $extraColumn; $i++):
//             echo sprintf("<th></th>", $column);
//         endfor;
//     endif;
//     echo "</tr>";
// }

?>