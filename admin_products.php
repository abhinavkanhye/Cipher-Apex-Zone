<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_POST['add_product'])) {

   $name = $_POST['name'];
   $price = $_POST['price'];
   $category = $_POST['category']; // Added category variable
   $quantity = $_POST['quantity']; // Added quantity variable


   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/' . $image;

   $select_product = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_product->execute([$name]);

   if ($select_product->rowCount() > 0) {
      $message[] = 'product name already exists!';
   } else {
      if ($image_size > 2000000) {
         $message[] = 'image size is too large!';
      } else {
         $insert_product = $conn->prepare("INSERT INTO `products`(name, price, image, category) VALUES(?,?,?,?)"); // Added category to the query
         $insert_product->execute([$name, $price, $image, $category]); // Added category to the execute method
         move_uploaded_file($image_tmp_name, $image_folder);
         $message[] = 'new product added!';
      }
   }
}

if (isset($_GET['delete'])) {

   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT image FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   unlink('uploaded_img/' . $fetch_delete_image['image']);
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   header('location:admin_products.php');
}

if (isset($_POST['order'])) {

   if ($user_id == '') {
       $message[] = 'please login first!';
   } else {
       $name = $_POST['name'];
       $number = $_POST['number'];
       $address = $_POST['address'];
       $method = $_POST['method'];
       $total_price = $_POST['total_price'];
       $total_products = $_POST['total_products'];

       $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
       $select_cart->execute([$user_id]);

       if ($select_cart->rowCount() > 0) {
           $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
           $insert_order->execute([$user_id, $name, $number, $method, $address, $total_products, $total_price]);

           // Decrement product quantity and update the database
           $cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);
           foreach ($cart_items as $item) {
               $qty_ordered = $item['quantity'];
               $pid = $item['pid'];

               // Fetch current quantity of the product
               $select_product_qty = $conn->prepare("SELECT quantity FROM `products` WHERE id = ?");
               $select_product_qty->execute([$pid]);
               $fetch_product_qty = $select_product_qty->fetch(PDO::FETCH_ASSOC);
               $current_qty = $fetch_product_qty['quantity'];

               // Check if the quantity to be ordered exceeds 10
               if ($qty_ordered >= 10) {
                   // If so, set quantity to 0 (out of stock)
                   $new_qty = 0;
               } else {
                   // Otherwise, subtract the ordered quantity from the current quantity
                   $new_qty = $current_qty - $qty_ordered;
               }

               // Update the product quantity in the database
               $update_product_qty = $conn->prepare("UPDATE `products` SET quantity = ? WHERE id = ?");
               $update_product_qty->execute([$new_qty, $pid]);
           }

           // Clear the cart after placing the order
           $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
           $delete_cart->execute([$user_id]);
           $message[] = 'order placed successfully!';
       } else {
           $message[] = 'your cart is empty!';
       }
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">


   <link rel="stylesheet" href="admin_style.css">

</head>

<body>

   <?php include 'admin_header.php' ?>

   <section class="add-products">

      <h1 class="heading">add product</h1>

      <form action="" method="post" enctype="multipart/form-data">
         <select class="box" name="category">
            <option value="" selected disabled>Select category</option>
            <option value="Clothing">Clothing</option>
            <option value="Furniture">Furniture</option>
            <option value="Vehicles">Vehicles</option>
            <option value="Collectibles">Collectibles</option>
         </select> <input type="text" class="box" required maxlength="100" placeholder="Enter product name" name="name">
         <input type="number" min="0" class="box" required max="9999999999" placeholder="Enter product price" onkeypress="if(this.value.length == 10) return false;" name="price">
         <input type="number" min="0" class="box" required max="10" placeholder="Enter Quantity" onkeypress="if(this.value.length == 10) return false;" name="quantity">
         <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
         <input type="submit" value="add product" class="btn" name="add_product">
      </form>

   </section>

   <section class="show-products">

      <h1 class="heading">products added</h1>

      <div class="box-container">

         <?php
         $select_products = $conn->prepare("SELECT * FROM `products`");
         $select_products->execute();
         if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <div class="box">
                  <div class="price">Rs<span><?= $fetch_products['price']; ?></span>/-</div>
                  <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                  <div class="name"><?= $fetch_products['name']; ?></div>
                  <div class="category"><?= $fetch_products['category']; ?></div>
                  <div class="quantity"><?= $fetch_products['quantity']; ?></div>
                  <div class="flex-btn">
                     <a href="admin_product_update.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
                     <a href="admin_products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
                  </div>
               </div>
         <?php
            }
         } else {
            echo '<p class="empty">no products added yet!</p>';
         }
         ?>

      </div>

   </section>



   <script src="admin_script.js"></script>

</body>

</html>