<?php
require 'header2.php';
if(!isset($_GET['id']) || empty($_GET['id'])){
    header('Location:/user.php');
    exit;
}
$quer=$db->prepare('SELECT * FROM Paper WHERE EXISTS (SELECT paper_id FROM Author_Wrote WHERE Paper.paper_id=Author_Wrote.paper_id AND Author_Wrote.author_id=?)');
$quer->execute([
    $_GET['id']
]);
$papers=$quer->fetchAll(PDO::FETCH_ASSOC);

$quer_auth=$db->prepare('SELECT * FROM Author WHERE Author.author_id=?');
$quer_auth->execute([
    $_GET['id']
]);
$author=$quer_auth->fetch(PDO::FETCH_ASSOC);
?>


<h3>Papers by <?php echo $author['name']." ".$author['surname']; ?></h3>

<?php if ($papers): ?>
    <ul>
        <?php foreach($papers as $paper): ?>
            <li>
                <?php echo $paper['title']?>
                <a href="/user.php/?page=paper_read&id=<?php echo $paper['paper_id'];?>">[READ]</a>
            </li>

        <?php endforeach; ?>

    </ul>
<?php else: ?>
    <div>
        No Papers Yet!
    </div>
<?php endif; ?>