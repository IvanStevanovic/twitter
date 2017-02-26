<?php
require "twitteroauth/autoload.php";

use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter{
    
    private function connectdb(){
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "tweetsdb";
        $conn = mysqli_connect($servername,$username,$password,$dbname);
        return $conn;
    }
    
    //connect to twitter api
    public function twitterConeection(){
        $consumer_key = "t3d83hZCU1UMX6qVL2m7DMak3";
        $consumer_secret = "YB1wEWsQOxlq6V49U7jaDLUP7zvB2ebU45tzVeY8c076pgBHhN";
        $access_token = "763790480006115332-qqhP81UfTsCB4jUo4MLO8pDkAbm2jw4";
        $access_token_secret="WJrP7klIKqVTRWbaPsV6PNUbe6JrOo5hIPQBCZFwQfT2x";
        $connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
        return $connection;
    }
    
    //get all tweets using api to fill database
    public function getTweets(){
        $connect =$this->twitterConeection();
        //get only tweets, without replies and retweets.
        $tweets = $connect->get('statuses/user_timeline',['count'=>200, 'exclude_replies'=>true, 'screen_name'=>'b92vesti','include_rts'=>false]);
        $page=0;
        $tweets_total[$page]=$tweets;
        for($count=200;$count<1000;$count+=200){
            $max = count($tweets_total[$page])-1;
            $tweets = $connect->get('statuses/user_timeline',['count'=>200,'exclude_replies'=>true, 'max_id'=>$tweets_total[$page][$max]->id_str,'screen_name'=>'b92vesti','include_rts'=>false]);
            if(count($tweets)==1){
                break;
            }
            $page++;
            $tweets_total[$page]=$tweets;        
        }
        return $tweets_total;
    }
    
    //get latest 50 tweets for cron job to update database every 15 mins(recommend).
    public function getLatestTweets(){
        $connect =$this->twitterConeection();
        //get only tweets, without replies and retweets.
        $tweets = $connect->get('statuses/user_timeline',['count'=>50, 'exclude_replies'=>true, 'screen_name'=>'b92vesti','include_rts'=>false]);
        //print_r($tweets);
        return $tweets;
    }
    
    public function stripTitle($title){
        $fixedTitle = explode("https",$title);
        return $fixedTitle[0];
    }
    
    public function customDate($date_string){
        $customDate = strtotime($date_string);
        return $customDate;
    }
    
    public function customDateAgo($date){
        $agoTime = time() - $date;
        if($agoTime <60){
            return 'now';
        }
        //if less then 24h
        if($agoTime < 60 * 60 * 24){
            $formats = array(60 * 60  => 'h', 60 => 'm');
            foreach($formats as $f => $str){
                $diff = $agoTime/$f;
                if($diff>=1){
                    $r = round($diff);
                    return $r." $str";
                }
            }
        }
        else {
            $formated_date = date('j M',$date);
            return $formated_date;
        }
    }
        
    //fill database for 1st time
    public function fillDataBase(){
        $conn = $this->connectdb();
        $tweets_total = $this->getTweets();
        foreach ($tweets_total as $page){
            foreach($page as $key){
                $title = $this->stripTitle($key->text);
                $prepared_title = mysqli_real_escape_string($conn,$title);
                if(!empty($key->entities->urls)){
                    $url_source = $key->entities->urls[0]->url;                   
                    $url_dispaly_source = $key->entities->urls[0]->display_url;
                }
                else{
                    $url_source=".";
                    $url_dispaly_source = ".";
                }
                $tweet_url = $key->id;
                $tweet_date = $this->customDate($key->created_at);
                $user_name = $key->user->name;
                $user_screen_name= $key->user->screen_name;
                $user_image_src= $key->user->profile_image_url;
                $sql="INSERT INTO tweets (title, url_source, tweet_url_id, created_date, url_display_source, user_name, user_screen_name, user_logo) "
                . "VALUES('$prepared_title','$url_source','$tweet_url','$tweet_date','$url_dispaly_source','$user_name','$user_screen_name','$user_image_src')";
                mysqli_query($conn, $sql);
            }
        }
    }
    
    public function getAllDatas(){
        $conn = $this->connectdb();
        $sql1 = "SELECT * FROM tweets";
        $rez = mysqli_query($conn,$sql1);
        $all_datas=array();
        while($grup = mysqli_fetch_assoc($rez)){
            $all_datas[]=$grup;
        }
        return $all_datas;
    }
    
    public function getDatasByPages($limit){
        $conn = $this->connectdb();
        $sql = "SELECT * FROM tweets ORDER BY created_date DESC $limit";
        $res = mysqli_query($conn,$sql);
        $datas = array();
        if(!empty($res)){
        while($grup = mysqli_fetch_assoc($res)){
            $datas[]=$grup;
        }
        }
        return $datas;
    } 
    //cron function for update databes, set cron job  on every 15 minutes(recommend).
    public function cornUpdateDb(){
        $conn = $this->connectdb();
        $twitter_data = $this->getLatestTweets();
        foreach($twitter_data as $key){
            $sql = "SELECT * FROM tweets WHERE tweet_url_id = $key->id ";
            $res = mysqli_query($conn, $sql);
            if(mysqli_num_rows($res)== 0){
                $title = $this->stripTitle($key->text);
                $prepared_title = mysqli_real_escape_string($conn,$title);
                if(!empty($key->entities->urls)){
                    $url_source = $key->entities->urls[0]->url;
                    $url_dispaly_source = $key->entities->urls[0]->display_url;
                }
                else{
                    $url_source=".";
                    $url_dispaly_source=".";
                }
                $tweet_url = $key->id;
                $tweet_date = $this->customDate($key->created_at);
                $user_name = $key->user->name;
                $user_screen_name= $key->user->screen_name;
                $user_image_src= $key->user->profile_image_url;
                $sql="INSERT INTO tweets (title, url_source, tweet_url_id, created_date, url_display_source, user_name, user_screen_name, user_logo)"
                . "VALUES('$prepared_title','$url_source','$tweet_url','$tweet_date','$url_dispaly_source','$user_name','$user_screen_name','$user_image_src')";
                mysqli_query($conn, $sql); 
                echo "New Tweet has been inserted with id: $tweet_url<br>";
            }
            else{
                echo "Last tweet before update has id: $key->id";
                break;
            }
        }
    }
}