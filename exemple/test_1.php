<?php
require_once '../csrf.php';
$csrf=new csrf();
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title>Document</title>
</head>
<body>';

echo '<form action="test.php" method="post">
        <input type="submit" value="Отправить_post" name="p">                
    </form>
    <form action="test.php" method="get">
        <input type="submit" value="Отправить_get" name="g">               
    </form>
</body>
</html>';




    