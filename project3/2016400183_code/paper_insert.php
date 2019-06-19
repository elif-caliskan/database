<?php
require 'header.php';


if(isset($_POST['submit'])){
    $paper_title=isset($_POST['paper_title']) ? $_POST['paper_title'] :null;
    $paper_abstract=isset($_POST['paper_abstract']) ? $_POST['paper_abstract'] :null;
    $result=isset($_POST['paper_result']) ? $_POST['paper_result'] :null;
    $authors_post=isset($_POST['selected_authors']) ? $_POST['selected_authors'] :null;
    $topics_post=isset($_POST['selected_topics']) ? $_POST['selected_topics'] :null;

    if($paper_title && $paper_abstract && $result && $authors_post && $topics_post){
        $que= $db->prepare('INSERT INTO Paper SET
        title=?,
        abstract=?,
        result=?');
        $add=$que->execute([
            $paper_title, $paper_abstract, $result
        ]);
        if($add){
            $lastId= $db->lastInsertId();
            foreach($authors_post as $authorId):

                $que_auth= $db->prepare('INSERT INTO Author_Wrote SET
                author_id=?,
                paper_id=?');
                $add_author=$que_auth->execute([
                $authorId, $lastId]);
                endforeach;
            foreach($topics_post as $topicId):

                $que_topic= $db->prepare('INSERT INTO Topic_In_Paper SET
                topic_id=?,
                paper_id=?');
                $add_topic=$que_topic->execute([
                $topicId, $lastId]);
                
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

//int kısmından emin değilim bir bak!!!!!!!!!!!!!
?>
<h1>Add Paper: </h1>
<hr>
<h2>Authors:</h2>
<form action="" method="post">
<?php $authors=$db->query('SELECT * FROM Author')->fetchAll(PDO::FETCH_ASSOC);?>
<?php if ($authors): ?>
    <ul>
        <label>
        <?php foreach($authors as $author): ?>
            <input type="checkbox" <?php echo (isset($_POST["selected_authors"])&& in_array($author["author_id"],$_POST["selected_authors"]))?"checked":null?> name="selected_authors[]" value="<?php echo $author['author_id'];?>"><?php echo $author['name'];
                echo " ";
                echo $author['surname']?>

            
            <br>

        <?php endforeach; ?>
        </label>

    </ul>
<?php else: ?>
    <div>
        No Authors Yet!
    </div>
<?php endif; ?>


<br>
<hr>
<h2>Title</h2>
<input type="text" required value="<?php echo isset($_POST["paper_title"])? $_POST["paper_title"]: null ?>" name="paper_title">
<hr>
<h2>Abstract:</h2>
<textarea name="paper_abstract" cols="30" required rows="10"><?php echo isset($_POST["paper_abstract"])? $_POST["paper_abstract"]: null ?></textarea>
<hr>
<h2>Result:</h2>
<input type="INT" required value="<?php echo isset($_POST["paper_result"])? $_POST["paper_result"]: null ?>" name="paper_result">
<hr>
<h2>Topics:</h2>
<form action="" method="post">
<?php $topics=$db->query('SELECT * FROM Topic')->fetchAll(PDO::FETCH_ASSOC);?>
<?php if ($topics): ?>
    <ul>
        <label>
        <?php foreach($topics as $topic): ?>
            <input type="checkbox" <?php echo (isset($_POST["selected_topics"])&& in_array($topic["topic_id"],$_POST["selected_topics"]))?"checked":null?> name="selected_topics[]" value="<?php echo $topic['topic_id'];?>"><?php echo $topic['topic_name'];?>
            <br>

        <?php endforeach; ?>
        </label>

    </ul>
<?php else: ?>
    <div>
        No Topics Yet!
    </div>
<?php endif; ?>

<input type="hidden" name="submit" value="1">
<button type="submit">Add Paper</button>

</form>
