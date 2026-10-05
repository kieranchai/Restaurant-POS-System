<?php
/******************************************************************
   index.php
   Landing page: guests start a self-order, staff log in.
   PHP logic runs first so header() redirects work correctly.
   ******************************************************************/
include("Common.php");

$error = "";
if (isset($_POST['login']) && !empty($_POST['username']) && !empty($_POST['userpassword'])) {
    $user = getUser($_POST['username'], $_POST['userpassword']);
    if (is_array($user)) {
        $userRole = $user['userRole'];
        header("location: admin.php?role=" . $userRole);
        exit;
    } else {
        $error = "Your username or password is invalid.";
    }
}

$pageTitle = "Ember POS";
include("partials/head.php");
?>

<div class="auth-wrap">
    <div class="auth-card">

        <h1>Ember POS</h1>
        <p class="auth-sub">Restaurant point of sale and self ordering</p>

        <?php
        if ($error) {
            echo "<div class='alert alert-error mb-4'><div>";
            echo sprintf("<span>%s</span>", $error);
            echo "</div></div>";
        }
        ?>

        <form action="view.php" method="post">
            <input type="hidden" name="action">
            <input class="btn btn-success btn-block" type="submit" value="Start Self-Order" />
        </form>

        <div class="auth-divider">Staff login</div>

        <form method="post">
            <label class="field-label" for="username">Username</label>
            <input id="username" class="input w-full mb-3" type="text" name="username" placeholder="Username" data-vgroup="staffLogin" data-validate-field required>

            <label class="field-label" for="userpassword">Password</label>
            <input id="userpassword" class="input w-full mb-4" type="password" name="userpassword" placeholder="Password" data-vgroup="staffLogin" data-validate-field required>

            <input type="hidden" name="login">
            <input class="btn btn-primary btn-block" type="submit" value="Log In" data-vgroup="staffLogin" data-validate-btn />
        </form>

        <p class="auth-hint">
            Demo accounts:
            <button type="button" class="demo-fill" data-user="admin" data-pass="admin1">admin</button>
            <button type="button" class="demo-fill" data-user="staff" data-pass="johnlogin">staff</button>
        </p>

    </div>
</div>

<?php include("partials/footer.php"); ?>
