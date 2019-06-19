<?php

if(!isset($_GET['id']) || empty($_GET['id'])){
    header('Location:/admin.php');
    exit;
}
$que=$db->prepare('DELETE FROM Topic WHERE topic_id = ?');
$que->execute([$_GET['id']]);
header('Location:/admin.php');


?>