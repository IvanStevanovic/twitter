<?php
require_once("Twitter.php");
//Pagination section
$twitter = new Twitter();
$count = count($twitter->getAllDatas());
if(isset($_GET['page'])){
    $page = preg_replace("#[^0-9]#","",$_GET['page']);
}
else{
    $page = 1;
}
$perPage = 20;
$lastPage = ceil($count/$perPage);
if($page<1){
    $page = 1;
}
if($page>$lastPage){
    $page = $lastPage;
}
$limit = "LIMIT ".($page-1)*$perPage.",$perPage";
$paginationNext="";
$paginationPrev="";
if($lastPage!=1){
    if($page!=$lastPage){
        $next = $page+1;
        $paginationNext = '<a href="index.php?page='.$next.'">Next</a>';
    }
    if($page != 1){
        $prev = $page-1;
        $paginationPrev = '<a href="index.php?page='.$prev.'">Prev</a>';
    }
}
$pageResponse = $twitter->getDatasByPages($limit);
//end of pagination section
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Tweets B92</title>
  <meta charset="utf-8">
  <link rel="stylesheet" type="text/css" href="style.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <h2><a href="https://twitter.com/b92vesti" target="_blank">B92 Vesti Tweets</a></h2>
<?php
    foreach($pageResponse as $pageR){
        if($pageR['url_source']=="."){
            $url_source = "";
        }
        else{
            $url_source = '<h4><a href="'.$pageR['url_source'].'" target="_blank">'.$pageR['url_display_source'].'</a></h4>';
        }
        echo '<div class="tweet">'
                . '<a href="https://www.twitter.com/'.$pageR['user_screen_name'].'" target="_blank"><img src="'.$pageR['user_logo'].'">'
                . '<span class="dname">'.$pageR['user_name'].'</span>'
                . '<span class="sname"> @'.$pageR['user_screen_name'].'</span></a>'
                . '<span> - </span><a href="https://www.twitter.com/'.$pageR['user_screen_name'].'/status/'.$pageR['tweet_url_id'].'" target="_blank">'
                . '<span class="time">'.$twitter->customDateAgo($pageR['created_date']).'</span></a>'
                . '<h3>'.$pageR['title'].'</h3><br>'
                . $url_source
                . '<br><a href="https://www.twitter.com/'.$pageR['user_screen_name'].'/status/'.$pageR['tweet_url_id'].'" target="_blank">'
                . '<button type="button" class="btn btn-primary">See on Twitter</button></a></div>';
    }
    echo '<ul class="pager"><li>'.$paginationPrev.'</li><li>'.$paginationNext.'</li></ul>';
?>
</div>
    
</body>
</html>



