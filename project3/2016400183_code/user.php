<?php
    require_once 'baglan.php';

if(!isset($_GET['page'])){
    $_GET['page']= 'index';
}

Switch ($_GET['page']){
    case 'index':
        require_once 'user_home.php';
    break;
    case 'paper_read':
        require_once 'user_read.php';
    break;
    case 'paper_of':
        require_once 'paper_of.php';
    break;
    case 'paper_topic':
        require_once 'paper_topic.php';
    break;
    case 'search_paper':
        require_once 'search_paper.php';
    break;
    case 'coauthors_of':
        require_once 'coauthor.php';
    break;
}
?>