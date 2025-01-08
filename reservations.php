<?php
require 'db.php';

$sql = "SELECT * FROM reservation";
$stmt = $pdo->query($sql);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($reservations);
?>