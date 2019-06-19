<?php require 'header2.php';?>
<h3>Authors</h3>

<form action="" method="post">

<input type="hidden" name="rank" value="1">
<button type="submit">Rank by SOTA count</button>

<?php
if(!isset($_POST['rank'])){
 $authors=$db->query('SELECT * FROM Author')->fetchAll(PDO::FETCH_ASSOC);
}
else{
    $authors=$db->query('SELECT * FROM Author ORDER BY (SELECT COUNT(*) FROM Topic WHERE EXISTS (SELECT * FROM Topic_In_Paper WHERE Topic_In_Paper.topic_id=Topic.topic_id AND EXISTS
    (SELECT * FROM Paper WHERE Paper.paper_id=Topic_In_Paper.paper_id AND EXISTS(SELECT * FROM Author_Wrote WHERE Author_Wrote.author_id=Author.author_id AND
        Author_Wrote.paper_id=Paper.paper_id AND Paper.result=Topic.sota)))) DESC')->fetchAll(PDO::FETCH_ASSOC);
}
?>
<?php if ($authors): ?>
    <ul>
        
        <?php foreach($authors as $author): ?>
            <li>
            <?php 
                    $quer=$db->prepare('SELECT COUNT(*) FROM Topic WHERE EXISTS (SELECT * FROM Topic_In_Paper WHERE Topic_In_Paper.topic_id=Topic.topic_id AND EXISTS
                    (SELECT * FROM Paper WHERE Paper.paper_id=Topic_In_Paper.paper_id AND EXISTS(SELECT * FROM Author_Wrote WHERE Author_Wrote.author_id=? AND
                        Author_Wrote.paper_id=Paper.paper_id AND Paper.result=Topic.sota)))');
                        $quer->execute([
                            $author['author_id']
                        ]);
                        $count=$quer->fetch(PDO::FETCH_ASSOC);

                ?>
                <?php echo $author['name']." ".$author['surname']." with SOTA COUNT: ".$count['COUNT(*)']?>
                
                <input type="button" onclick="window.location='/user.php/?page=paper_of&id=<?php echo $author['author_id'];?>'" value="See All Papers"/>
                

                <input type="button" onclick="window.location='/user.php/?page=coauthors_of&id=<?php echo $author['author_id'];?>'" value="Co-Authors"/>

                
            </li>
                    <?php endforeach; ?>
                   
    </ul>
<?php else: ?>
    <div>
        No Authors Yet!
    </div>
<?php endif; ?>

<h3>Papers</h3>
<input type="button" onclick="window.location='/user.php/?page=search_paper'" value="Search in Papers"/>
<?php

$papers=$db->query('SELECT * FROM Paper')->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if ($papers): ?>
    <ul>
        <?php foreach($papers as $paper): ?>
            <li>
                <?php echo $paper['title']?>
                <input type="button" onclick="window.location='/user.php/?page=paper_read&id=<?php echo $paper['paper_id'];?>'" value="READ"/>

                
            </li>

        <?php endforeach; ?>

    </ul>
<?php else: ?>
    <div>
        No Papers Yet!
    </div>
<?php endif; ?>

<h3>Topics</h3>
<?php

$topics=$db->query('SELECT * FROM Topic')->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if ($topics): ?>
    <ul>
        <?php foreach($topics as $topic): ?>
            <li>
                <?php echo $topic['topic_name']; 
                if($topic['sota']): ?>
                    <?php echo " SOTA: ".$topic['sota'] ?>
                    <?php 
                        $quer=$db->prepare('SELECT * FROM Paper WHERE EXISTS (SELECT * FROM Topic_In_Paper WHERE Paper.paper_id=Topic_In_Paper.paper_id AND Topic_In_Paper.topic_id=? AND Paper.result=?)');
                        $quer->execute([
                            $topic['topic_id'], $topic['sota']
                        ]);
                        $paper_sota=$quer->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <input type="button" onclick="window.location='/user.php/?page=paper_read&id=<?php echo $paper_sota['paper_id'];?>'" value="SOTA PAPER"/>
                    <input type="button" onclick="window.location='/user.php/?page=paper_topic&id=<?php echo $topic['topic_id'];?>'" value="SEE ALL PAPERS"/>

                    <?php endif; ?>

                
                
            </li>

        <?php endforeach; ?>

    </ul>
<?php else: ?>
    <div>
        No Topics Yet!
    </div>
<?php endif; ?>
</form>

