<?php

require_once 'DBConnect.php';



$db = new DBHandler();

  function getProductName($db, $category){
    $db -> query ('SELECT name FROM `products` wHERE category = :category ');
    $db -> bind (":category", $category);
    return $db -> resultset();    
  }



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dropdown</title>
</head>
<body>
<form action = "" method="post" enctype="multipart/form-data">

<label for="cat">Category</label>
        <select name="txtCat" placeholder="Choose category" required >
        <option value="EL">Choose Category</option>
        <option value="Juice">Juice</option>
        <option value="Smoothie">Smoothie</option>   
      

        <input type="submit" value="search" name="btnSave">   
        
</form>

<?php
        if (isset($_POST["btnSave"])){     
              $listProduct = getProductName($db, $_POST["txtCat"]);
            foreach($listProduct as $row) {
                echo "<br>".$row ["name"];
            }
   
}

?>

</body>
</html>