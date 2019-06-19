<?php
require 'header.php';
if(!isset($_GET['id']) || empty($_GET['id'])){
    header('Location:/admin.php');
    exit;
}
$quer=$db->prepare('SELECT * FROM Author WHERE author_id=?');
$quer->execute([
    $_GET['id']
]);
$author_base=$quer->fetch(PDO::FETCH_ASSOC);
$name=$author_base['name'];
$surname=$author_base['surname'];

    $que=$db->query("CALL FindCoauthors('$name','$surname')");
    $authors=$que->fetchAll(PDO::FETCH_ASSOC);
    


?>

<h1>Coauthors of <?php echo $author_base['name']." ".$author_base['surname']?></h1>
<hr>
<?php if($authors): ?>
<?php foreach($authors as $author): ?>
    <li>
    <?php echo $author['name'];
    echo " ";
    echo $author['surname']?>
    </li>
<?php endforeach ?>
<?php else: ?>
    <div>
    There is no coauthor of this person!
    </div>
<?php endif;?>

<hr>




