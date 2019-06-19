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
$author=$quer->fetch(PDO::FETCH_ASSOC);

if(!$author){
    header('Location:/admin.php');
    exit;
}
if(isset($_POST['submit'])){
    $author_name=isset($_POST['author_name']) ? $_POST['author_name'] :$author['name'];
    $author_surname=isset($_POST['author_surname']) ? $_POST['author_surname'] :$author['surname'];

    if($author_name && $author_surname){

        $que=$db->prepare('UPDATE Author SET name = ?,surname = ? WHERE author_id = ?');
        $update=$que->execute([
        $author_name,$author_surname, $author['author_id']
    
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
Update Author: <br>
<hr>
Name: <br>
<input type="text" required value="<?php echo isset($_POST["author_name"])? $_POST["author_name"]: $author['name'] ?>" name="author_name">
<hr>
Surname <br>
<input type="text" required value="<?php echo isset($_POST["author_surname"])? $_POST["author_surname"]: $author['surname'] ?>" name="author_surname">
<hr>
<input type="hidden" name="submit" value="1">
<button type="submit">Update</button>

</form>
