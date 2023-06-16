<?php
session_unset();
session_destroy();
unset($_SESSION["user"]);
header("Location: ../../login.php");
exit();