<?php
/* Shared document head + brand bar for every page.
   Set $pageTitle before including; optionally set $barSlot for right-side content. */
if (!isset($pageTitle)) { $pageTitle = "Ember POS"; }
if (!isset($barSlot))   { $barSlot = ""; }
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.6/dist/full.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2/dist/tailwind.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/app.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <header class="site-bar">
        <a class="brand" href="index.php">
            <span class="brand-name">Ember<span class="brand-dot"> POS</span></span>
        </a>
        <?= $barSlot ?>
    </header>

    <main class="app-main">
