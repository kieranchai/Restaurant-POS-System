<?php
require_once('DB.class.php'); // controls all the connection to Database
require_once('ControllerFunction.php');
require_once('ControllerDisplay.php');

// Initialise the database connection.
// The app uses a self-contained SQLite file (no server to install). On first
// run the file is created and seeded automatically from sql/schema.sqlite.sql.
$DB = new DB();
$DB->connectSqlite(__DIR__ . '/data/ember.sqlite', __DIR__ . '/sql/schema.sqlite.sql');

// to print out Array in a nice HTML format for easy reading
function printArray($array)
{
    echo "<pre>";
    echo "<p>=== FOR DEBUGGING PURPOSES ====</p>";
    print_r($array);
    echo "<p>===============================</p>";
    echo "</pre>";
}

// to print out the top green header and show what page you are on
function displayPageHeader($pageName)
{
    $pageHeader = sprintf("<h1><font color='#00008B'>%s</font></h1>", $pageName);
    echo $pageHeader;
}
?>