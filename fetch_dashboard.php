<?php
include 'conn.php'; // your DB connection file

// Example queries
$totalroom = $conn->query("SELECT COUNT(*) AS total FROM classrooms")->fetch_assoc();
$totalusers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc();
$totaloccupied = $conn->query("SELECT COUNT(*) AS total FROM classrooms WHERE status='Occupied'")->fetch_assoc();
$totalUnoccupied = $conn->query("SELECT COUNT(*) AS total FROM classrooms WHERE status='Unoccupied'")->fetch_assoc();

echo json_encode([
  'totalroom' => $totalroom['total'],
  'totalusers' => $totalusers['total'],
  'totaloccupied' => $totaloccupied['total'],
  'totalUnoccupied' => $totalUnoccupied['total']
]);
?>
