<?php

declare(strict_types=1);

//------------------------------------------------------------------ session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'another-page';


require 'src/html-head.php';
?>

<body>
    <?php
    require 'src/html-nav.php';

    if (empty($_SESSION['count'])) {
        $_SESSION['count'] = 1;
    } else {
        $_SESSION['count']++;
    }

    if (isset($_SESSION['firstname'])) {
        echo '<h1 class="header">' . $_SESSION['firstname'] . '</h1>';
    }

    if (isset($_SESSION['lastname'])) {
        echo '<h2>' . $_SESSION['lastname'] . '</h2>';
    }

    if (isset($_SESSION['gender'])) {
        echo '<h3>' . $_SESSION['gender'] . '</h3>';
    }

    echo "<h3>page seen {$_SESSION['count']} times.</h3>";
    echo '<h2>SID : ' . htmlspecialchars(session_id()) . '</h2>';

    ?>

    <?php require 'src/globals-dump.php' ?>
</body>

</html>