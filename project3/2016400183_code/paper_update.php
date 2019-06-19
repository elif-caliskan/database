<?php

//yapılacaklar: SOTA için trigger eklemek her paper delete update insert olduktan sonra sotayı yenile sonra da requirementlar
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

if(!$paper){
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
$authors_in_paper=$que->fetchAll(PDO::FETCH_ASSOC);
$que_topic=$db->prepare('SELECT DISTINCT * FROM Topic WHERE EXISTS (SELECT topic_id FROM Topic_In_Paper WHERE Topic_In_Paper.topic_id=Topic.topic_id AND Topic_In_Paper.paper_id=paper_id AND paper_id=?)');
$que_topic->execute([$_GET['id']]);
$topics_in_paper=$que_topic->fetchAll(PDO::FETCH_ASSOC);
$author_ids=array_map(function($auth){
    return $auth['author_id'];
}, $authors_in_paper );
$topic_ids=array_map(function($top){
    return $top['topic_id'];
}, $topics_in_paper );


if(isset($_POST['submit'])){
    $paper_title=isset($_POST['paper_title']) ? $_POST['paper_title'] :null;
    $paper_abstract=isset($_POST['paper_abstract']) ? $_POST['paper_abstract'] :null;
    $paper_result=isset($_POST['paper_result']) ? $_POST['paper_result'] :null;
    $authors_post=isset($_POST['selected_authors']) ? $_POST['selected_authors'] :null;
    $topics_post=isset($_POST['selected_topics']) ? $_POST['selected_topics'] :null;
    
    if($paper_title && $paper_abstract && $paper_result!=null && $authors_post && $topics_post){
        $que= $db->prepare('UPDATE Paper SET
        title=?,
        abstract=?,
        result=?
        WHERE paper_id=?' );
        $update=$que->execute([
            $paper_title, $paper_abstract, $paper_result, $paper['paper_id']
        ]);
        if($update){
            foreach($authors_post as $authorId):
                if(!in_array($authorId, $author_ids)){
                
                $que_auth= $db->prepare('INSERT INTO Author_Wrote SET
                author_id=?,
                paper_id=?');
                $add_author=$que_auth->execute([
                $authorId, $paper['paper_id']]);
                }
                endforeach;
                
            foreach($author_ids as $authorId):
                if(!in_array($authorId, $authors_post)){
                    
                    $que_auth= $db->prepare('DELETE FROM Author_Wrote WHERE
                    author_id=? AND
                    paper_id=?');
                    $add_author=$que_auth->execute([
                    $authorId, $paper['paper_id']]);
                }
            endforeach;
            foreach($topics_post as $topicId):
                if(!in_array($topicId,$topic_ids)){
                   
                $que_topic= $db->prepare('INSERT INTO Topic_In_Paper SET
                topic_id=?,
                paper_id=?');
                $add_topic=$que_topic->execute([
                $topicId, $paper['paper_id']]);
                }
            endforeach;
            foreach($topic_ids as $topicId):
                if(!in_array($topicId,$topics_post)){
                    
                $que_topic= $db->prepare('DELETE FROM Topic_In_Paper WHERE
                topic_id=? AND
                paper_id=?');
                $add_topic=$que_topic->execute([
                $topicId, $paper['paper_id']]);
                }
            endforeach;
            header('Location:/admin.php');

        }
        else{
            print_r($que->errorInfo());
        }


    }
    else{
        echo 'Please fill every content!!';
    }

}




?>

<h1>Update Paper: </h1>
<hr>
<h2>Authors:</h2>
<form action="" method="post">
<?php $authors=$db->query('SELECT * FROM Author')->fetchAll(PDO::FETCH_ASSOC);?>

    <ul>
        <label>
        <?php if(isset($_POST["paper_title"])): ?>
        <?php foreach($authors as $author): ?>
            <input type="checkbox" <?php echo (isset($_POST["selected_authors"]) && in_array($author["author_id"],$_POST["selected_authors"])) ? "checked":null ?> 
            name="selected_authors[]" value="<?php echo $author['author_id'];?>"><?php echo $author['name'];
                echo " ";
                echo $author['surname']?>
                <br>
            <?php endforeach; ?>  
            <?php else: ?>
        
        <?php foreach($authors as $author): ?>
            <input type="checkbox" <?php echo in_array($author["author_id"],$author_ids) ? "checked": null ?> name="selected_authors[]" value="<?php echo $author['author_id'];?>"><?php echo $author['name'];
                echo " ";
                echo $author['surname']?>
                <br>
            <?php endforeach; ?>  
<?php endif; ?>
        </label>

    </ul>



<br>
<hr>
<h2>Title</h2>
<input type="text" required value="<?php echo isset($_POST["paper_title"])? $_POST["paper_title"]: $paper['title'] ?>" name="paper_title">
<hr>
<h2>Abstract:</h2>
<textarea name="paper_abstract" cols="30" required rows="10"><?php echo isset($_POST["paper_abstract"])? $_POST["paper_abstract"]: $paper['abstract'] ?></textarea>
<hr>
<h2>Result:</h2>
<input type="INT" required value="<?php echo isset($_POST["paper_result"])? $_POST["paper_result"]: $paper['result'] ?>" name="paper_result">
<hr>
<h2>Topics:</h2>
<form action="" method="post">
<?php $topics=$db->query('SELECT * FROM Topic')->fetchAll(PDO::FETCH_ASSOC);?>

    <ul>
    <label>
        <?php if(isset($_POST["paper_title"])): ?>
        <?php foreach($topics as $topic): ?>
            <input type="checkbox" <?php echo (isset($_POST["selected_topics"]) && in_array($topic["topic_id"],$_POST["selected_topics"])) ? "checked":null ?> 
            name="selected_topics[]" value="<?php echo $topic['topic_id'];?>"><?php echo $topic['topic_name'] ?>
                <br>
            <?php endforeach; ?>  
            <?php else: ?>
        
        <?php foreach($topics as $topic): ?>
            <input type="checkbox" <?php echo in_array($topic["topic_id"],$topic_ids) ? "checked": null ?> name="selected_topics[]" value="<?php echo $topic['topic_id'];?>"><?php echo $topic['topic_name']?>
                <br>
            <?php endforeach; ?>  
<?php endif; ?>
        </label>
    </ul>

<input type="hidden" name="submit" value="1">
<button type="submit">Update Paper</button>

</form>
