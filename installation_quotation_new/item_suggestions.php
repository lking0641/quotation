// item_suggestions.php
<?php
include '../config/db_conn.php';
$q = $conn->real_escape_string($_GET['query'] ?? '');
$suggestions = [];
if ($q !== '') {
  $stmt = $conn->prepare("SELECT item_code, item_name 
      FROM items 
      WHERE item_code LIKE CONCAT('%', ?, '%') 
         OR item_name LIKE CONCAT('%', ?, '%') 
      LIMIT 5");
  $stmt->bind_param('ss', $q, $q);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $suggestions[] = "{$row['item_code']} - {$row['item_name']}";
  }
}
header('Content-Type: application/json');
echo json_encode($suggestions);
