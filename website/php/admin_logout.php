<?php

session_start();
session_destroy();
header("Location: /php/admin_login.php");
exit();
