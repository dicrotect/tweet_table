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
            if (ctype_lower($_POST['password']) == true) {
                $error["password"] = "allsmall";
            } elseif (ctype_upper($_POST['password']) == true) {
                $error["password"] = "allbig";
            }
        } else {
            $error["password"] = "notalphabet";          
        }

        if ($_POST["comment"] == "") {
            $error["comment"] = "Blanck";
        } elseif (strlen($_POST['comment']) > 400) {
            $error["comment"] = "long";                           
        } 

        if (empty($error)) {
            $sql = sprintf('INSERT INTO comments SET post_id=%d, user="%s", password="%s", comment="%s", created=NOW()',
                mysqli_real_escape_string($db, $_REQUEST["id"]),
                mysqli_real_escape_string($db, $_POST["user"]),
                mysqli_real_escape_string($db, $_POST["password"]),
                mysqli_real_escape_string($db, $_POST["comment"])    
            );
            mysqli_query($db, $sql) or die (mysqli_error($db));
            $_SESSION['user'] = $_POST["user"];
            $posted = "on";
        } 
    }

    $sql = sprintf('SELECT * FROM comments WHERE post_id = %d ORDER BY created DESC',
         $_REQUEST['id']
    );
    $comments = mysqli_query($db, $sql) or die (mysqli_error($db));

    $sql = sprintf('SELECT * FROM posts WHERE id = %d',
        $_REQUEST['id']
    );
    $post = mysqli_query($db, $sql) or die (mysqli_error($db));
    $post = mysqli_fetch_assoc($post);

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title>簡易掲示板</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/index.css">
  </head>
  <body>
    <div class="container">
      <p><a href="index.php">投稿一覧へもどる</a></p>
      <div class="qa-message-list" id="wallmessages">
        <div class="message-item" id="m16">
          <div class="message-inner">
            <div class="message-head clearfix">
              <div class="message-icon pull-left"><i class="glyphicon glyphicon-check"></i></div>
              <div class="user-detail">
                <h5 class="handle"><?php echo h($post['user']); ?>さんのつぶやき</h5>
                <div class="post-type">
                  <?php echo h($post['post']);?>      
                </div>
              </div>
            </div>
            <div class="qa-message-content">
              <?php echo h($post['created']);?>
              [<a href="edit.php?id=<?php echo h($post['id']) ?>" style="color: #F33;">編集する</a>]
              [<a href="delete.php?id=<?php echo h($post['id']) ?>" style="color: #F33;">削除する</a>]
            </div>
          </div>   
        </div>        
      </div>
    </div>
    <!-- 入力フォーム -->
    <div class="span3 well">
      <legend>この投稿にコメントができます。</legend>
      <form accept-charset="UTF-8" action="" method="post">
        <div class="container">
          <div class="row">
            <label>ニックネーム[20文字以内]</label>
            <?php
                if (isset($_SESSION["user"]) && empty($_POST)){
                    echo sprintf('<input class="span3" name="user" value="%s" type="text">',h($_SESSION["user"]));
                } elseif (isset($_POST["user"])) {
                    if (isset($error["user"])) {
                        if ($error["user"] == "Blanck"){
                            echo '<input class="span3" name="user" type="text">';
                            echo '<p class="text-danger">必須項目です</p>';
                        } elseif ($error["user"] == "long") {
                            echo '<input class="span3" name="user" type="text">';
                            echo '<p class="text-danger">ニックネームは20文字以内でご入力ください</p>';
                        }
                    } else {
                        echo sprintf('<input type="text" name="user" value="%s">',
                            h($_POST["user"])
                        );
                    } 
                } else {
                    echo '<input class="span3" name="user" placeholder="例) ネクシー子" type="text"> ';
                }
            ?>
          </div>
          <div class="row">
            <label >パスワード[半角英数/大文字小文字混在で4~8文字]</label>
            <?php
                if (isset($_POST["password"])) {
                    if (isset($error["password"])) {
                        if ($error["password"] == "Blanck") {
                            echo '<input class="span3" name="password" type="password">';
                            echo '<p class="text-danger">パスワードを入力してください</p>';
                        } elseif ($error["password"] == "short" || $error['password'] == "long") {
                            echo '<input class="span3" name="password" type="password">';
                            echo '<p class="text-danger">パスワードの文字数は4~8文字で設定ください</p>';
                        } elseif ($error["password"] == "notalphabet" || $error["password"] == "allsmall" || $error["password"] == "allbig") {
                            echo '<input class="span3" name="password" type="password">';
                            echo '<p class="text-danger">半角英数大文字小文字をまぜて設定してください</p>';
                        } else {
                            echo '<input class="span3" name="password" type="password">';
                        }
                    } elseif (empty($error["password"])) {
                        echo '<input class="span3" name="password" type="password">';
                    }
                 } else {
                   echo '<input class="span3" name="password" placeholder="例) HjlsOk" type="password">';
                 }
            ?>  
          </div>
        </div>
        <div class="container">
          <div class="row">
            <div class="col-md-10" style="padding-left: 0px;">
              <div class="panel panel-primary">
                <div class="panel-heading">  
                  <p>コメントを書き込もう!</p>          
                </div>
                
                  <div class="panel-body ">
                      <?php
                          if (isset($_POST["comment"]) && isset($error)) {               
                              if (isset($error["comment"])) {
                                  if ($error["comment"] == "Blanck"){
                                      echo '<input class="span3 col-xs-12" name="comment" type="text" placeholder="ここにコメントを書き込みます">';
                                      echo '<p class="text-danger">つぶやきを投稿してください</p>';
                                  } elseif ($error["comment"] == "long") {
                                      echo sprintf('<input class="span3 col-xs-12" name="commet" value="%s" type="textarea">',
                                          h($_POST["comment"])
                                      );
                                      echo '<p class="text-danger">400文字以内で投稿してください</p>';
                                  }   
                              } else {
                                echo sprintf('<input class="span3 col-xs-12" type="textarea" name="comment" value="%s">',
                                    h($_POST["comment"])
                                );
                              }                 
                          } else {
                              echo '<input class="span3 col-xs-12" name="comment" type="textarea" placeholder="ここにつぶやきを書き込みます">';
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
 
    <?php
        $sql = sprintf('SELECT * FROM comments WHERE post_id = %d ORDER BY created DESC',
             $_REQUEST['id']
        );
        $check = mysqli_query($db, $sql) or die (mysqli_error($db));
        $check_comment = mysqli_fetch_assoc($check);
        if (empty($check_comment)):
    ?>
    <div class="container">
      <div class="row">
        <div class="col-xs-6 bg-info">
          <h4 class="text-info">コメントはありません。</h4>
        </div>
      </div>
    </div>
    <?php endif;?>

    <?php if (isset($posted)):?>
      <div class="container">
        <div class="row">
          <div class="col-xs-6 bg-warning">
            <h4 class="text-warning">コメントを投稿しました。</h4>
          </div>
        </div>
    <?php endif;?>
    
    <?php while($comment = mysqli_fetch_assoc($comments)):?>
      <div class="container">
        <div class="qa-message-list">
          <div class="message-item">
            <div class="message-inner">
              <div class="message-head clearfix">
                <div class="message-icon pull-left" style="background-color:#4567ff;"><i class="glyphicon glyphicon-check"></i></div>
                <div class="user-detail">
                  <h5 class="handle"><?php echo h($comment['user']); ?>さん</h5>
                  <div class="post-type">
                    <?php echo h($comment['comment']);?>      
                  </div>
                </div>
              </div>
              <div class="qa-message-content">
                <?php echo h($comment['created']);?>
              </div>
            </div>   
          </div>        
        </div>
      </div>
    <?php endwhile;?>
  </body>
</html>
