<?php
require 'header.php';


if(isset($_POST['submit'])){
    $author_name=isset($_POST['author_name']) ? $_POST['author_name'] :null;
    $author_surname=isset($_POST['author_surname']) ? $_POST['author_surname'] :null;

    if($author_name && $author_surname){
        $que= $db->prepare('INSERT INTO Author SET
        name=?,
        surname=?');
        $add=$que->execute([
            $author_name, $author_surname
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
Add Author: <br>
<hr>
Name: <br>
<input type="text" required value="<?php echo isset($_POST["author_name"])? $_POST["author_name"]: null ?>" name="author_name">
<hr>
Surname <br>
<input type="text" required value="<?php echo isset($_POST["author_surname"])? $_POST["author_surname"]: null ?>" name="author_surname">
<hr>
<input type="hidden" name="submit" value="1">
<button type="submit">Add Author</button>

</form>
