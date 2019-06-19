<?php
require 'header.php';


if(isset($_POST['search'])){
    $str=isset($_POST['search']) ? $_POST['search'] :null;

        $que= $db->prepare('SELECT * FROM Paper WHERE title LIKE ? OR abstract LIKE ?');
        $papers=$que->execute([
            "%".$str."%", "%".$str."%"
        ]);
        $papers=$que->fetchAll(PDO::FETCH_ASSOC);
        print_r($papers);
    }
   


?>

<form action="" method="post">
<h2>Search In Papers: </h2>
<hr>
Search: <br>
<input type="text" required value="<?php echo isset($_POST["search"])? $_POST["search"]: null ?>" name="search">
<hr>

<input type="hidden" name="submit" value="1">
<button type="submit">Search</button>

<h3>Papers</h3>

<?php if((isset($_POST['search']) && $papers)): ?>
    <ul>
        <?php foreach($papers as $paper): ?>
            <li>
                <?php echo $paper['title']?>
                <a href="/admin.php/?page=paper_read&id=<?php echo $paper['paper_id'];?>">[READ]</a>
            </li>

        <?php endforeach; ?>

    </ul>
<?php elseif(isset($_POST['search'])): ?>
    <div>
        No Papers which contains " <?php echo $_POST['search'] ?>" in it!
    </div>
<?php else: ?>
    <div>
        Please fill in the search bar!
    </div>

<?php endif; ?>

</form>
