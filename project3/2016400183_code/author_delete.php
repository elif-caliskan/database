<?php

if(!isset($_GET['id']) || empty($_GET['id'])){
    header('Location:/admin.php');
    exit;
}
$que=$db->prepare('DELETE FROM Author WHERE author_id = ?');
$que->execute([$_GET['id']]);
header('Location:/admin.php');


?>