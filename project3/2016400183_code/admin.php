<?php
    
    ob_start();
    require_once 'baglan.php';



if(!isset($_GET['page'])){
    $_GET['page']= 'index';
}

Switch ($_GET['page']){
    case 'index':
        require_once 'admin_home.php';
    break;
    case 'insert':
        require_once 'author_insert.php';
    break;
    case 'update':
        require_once 'author_update.php';
    break;
    case 'delete':
        require_once 'author_delete.php';
    break;
    case 'paper_insert':
        require_once 'paper_insert.php';
    break;
    case 'topic_insert':
        require_once 'topic_insert.php';
    break;
    case 'topic_update':
        require_once 'topic_update.php';
    break;
    case 'topic_delete':
        require_once 'topic_delete.php';
    break;
    case 'paper_update':
        require_once 'paper_update.php';
    break;
    case 'paper_delete':
        require_once 'paper_delete.php';
    break;
    case 'paper_read':
        require_once 'paper_read.php';
    break;
    case 'search_paper':
        require_once 'admin_search.php';
    break;
    case 'paper_topic':
        require_once 'admin_topic.php';
    break;
    case 'coauthors_of':
        require_once 'admin_coauth.php';
    break;
    case 'paper_of':
        require_once 'admin_paper_of.php';
    break;
}
?>