<?php
    
    require('dbconnect.php');
    require('function.php');
    session_start();
    $id = $_REQUEST['id'];
        
    if (isset($_POST["password"])) {
        $sql = sprintf('SELECT * FROM posts WHERE id=%d',
            mysqli_real_escape_string($db, $id)
        );
        $sql = mysqli_query($db, $sql) or die (mysqli_error($db));
        $post = mysqli_fetch_assoc($sql);

        if ($post["password"] == $_POST["password"]) {
            $_SESSION["name"] = $post["user"];
            $_SESSION["post"] = $post["post"]; 
            $_SESSION["check_pass"] = "ok";
            header(sprintf('Location:edit.php?id=%d&edit=on',$_REQUEST["id"]));
       } else {
            $error["password"] = "wrong";    
       }
    } 


    if (isset($_REQUEST["edit"])) {
        if (empty($_SESSION["check_pass"])) {
            header(sprintf('Location:edit.php?id=%d',$_REQUEST["id"]));
        } elseif (isset($_SESSION["check_pass"])) {
            
            if (isset($_POST) && $_SESSION["check_pass"] == "ok") {
                if (!empty($_POST)) {
                    if ($_POST["user"] == "") {
                        $error["user"] = "Blanck"; 
                    } elseif (strlen($_POST['user']) > 20 ) {
                        $error["user"] = "long";
                    }
                    //投稿のエラー判定
                    if ($_POST["post"] == "") {
                        $error["post"] = "Blanck";          
                    } elseif (strlen($_POST['post']) > 400) {
                        $error["post"] = "long";                           
                    }       
                    if (empty($error)) {
                        $sql = sprintf('UPDATE posts SET user="%s", post="%s",modified=NOW() WHERE id=%d',
                            mysqli_real_escape_string($db, $_POST["user"]),
                            mysqli_real_escape_string($db, $_POST["post"]),
                            mysqli_real_escape_string($db, $_REQUEST["id"])  
                        );
                        mysqli_query($db, $sql) or die (mysqli_error($db));
                        $_SESSION['user'] = $_POST["user"];
                        $_SESSION["check_pass"] = "false";
                        header('Location:index.php'); 
                    } 
                }   
            } elseif ($_SESSION["check_pass"] == "false"){
                header(sprintf('Location:edit.php?id=%d',$_REQUEST["id"]));
            }
        }
    }
    
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="assets/css/bootstrap.css">
  <title>passwordを入力してください</title>
</head>
<body>
  <div class="container">
    <?php if (empty($_REQUEST["edit"])):?>
      <div class='well well-sm col-md-6 col-md-offset-3'>
      <center><H1><b>パスワード確認</b></H1></center>
          <form role="form" method="post">
          <div class="form-group">
            <label for="exampleInputEmail1">編集にはパスワードが必要です</label>
            <input type="password" class="form-control" id="exampleInputEmail1" placeholder="password" name="password">
            <?php
                if (isset($error["password"])) {
                    if ($error["password"] == "wrong") {
                        echo '<p class="text-danger">正しいパスワードをご入力ください。</p>';
                    }
                }
            ?>
          </div>
          <center><input type="submit" class="btn btn-default btn-danger btn-lg" value="編集画面へ"></center>
          <a href="index.php">編集をやめる</a>
        </form>
      </div>
  <?php endif; ?>
  <?php if (isset($_REQUEST["edit"])):?>
    <div class="span3 well">
      <legend>この投稿を編集できます。</legend>
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
                        echo sprintf('<input class="span3" name="user" value="%s" type="text">', h($_SESSION["name"]));
                    }
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
  <?php endif; ?>
</div>
</body>
</html>
