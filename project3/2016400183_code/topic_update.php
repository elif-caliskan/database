<?php
require 'header.php';


if(!isset($_GET['id']) || empty($_GET['id'])){
    header('Location:/admin.php');
    exit;
}
$quer=$db->prepare('SELECT * FROM Topic WHERE topic_id=?');
$quer->execute([
    $_GET['id']
]);
$topic=$quer->fetch(PDO::FETCH_ASSOC);

if(!$topic){
    header('Location:/admin.php');
    exit;
}
if(isset($_POST['submit'])){
    $topic_name=isset($_POST['topic_name']) ? $_POST['topic_name'] :$topic['name'];

    if($topic_name ){

        $que=$db->prepare('UPDATE Topic SET topic_name = ? WHERE topic_id = ?');
        $update=$que->execute([
        $topic_name, $topic['topic_id']
    
        ]);
    }
if($update){
    header('Location:/admin.php');
}
else{
    echo "update failed";
}
}


?>
<form action="" method="post">
Update Topic: <br>
<hr>
Name: <br>
<input type="text" required value="<?php echo isset($_POST["topic_name"])? $_POST["topic_name"]: $topic['topic_name'] ?>" name="topic_name">
<hr>

<input type="hidden" name="submit" value="1">
<button type="submit">Update</button>

</form>
