<?php
require_once "../clases/libreriaDb.php";

$db = new LibreriaDB();

$db->execute("SET FOREIGN_KEY_CHECKS = 0");
$db->execute("TRUNCATE TABLE libros");
$db->execute("SET FOREIGN_KEY_CHECKS = 1");