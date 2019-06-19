<?php
require 'header.php';
if(!isset($_GET['id']) || empty($_GET['id'])){
    header('Location:/user.php');
    exit;
}
$quer=$db->prepare('SELECT * FROM Paper WHERE EXISTS (SELECT paper_id FROM Topic_In_Paper WHERE Paper.paper_id=Topic_In_Paper.paper_id AND Topic_In_Paper.topic_id=?)');
$quer->execute([
    $_GET['id']
]);
$papers=$quer->fetchAll(PDO::FETCH_ASSOC);

$quer_topic=$db->prepare('SELECT * FROM Topic WHERE Topic.topic_id=?');
$quer_topic->execute([
    $_GET['id']
]);
$topic=$quer_topic->fetch(PDO::FETCH_ASSOC);
?>


<h3>Papers with Topic: <?php echo $topic['topic_name']; ?></h3>

<?php if ($papers): ?>
    <ul>
        <?php foreach($papers as $paper): ?>
            <li>
                <?php echo $paper['title']?>
                <a href="/admin.php/?page=paper_read&id=<?php echo $paper['paper_id'];?>">[READ]</a>
            </li>

        <?php endforeach; ?>

    </ul>
<?php else: ?>
    <div>
        No Papers Yet!
    </div>
<?php endif; ?>