<?php

require_once __DIR__ . "/DataSource.php";
$query = "SELECT * FROM tbl_url WHERE short_url_hash = ?";
$paramType = "s";
$paramValueArray = array($_GET["hash"]);

$ds = new DataSource();
$result = $ds->select($query, $paramType, $paramValueArray);

if(!empty($result[0]["original_url"])) {
header("Location: " . urldecode($result[0]["original_url"]));
} else {
    header('HTTP/1.0 404 Not Found');
}
