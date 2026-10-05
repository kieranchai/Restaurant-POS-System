<?php
/******************************************************************
   ControllerDisplay.php
   This file communicates with View.php and responsible for all the display output
   ******************************************************************/

/* Maps a menu item name to its product photo in images/menu.
   Falls back to a neutral placeholder when no match is found. */
function menuItemImage($menuItemName)
{
    $map = array(
        '3pc Flame-Grilled Chicken' => 'chicken3pc.jpg',
        '6pc Sharing Bucket'        => 'bucket6pc.jpg',
        '9pc Family Bucket'         => 'bucket9pc.jpg',
        'Ember Classic Burger'      => 'burger-classic.jpg',
        'Spicy Zinger Burger'       => 'burger-zinger.jpg',
        'Double Smash Burger'       => 'burger-smash.jpg',
        'Seasoned Fries'            => 'fries.jpg',
        'Creamy Mash'               => 'mash.jpg',
        'Garden Coleslaw'           => 'coleslaw.jpg',
        'Soft Drink'                => 'drink-soft.jpg',
        'Iced Lemon Tea'            => 'lemon-tea.jpg',
        'Fresh Orange Juice'        => 'orange-juice.jpg',
    );
    $file = isset($map[$menuItemName]) ? $map[$menuItemName] : 'placeholder.jpg';
    return 'images/menu/' . $file;
}
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

    echo "<div class='pb-6'>";
    echo "<h1 class='section-title'>Order Complete</h1>";
    echo "<p class='section-sub'>Your payment was successful. A summary is below.</p>";
    echo "</div>";

    echo "<div class='grid md:grid-cols-2 gap-6 max-w-3xl mx-auto'>";

    echo "<div class='panel'><b>Payment Receipt</b><div class='divider'></div>";
    echo sprintf("<p>Transaction ID: <b>%s</b></p>", $payment['paymentId']);
    echo sprintf("<p>Payment Method: <b>%s</b></p>", $payment['paymentMethod']);
    echo sprintf("<p>Amount Paid: <b>$%s</b></p>", number_format((float) $payment['totalAmount'], 2, '.', ''));
    echo sprintf("<p>Transaction Date: <b>%s</b></p>", $payment['paymentDateTime']);
    if ($isTakeaway == false) {
        echo sprintf("<p>Table Number: <b>%s</b></p>", $table['tableNo']);
    } else {
        echo "<p>Order Type: <b>Takeaway</b></p>";
    }
    echo "</div>";

    echo "<div class='panel'><b>Order Receipt</b><div class='divider'></div>";
    foreach ($cartItem as $item):
        $menuItem = getMenuItemFromCart($item);
        echo sprintf("<p>%s</p>", $menuItem['menuItemName']);
    endforeach;
    echo "</div>";

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

    echo "<div class='pb-2'><span class='section-eyebrow'>Customise your order</span>";
    echo sprintf("<h1 class='section-title'>%s</h1></div>", $menuItem['menuItemName']);
    echo "<div class='divider'></div>";

    $itemTypeAddOns = getItemTypeAddOns($menuItem['itemTypeId']);

    echo "<form action='view.php' method='post'>";
    if ($menuItem['itemTypeId'] == 1) {
        foreach ($itemTypeAddOns as $itemTypeAddOn):
            echo "<div class='form-control w-1/4'><label class='label cursor-pointer'>";
            echo sprintf("<label class='label-text' for='%s'>%s<br>+<b>$%s</b></label>", $itemTypeAddOn['itemTypeAddOnsId'], $itemTypeAddOn['itemTypeAddOnsName'], number_format((float) $itemTypeAddOn['itemTypeAddOnsPriceModifier'], 2, '.', ''));
            echo sprintf("<input class='checkbox checkbox-primary' type='checkbox' name='itemTypes[]' value='%s' id='%s'>", $itemTypeAddOn['itemTypeAddOnsId'], $itemTypeAddOn['itemTypeAddOnsId']);
            echo "</label></div><br><br>";
        endforeach;
    } else if ($menuItem['itemTypeId'] == 2) {
        foreach ($itemTypeAddOns as $itemTypeAddOn):
            echo "<div class='form-control w-1/4'><label class='label cursor-pointer'>";
            echo sprintf("<label class='label-text' for='%s' required>%s<br>+<b>$%s</b></label>", $itemTypeAddOn['itemTypeAddOnsId'], $itemTypeAddOn['itemTypeAddOnsName'], number_format((float) $itemTypeAddOn['itemTypeAddOnsPriceModifier'], 2, '.', ''));
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

function displayCart($branchId, $promotionValue, $tableId, $isTakeaway, $newCust, $promoError = false)
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

    echo "<div class='pb-6'><span class='section-eyebrow'>Review</span><h1 class='section-title'>Your cart</h1></div>";

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

            echo sprintf("<td>$%s", number_format((float) $item['itemPrice'], 2, '.', ''));
            if (!empty($item['itemAddOns'])) {
                $itemTypeIdsArray = explode(",", $item['itemAddOns']);
                foreach ($itemTypeIdsArray as $itemTypeId):
                    $itemTypeAddOnsData = getItemTypeAddOnsFromPK($itemTypeId);
                    echo sprintf("<br>$%s", number_format((float) $itemTypeAddOnsData['itemTypeAddOnsPriceModifier'], 2, '.', ''));
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
        echo "<div class='w-full flex justify-end mt-4'><div class='panel text-right' style='min-width:340px'>";

        if ($promoError) {
            echo "<div class='alert alert-error mb-3 text-left'><div><span>That promotion code is not valid.</span></div></div>";
        }
        echo "<form class='mb-6 flex gap-2' action=view.php method='post'>";
        echo "<input type='text' placeholder='Promotion Code' class='input input-bordered w-full' name='promotionCode' data-vgroup='promo' data-validate-field/>";
        echo "<input type='hidden' name='action' value='applyPromotionCode'><input class='btn btn-primary' type='submit' value='Apply' data-vgroup='promo' data-validate-btn/>";
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
            $promoSign = $promotionValue < 0 ? '-$' : '$';
            echo sprintf("<b>Promotion: <u>%s%s</u></b>", $promoSign, number_format(abs((float) $promotionValue), 2, '.', ''));
            echo "<br>";
        }

        if ($isTakeaway == true) {
            $takeawayFee = getPriceConstants(2)['priceModifier'];
            echo sprintf("<b>Takeaway Fee: <u>$%s</u></b>", number_format((float) $takeawayFee, 2, '.', ''));
            echo "<br>";
            $totalSum += $takeawayFee;
        }

        echo "<div class='divider'></div>";
        echo sprintf("<div style='font-weight:800;font-size:1.4rem;color:#20242e'>Total: $%s</div>", number_format((float) $totalSum, 2, '.', ''));


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

function displayPay($newCust, $branchId, $sum, $billItemIds, $tableId, $isTakeaway, $memberNumber = null, $usePoints = false, $memberError = false)
{
    $pointsGotten = 0;
    $pointsDeducted = 0;
    $member = getMember($memberNumber);
    $hasMember = is_array($member) && !empty($member['memberNumber']);

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

    echo "<div class='pt-6 flex flex-col items-center'>";
    echo "<div class='text-center pb-2'><span class='section-eyebrow'>Checkout</span><h1 class='section-title'>Payment</h1></div>";
    echo sprintf("<div class='text-center stat-value' style='font-size:2.6rem'>$%s</div>", number_format((float) $sum, 2, '.', ''));

    echo "<div class='w-full max-w-sm pt-8'>";

    $pointsGotten = floor($sum / 5);

    if ($hasMember) {
        // Member summary: name, current points, and points earned from this order.
        echo "<div class='member-card'>";
        echo sprintf("<div class='member-name'>%s %s</div>", htmlspecialchars($member['memberFirstName']), htmlspecialchars($member['memberLastName']));
        echo sprintf("<div class='member-stat'>Member number: %s</div>", htmlspecialchars($member['memberNumber']));
        echo sprintf("<div class='member-stat'>Current balance: <b>%s points</b></div>", $member['totalPoints']);
        echo sprintf("<div class='member-stat'>You will earn <b>%s points</b> from this order.</div>", $pointsGotten);
        echo "</div>";

        if ($member['totalPoints'] >= 10 && $usePoints == false) {
            echo "<p class='text-center' style='margin-bottom:.5rem'>Redeem 10 points for $2 off.</p>";
            echo "<form class='mb-4' action=view.php method='post'>";
            echo "<input type='hidden' name='action' value='redeemPoints'><input class='w-full btn btn-primary' type='submit' value='Redeem 10 points' />";
            echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
            echo sprintf("<input type='hidden' name='memberNumber' value='%s'>", $memberNumber);
            echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
            echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
            echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
            echo sprintf("<input type='hidden' name='billItemIds' value='%s'>", $billItemIds);
            echo sprintf("<input type='hidden' name='sum' value='%s'>", $sum);
            echo "</form>";
        } else if ($usePoints == true) {
            echo "<form class='mb-4' action=view.php method='post'>";
            echo "<input type='hidden' name='action' value='cancelRedemption'><input class='w-full btn btn-error' type='submit' value='Cancel Redemption' />";
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
    } else {
        // Member lookup. Confirm stays disabled until a number is entered.
        if ($memberError) {
            echo "<div class='alert alert-error mb-3'><div><span>No member found with that number. Check and try again, or continue without a member.</span></div></div>";
        }
        echo "<label class='field-label'>Member number (optional)</label>";
        echo "<form class='mb-0 flex gap-2' action=view.php method='post'>";
        echo "<input type='text' placeholder='e.g. 91234567' class='input input-bordered flex-1' name='memberNumber' data-vgroup='memberLookup' data-validate-field/>";
        echo "<input type='hidden' name='action' value='checkMember'><input class='btn btn-primary' type='submit' value='Confirm' data-vgroup='memberLookup' data-validate-btn/>";
        echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
        echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
        echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
        echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
        echo sprintf("<input type='hidden' name='billItemIds' value='%s'>", $billItemIds);
        echo sprintf("<input type='hidden' name='sum' value='%s'>", $sum);
        echo "</form>";
    }

    // Payment method as two clear buttons instead of a dropdown.
    echo "<form class='pt-6' action=view.php method='post'>";
    echo "<label class='field-label'>Card type</label>";
    echo "<div class='pay-methods'>";
    echo "<label class='pay-method'><input type='radio' name='payment' value='VISA/MASTERCARD' checked><span>VISA / Mastercard</span></label>";
    echo "<label class='pay-method'><input type='radio' name='payment' value='AMEX'><span>AMEX</span></label>";
    echo "</div>";
    echo "<input type='hidden' name='action' value='submitPayment'><input class='w-full btn btn-success' type='submit' value='Pay $" . number_format((float) $sum, 2, '.', '') . "' />";
    if ($hasMember) {
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
    echo "</form>";

    echo "</div>";
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

    echo "<div class='navbar text-neutral-content menu-topbar'>";
    if ($isTakeaway == false) {
        // Go Back Button
        echo "<div class='navbar-start'><form class='mb-0' action=view.php method='post'>";
        echo "<input type='hidden' name='action' value='goBackFromMenu'><input class='btn btn-primary' type='submit' value='Back to Tables' />";
        echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
        echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
        echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
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

    echo "<div class='pb-4'>";
    echo "<span class='section-eyebrow'>Step 4 of 4</span>";
    echo "<h1 class='section-title'>Menu</h1>";
    if ($isTakeaway == false) {
        $table = getTable($tableId);
        echo sprintf("<p class='section-sub'>%s &nbsp;·&nbsp; Table <b>%s</b></p>", $branch['branchName'], $table['tableNo']);
    } else {
        echo sprintf("<p class='section-sub'>%s &nbsp;·&nbsp; <b>Takeaway</b></p>", $branch['branchName']);
    }
    echo "</div>";

    $menuId = (getMenu($branchId))['menuId'];
    $menuCategories = getMenuCategories($menuId);

    // Two column layout: sticky category rail on the left, items on the right.
    echo "<div class='menu-layout'>";

    echo "<aside class='menu-side'>";
    foreach ($menuCategories as $menuCategory):
        $catSlug = preg_replace('/[^a-z0-9]+/', '-', strtolower($menuCategory['menuCategoryName']));
        echo sprintf("<a class='menu-side-link' href='#%s' data-spy-link='%s'>%s</a>", $catSlug, $catSlug, $menuCategory['menuCategoryName']);
    endforeach;
    echo "</aside>";

    echo "<div class='menu-items'>";
    foreach ($menuCategories as $menuCategory):
        $catSlug = preg_replace('/[^a-z0-9]+/', '-', strtolower($menuCategory['menuCategoryName']));
        echo sprintf("<h2 id='%s' class='menu-category-title' data-spy-section='%s'>%s</h2>", $catSlug, $catSlug, $menuCategory['menuCategoryName']);
        echo "<div class='divider'></div>";
        echo "<div class='grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6'>";
        $menuItems = getMenuItem($menuCategory['menuCategoryId']);
        foreach ($menuItems as $menuItem):
            $itemImage = menuItemImage($menuItem['menuItemName']);
            $action = !empty($menuItem['itemTypeId']) ? 'viewItemAddOns' : 'addToCart';

            echo "<div class='card bg-neutral mb-5'>";

            echo sprintf("<div class='item-media'><img src='%s' alt='%s'/></div>", $itemImage, htmlspecialchars($menuItem['menuItemName']));

            echo "<div class='card-body'>";
            echo sprintf("<div class='card-title'>%s</div>", $menuItem['menuItemName']);
            echo sprintf("<p class='stat-desc' style='min-height:2.6rem'>%s</p>", $menuItem['menuItemDescription']);
            echo sprintf("<div class='stat-value' style='font-size:1.25rem'>$%s</div>", number_format((float) $menuItem['price'], 2, '.', ''));

            echo "<form class='mt-auto pt-3' action=view.php method='post'>";
            echo sprintf("<input type='hidden' name='action' value='%s'>", $action);
            echo sprintf("<input class='btn btn-sm btn-success w-full' type='submit' value='Add to Cart' />");
            echo sprintf("<input type='hidden' name='menuItemId' value='%s'>", $menuItem['menuItemId']);
            echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
            echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", $isTakeaway);
            echo sprintf("<input type='hidden' name='tableId' value='%s'>", $tableId);
            echo sprintf("<input type='hidden' name='newCust' value='%s'>", $newCust);
            echo "</form>";

            echo "</div>";
            echo "</div>";
        endforeach;
        echo "</div>";
    endforeach;
    echo "</div>";   // .menu-items
    echo "</div>";   // .menu-layout
    echo "</div>";   // .container
}

function displayTables($branchId, $isTakeaway, $currentTableId = null)
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

    echo "<div class='pt-6 pb-8'>";
    echo "<span class='section-eyebrow'>Step 3 of 4</span>";
    echo "<h1 class='section-title'>Select a table</h1>";
    echo sprintf("<p class='section-sub'>Available tables at <b>%s</b>.</p>", $branch['branchName']);
    echo "</div>";

    echo "<div class='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6'>";
    foreach ($tables as $table):
        echo "<div class='card bg-neutral text-neutral-content'>";

        echo "<div class='card-body items-center text-center'>";
        echo sprintf("<div class='stat-value'>%s</div>", $table['tableNo']);
        echo "<p class='stat-desc' style='margin-bottom:.5rem'>Table number</p>";
        echo "<form class='w-full mt-auto' action=view.php method='post'>";
        echo "<input type='hidden' name='action' value='selectTable'>";
        $isOwnTable = ($currentTableId !== null && $currentTableId == $table['tableId']);
        if ($table['isReserved'] == 1 && !$isOwnTable) {
            echo sprintf("<input class='btn btn-disabled w-full' value='Occupied' />");
        } else {
            $label = $isOwnTable ? 'Your table' : 'Select';
            $btnClass = $isOwnTable ? 'btn btn-success w-full' : 'btn btn-primary w-full';
            echo sprintf("<input class='%s' type='submit' value='%s' />", $btnClass, $label);
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

    echo "<div class='pt-6 pb-8'>";
    echo "<span class='section-eyebrow'>Step 2 of 4</span>";
    echo "<h1 class='section-title'>How would you like to order?</h1>";
    echo sprintf("<p class='section-sub'>Ordering from <b>%s</b>.</p>", $branch['branchName']);
    echo "</div>";

    echo "<div class='grid md:grid-cols-2 gap-6 max-w-3xl'>";

    // Dine-In
    echo "<div class='card bg-neutral text-neutral-content'>";
    echo "<div class='card-body items-center text-center'>";
    echo "<div class='card-title'>Dine-In</div>";
    echo "<p class='stat-desc pb-4'>Pick a table and enjoy your meal with us.</p>";
    echo "<form class='w-full mt-auto' action=view.php method='post'>";
    echo "<input type='hidden' name='action' value='selectDineOptions'>";
    echo sprintf("<input class='btn btn-primary w-full' type='submit' value='Dine-In' />");
    echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branchId);
    echo sprintf("<input type='hidden' name='isTakeaway' value='%s'>", false);
    echo "</form>";
    echo "</div>";
    echo "</div>";

    // Takeaway
    echo "<div class='card bg-neutral text-neutral-content'>";
    echo "<div class='card-body items-center text-center'>";
    echo "<div class='card-title'>Takeaway</div>";
    echo "<p class='stat-desc pb-4'>Grab your order to go. A small packaging fee applies.</p>";
    echo "<form class='w-full mt-auto' action=view.php method='post'>";
    echo "<input type='hidden' name='action' value='selectDineOptions'>";
    echo sprintf("<input class='btn btn-primary w-full' type='submit' value='Takeaway' />");
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
    echo "<div class='container my-12 px-6 mx-auto'>";

    echo "<div class='navbar px-0'>";
    // Home Button
    echo "<form class='mb-0' action=index.php method='post'>";
    echo "<input type='hidden' name='action'><input class='btn btn-primary' type='submit' value='← Home' />";
    echo "</form>";
    echo "</div>";

    echo "<div class='pt-6 pb-8'>";
    echo "<span class='section-eyebrow'>Step 1 of 4</span>";
    echo "<h1 class='section-title'>Choose your outlet</h1>";
    echo "<p class='section-sub'>Pick the Ember location you're ordering from.</p>";
    echo "</div>";

    echo "<div class='grid md:grid-cols-2 lg:grid-cols-3 gap-6'>";
    foreach ($branches as $branch):
        echo "<div class='card bg-neutral text-neutral-content'>";

        echo "<figure class='h-44'><img src='images/" . $branch['branchImage'] . "' alt='" . $branch['branchName'] . "'/></figure>";

        echo "<div class='card-body'>";
        echo sprintf("<div class='card-title'>%s</div>", $branch['branchName']);
        echo sprintf("<p class='stat-desc pb-4'>%s</p>", $branch['branchAddress']);
        echo "<form class='card-actions mt-auto' action=view.php method='post'>";
        echo "<input type='hidden' name='action' value='selectBranch'>";
        echo sprintf("<input class='btn btn-primary w-full' type='submit' value='View Menu →' />");
        echo sprintf("<input type='hidden' name='branchId' value='%s'>", $branch['branchId']);
        echo "</form>";
        echo "</div>";

        echo "</div>";
    endforeach;
    echo "</div>";
    echo "</div>";
}

?>