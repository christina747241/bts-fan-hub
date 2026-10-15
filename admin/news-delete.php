<?php
require_once '../includes/admin-auth.php';
require_once '../config/database.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: index.php');
    exit;
}

$submitToken = $_POST['csrf_token'] ?? '';

if(
    !is_string($submitToken) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $submitToken)
){
    header('Location: index.php?error=csrf');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if($id === false || $id === null || $id < 1){
    header('Location: index.php?error=invalid_id');
    exit;
}

$stmt = $pdo->prepare(
    "DELETE FROM news WHERE id = ?"
);

$stmt->execute([$id]);

if($stmt->rowCount() > 0){
    header('Location: index.php?deleted=1');
    exit;
}

header('Location: index.php?error=not_found');
exit;

?>