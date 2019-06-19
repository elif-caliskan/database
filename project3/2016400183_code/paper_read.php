<?php
require 'header.php';
if(!isset($_GET['id']) || empty($_GET['id'])){
    header('Location:/admin.php');
    exit;
}
$quer=$db->prepare('SELECT * FROM Paper WHERE paper_id=?');
$quer->execute([
    $_GET['id']
]);
$paper=$quer->fetch(PDO::FETCH_ASSOC);

    $que=$db->prepare('SELECT DISTINCT * FROM Author WHERE EXISTS (SELECT author_id FROM Author_Wrote WHERE Author_Wrote.author_id=Author.author_id AND Author_Wrote.paper_id=paper_id AND paper_id=?)');
    $que->execute([$_GET['id']
    ]);
    $authors=$que->fetchAll(PDO::FETCH_ASSOC);
    $que_topic=$db->prepare('SELECT DISTINCT * FROM Topic WHERE EXISTS (SELECT topic_id FROM Topic_In_Paper WHERE Topic_In_Paper.topic_id=Topic.topic_id AND Topic_In_Paper.paper_id=paper_id AND paper_id=?)');
    $que_topic->execute([$_GET['id']]);
    $topics=$que_topic->fetchAll(PDO::FETCH_ASSOC);


?>

<h1><?php echo $paper['title']?></h1>
<hr>
<h2>Author(s):</h2>
<?php foreach($authors as $author): ?>
    <li>
    <?php echo $author['name'];
    echo " ";
    echo $author['surname']?>
    </li>
<?php endforeach ?>
<hr>
<h2>Abstract:</h2>
<h1><?php echo $paper['abstract']?></h1>
<hr>
<h2>Result:</h2>
<h1><?php echo $paper['result']?></h1>
<hr>
<h2>Topic(s):</h2>
<?php foreach($topics as $topic): ?>
    <li>
    <?php echo $topic['topic_name']; 
    if($topic['sota']){
        echo ' with SOTA: ';
        echo $topic['sota'];
    }
    ?>
    </li>
<?php endforeach ?>



