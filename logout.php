<?php
session_start();
session_unset();
session_destroy();
header("Location: Halaman_Awal.php");
exit;
?>
 