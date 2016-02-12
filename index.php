<?php
    session_start();
    include('dbconnect.php');
    include('function.php');
    
    if (!empty($_POST)) {
        if ($_POST["user"] == "") {
            $error["user"] = "Blanck"; 
        } elseif (strlen($_POST['user']) > 20 ){
            $error["user"] = "long";
        }
        if ($_POST["password"] == "") {
            $error["password"] = "Blanck";
        } elseif (strlen($_POST['password']) < 4) {
            $error["password"] = "short";
        } elseif (strlen($_POST['password']) > 8) {
            $error["password"] = "long";
        } elseif (preg_match('/^[a-zA-Z0-9]+$/', $_POST['password'])) {
            if (ctype_lower($_POST['password']) == true){
                $error["password"] = "allsmall";
            } elseif (ctype_upper($_POST['password']) == true){
                $error["password"] = "allbig";
            }
        } else {
            $error["password"] = "notalphabet";          
        }
        if ($_POST["post"] == "") {
            $error["post"] = "Blanck"; 
        } elseif (strlen($_POST['post']) > 400) {
            $error["post"] = "long";
        }         

        if (empty($error)) {
            $sql = sprintf('INSERT INTO posts SET user="%s", password="%s", flag=0, post="%s",created=NOW(),modified=NOW()',
                mysqli_real_escape_string($db, $_POST["user"]),
                mysqli_real_escape_string($db, $_POST["password"]),
                mysqli_real_escape_string($db, $_POST["post"])    
            );
            mysqli_query($db, $sql) or die (mysqli_error($db));
            $_SESSION['user'] = $_POST["user"];
            $posted = "on";
        }   
    }
    $sql = 'SELECT * FROM posts WHERE flag = 0 ORDER BY modified DESC';
    $posts = mysqli_query($db, $sql) or die (mysqli_error($db));

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title>簡易掲示板</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/index.css">

    <script type="text/javascript" src="assets/js/index.js"></script>

  </head>
  <body><!-- 入力フォーム -->
    <div class="span3 well">
      <legend>あなたのきもちを投稿しよう</legend>
      <form accept-charset="UTF-8" action="" method="post">
        <div class="container">
          <div class="row">
            <label>ニックネーム[20文字以内]</label>
            <?php
                if(!empty($_POST)) { 
                    if (isset($error["user"])) {
                        if($error["user"] == "Blanck") {
                            echo '<input class="span3" name="user" type="text">';
                            echo '<p class="text-danger">必須項目です</p>';
                        } elseif ($error["user"] == "long") {
                            echo '<input class="span3" name="user" type="text">';
                            echo '<p class="text-danger">ニックネームは20文字以内でご入力ください</p>';
                        } 
                    } else {
                        echo sprintf('<input class="span3" name="user" type="text" value="%s">',
                            h($_POST["user"])
                        );        
                    }
                } else {
                    if (isset($_SESSION["user"])) {
                        echo sprintf('<input class="span3" name="user" value="%s" type="text">',h($_SESSION["user"]));
                    } else {
                        echo '<input class="span3 col-md-12" name="user" placeholder="例) ネクシ太郎" type="text">';
                    }
                }   
            ?>
          </div>
          <div class="row">
            <label >パスワード[半角英数/大文字小文字混在で4~8文字]</label>
            <?php
                if (isset($_POST["password"]) && isset($error)) {
                    if (isset($error["password"])) {
                        if($error["password"] == "Blanck") {
                            echo '<input class="span3" name="password" type="password">';
                            echo '<p class="text-danger">パスワードを入力してください</p>';
                        } elseif($error["password"] == "short" || $error['password'] == "long") {
                            echo '<input class="span3" name="password" type="password">';
                            echo '<p class="text-danger">パスワードの文字数は4~8文字で設定ください</p>';
                        } elseif($error["password"] == "notalphabet" || $error["password"] == "allsmall" || $error["password"] == "allbig") {
                            echo '<input class="span3" name="password" type="password">';
                            echo '<p class="text-danger">半角英数のみ/大文字小文字をまぜて設定してください</p>';
                        } 
                    } else {
                        echo '<input class="span3" name="password" type="password">';
                    }
                 } else {
                   echo '<input class="span3 " name="password" type="password" placeholder="例) hjKijjO">';
                 }
            ?>  
          </div>
        </div>      
        <div class="container">
          <div class="row">
            <div class="col-md-10" style="padding-left: 0px;">
              <div class="panel panel-primary">
                <div class="panel-heading">  
                  <p>つぶやきを書き込もう!</p>          
                </div> 
                <div class="panel-body ">
                    <?php
                        if (isset($_POST["post"]) && isset($error)) {               
                            if (isset($error["post"])) {
                                if ($error["post"] == "Blanck"){
                                    echo '<input class="span3 col-xs-12" name="post" type="text" placeholder="ここにコメントを書き込みます">';
                                    echo '<p class="text-danger">つぶやきを投稿してください</p>';
                                } elseif ($error["post"] == "long") {
                                    echo sprintf('<input class="span3 col-xs-12" name="post" value="%s" type="textarea">',
                                        h($_POST["post"])
                                    );
                                    echo '<p class="text-danger">400文字以内で投稿してください</p>';
                                }   
                            } else {
                                echo sprintf('<input class="span3 col-xs-12" type="textarea" name="post" value="%s">',
                                    h($_POST["post"])
                                );
                            }                 
                        } else {
                            echo '<input class="span3 col-xs-12" name="post" type="textarea" placeholder="ここにつぶやきを書き込みます">';
                        }
                    ?>
                </div>        
              </div>
              <button class="btn btn-warning col-xs-6 " type="submit">投稿する</button>
            </div>
          </div>
        </div>
      </form>
    </div>
    <?php if(isset($posted)):?>
      <div class="container">
        <div class="row">
          <div class="col-xs-6 bg-warning">
            <p class="text-warning">投稿が完了しました。</hp>
          </div>
        </div>
    <?php endif;?>
    <!-- タイムライン -->
    <?php while($post = mysqli_fetch_assoc($posts)): ?> 
      <div class="container">
        <div class="qa-message-list" id="wallmessages">
          <div class="message-item" id="m16">
            <div class="message-inner">
              <div class="message-head clearfix">
                <div class="message-icon pull-left"><i class="glyphicon glyphicon-check"></i></div>
                <div class="user-detail">
                  <h5 class="handle"><?php echo h($post['user']); ?>さん</h5>
                  <div class="post-type">
                    <?php echo h($post['post']);?>      
                  </div>
                </div>
              </div>
              <div class="qa-message-content">
                <?php echo h($post['created']);?>
                [<a href="show.php?id=<?php echo h($post['id']) ?>" style="color: #F33;">個別ページへ</a>]
              </div>
            </div>   
          </div>        
        </div>
      </div>
    <?php endwhile; ?>
  </body>
</html>
