<?php
    
    require('dbconnect.php');
    $id = $_REQUEST['id'];
        
    if (isset($_POST["password"])) {
        $sql = sprintf('SELECT * FROM posts WHERE id=%d',
            mysqli_real_escape_string($db, $id)
        );
        $sql = mysqli_query($db, $sql) or die (mysqli_error($db));
        $delete_post = mysqli_fetch_assoc($sql);

        if ($delete_post["password"] == $_POST["password"]) {
            
            $sql = sprintf('UPDATE posts SET flag=1 WHERE id=%d && password="%s"',
                mysqli_real_escape_string($db, $id), 
                mysqli_real_escape_string($db, $_POST["password"])
            );      
            mysqli_query($db, $sql) or die (mysqli_error($db));
            header('Location: index.php');
            exit();  
        } else {
            $error["password"] = "wrong";
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
    <div class='well well-sm col-md-6 col-md-offset-3'>
    <center><H1><b>投稿削除確認</b></H1></center>
        <form role="form" method="post">
        <div class="form-group">
          <label for="exampleInputEmail1">パスワードをご入力ください</label>
          <input type="password" class="form-control" id="exampleInputEmail1" placeholder="password" name="password">
          <?php
              if (isset($error["password"])) {
                  if ($error["password"] == "wrong") {
                      echo '<p class="text-danger">正しいパスワードをご入力ください。</p>';
                  }
              }
          ?>
        </div>
        <center><input type="submit" class="btn btn-default btn-danger btn-lg" value="投稿を削除する"></center>
        <a href="index.php">削除をやめる</a>
      </form>

    </div>
</div>
</body>
</html>
