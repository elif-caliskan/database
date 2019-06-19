<?php
require 'header.php';


if(isset($_POST['submit'])){
    $topic_name=isset($_POST['topic_name']) ? $_POST['topic_name'] :null;

    if($topic_name){
        $que= $db->prepare('INSERT INTO Topic SET
        topic_name=?');
        $add=$que->execute([
            $topic_name
        ]);
        if($add){
            header('Location:admin.php');
        }
        else{
            print_r($que->errorInfo());
        }

    }
}


?>

<form action="" method="post">
Add Topic: <br>
<hr>
Name: <br>
<input type="text" required value="<?php echo isset($_POST["topic_name"])? $_POST["topic_name"]: null ?>" name="topic_name">
<hr>
<input type="hidden" name="submit" value="1">
<button type="submit">Add Topic</button>

</form>
