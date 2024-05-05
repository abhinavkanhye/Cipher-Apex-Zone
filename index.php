<?php

include 'config.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
};

if (isset($_POST['register'])) {

    $name = $_POST['name'];

    $email = $_POST['email'];

    $pass = $_POST['pass'];

    $cpass = $_POST['cpass'];


    $select_user = $conn->prepare("SELECT * FROM `user` WHERE name = ? AND email = ?");
    $select_user->execute([$name, $email]);

    if ($select_user->rowCount() > 0) {
        $message[] = 'username or email already exists!';
    } else {
        if ($pass != $cpass) {
            $message[] = 'passwords are different!';
        } else {
            $insert_user = $conn->prepare("INSERT INTO `user`(name, email, password) VALUES(?,?,?)");
            $insert_user->execute([$name, $email, $cpass]);
            $message[] = 'registered successfully, login now please!';
        }
    }
}

if (isset($_POST['update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $qty = $_POST['qty'];
    $qty = filter_var($qty);
    $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
    $update_qty->execute([$qty, $cart_id]);
    $message[] = 'cart quantity updated!';
}

if (isset($_GET['delete_cart_item'])) {
    $delete_cart_id = $_GET['delete_cart_item'];
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
    $delete_cart_item->execute([$delete_cart_id]);
    header('location:index.php');
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('location:index.php');
}

if (isset($_POST['add_to_cart'])) {

    if ($user_id == '') {
        $message[] = 'please login first!';
    } else {

        $pid = $_POST['pid'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $image = $_POST['image'];
        $qty = $_POST['qty'];


        $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
        $select_cart->execute([$user_id, $name]);

        if ($select_cart->rowCount() > 0) {
            $message[] = 'already added to cart';
        } else {
            $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
            $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
            $message[] = 'added to cart!';
        }
    }
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
            $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
            $delete_cart->execute([$user_id]);
            $message[] = 'order placed successfully!';
        } else {
            $message[] = 'your cart empty!';
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
    <title>Kenstore.mu</title>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="style.css">

</head>

<body>

    <?php
    if (isset($message)) {
        foreach ($message as $message) {
            echo '
         <div class="message">
            <span>' . $message . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
        }
    }
    ?>

    <!-- header section starts  -->

    <header class="header">

        <section class="flex">

            <a href="#home" class="logo"><span>K E N</span> Merchandise</a>

            <nav class="navbar">
                <a href="#home">home</a>
                <a href="#about">about</a>
                <a href="#catalog">catalog</a>
                <a href="#order">order</a>
                <a href="#faq">faq</a>
            </nav>

            <div class="icons">
                <div id="menu-btn" class="fas fa-bars"></div>
                <div id="user-btn" class="fas fa-user"></div>
                <div id="order-btn" class="fas fa-box"></div>
                <?php
                $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $count_cart_items->execute([$user_id]);
                $total_cart_items = $count_cart_items->rowCount();
                ?>
                <div id="cart-btn" class="fas fa-shopping-cart"><span>(<?= $total_cart_items; ?>)</span></div>
                <a href="admin_login.php">
                    <div id="admin-btn" class="fa-brands fa-adn"></div>
                </a>

            </div>

        </section>

    </header>

    <!-- header section ends -->

    <div class="user-account">

        <section>

            <div id="close-account"><span>close</span></div>

            <div class="user">
                <?php
                $select_user = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
                $select_user->execute([$user_id]);
                if ($select_user->rowCount() > 0) {
                    while ($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)) {
                        echo '<p>Welcome , <span>' . $fetch_user['name'] . '</span> !</p>';
                        echo '<a href="index.php?logout" class="btn">logout</a>';
                    }
                } else {
                    echo '<p><span>you are not logged in now!</span></p>';
                }
                ?>
            </div>

            <div class="display-orders">
                <?php
                $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $select_cart->execute([$user_id]);
                if ($select_cart->rowCount() > 0) {
                    while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                        echo '<p>' . $fetch_cart['name'] . ' <span>(' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')</span></p>';
                    }
                } else {
                    echo '<p><span>your cart is empty!</span></p>';
                }
                ?>
            </div>

            <div class="flex">

                <form action="user_login.php" method="post">
                    <h3>login now</h3>
                    <input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
                    <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20">
                    <input type="submit" value="login now" name="login" class="btn">
                </form>

                <form action="" method="post">
                    <h3>register now</h3>
                    <input type="text" name="name" required class="box" placeholder="enter your username" maxlength="20">
                    <input type="email" name="email" oninput="this.value = this.value.replace(/\s/g, '')" required class="box" placeholder="enter your email" maxlength="50">
                    <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
                    <input type="password" name="cpass" required class="box" placeholder="confirm your password" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
                    <input type="submit" value="register now" name="register" class="btn">
                </form>

            </div>

        </section>

    </div>

    <div class="my-orders">

        <section>

            <div id="close-orders"><span>close</span></div>

            <h3 class="title"> my orders </h3>

            <?php
            $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
            $select_orders->execute([$user_id]);
            if ($select_orders->rowCount() > 0) {
                while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
            ?>
                    <div class="box">
                        <p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
                        <p> name : <span><?= $fetch_orders['name']; ?></span> </p>
                        <p> number : <span><?= $fetch_orders['number']; ?></span> </p>
                        <p> address : <span><?= $fetch_orders['address']; ?></span> </p>
                        <p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>
                        <p> total_orders : <span><?= $fetch_orders['total_products']; ?></span> </p>
                        <p> total price : <span>$<?= $fetch_orders['total_price']; ?>/-</span> </p>
                        <p> payment status : <span style="color:<?php if ($fetch_orders['payment_status'] == 'pending') {
                                                                    echo 'red';
                                                                } else {
                                                                    echo 'green';
                                                                }; ?>"><?= $fetch_orders['payment_status']; ?></span> </p>
                    </div>
            <?php
                }
            } else {
                echo '<p class="empty">nothing ordered yet!</p>';
            }
            ?>

        </section>

    </div>

    <div class="shopping-cart">

        <section>

            <div id="close-cart"><span>close</span></div>

            <?php
            $grand_total = 0;
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if ($select_cart->rowCount() > 0) {
                while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                    $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
                    $grand_total += $sub_total;
            ?>
                    <div class="box">
                        <a href="index.php?delete_cart_item=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('delete this cart item?');"></a>
                        <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
                        <div class="content">
                            <p> <?= $fetch_cart['name']; ?> <span>(<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?>)</span></p>
                            <form action="" method="post">
                                <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
                                <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" onkeypress="if(this.value.length == 2) return false;">
                                <button type="submit" class="fas fa-edit" name="update_qty"></button>
                            </form>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo '<p class="empty"><span>your cart is empty!</span></p>';
            }
            ?>

            <div class="cart-total"> grand total : <span>Rs<?= $grand_total; ?>/-</span></div>

            <a href="#order" class="btn">order now</a>

        </section>

    </div>

    <div class="home-bg">

        <section class="home" id="home">

            <div class="slide-container">

                <div class="slide active">
                    <div class="image">
                        <img src="images/Banner1.png" alt="">
                    </div>
                    <div class="content">
                        <h3>What's Up Fans</h3>
                        <div class="fas fa-angle-left" onclick="prev()"></div>
                        <div class="fas fa-angle-right" onclick="next()"></div>
                    </div>
                </div>

                <div class="slide">
                    <div class="image">
                        <img src="images/Banner 2.png" alt="">
                    </div>
                    <div class="content">
                        <h3>A fan will always support</h3>
                        <div class="fas fa-angle-left" onclick="prev()"></div>
                        <div class="fas fa-angle-right" onclick="next()"></div>
                    </div>
                </div>

                <div class="slide">
                    <div class="image">
                        <img src="images/Ken jeep 2.png" alt="">
                    </div>
                    <div class="content">
                        <h3>Keep it Lowkey</h3>
                        <div class="fas fa-angle-left" onclick="prev()"></div>
                        <div class="fas fa-angle-right" onclick="next()"></div>
                    </div>
                </div>

            </div>

        </section>

    </div>

    <!-- about section starts  -->

    <section class="about" id="about">

        <h1 class="heading">Welcome to Ken's Post-Divorce Sale</h1>

        <div class="box-container">

            <div class="box">
                <img src="images/Ken House.png" alt="Ken's House">
                <h3>Ken's Dream House</h3>
                <p>Say goodbye to Ken's iconic dream house! After the divorce with Barbie, Ken has decided to part ways with this spacious and stylish abode. It's your chance to own a piece of toy history.</p>
                <a href="#catalog" class="btn">Browse Houses</a>
            </div>

            <div class="box">
                <img src="images/KenJeep.jpg" alt="Ken's Car">
                <h3>Ken's Convertible</h3>
                <p>Looking for a stylish ride? Ken's convertible is up for grabs! Cruise down the imaginary streets in style with this sleek and sporty vehicle, previously owned by the iconic doll couple.</p>
                <a href="#catalog" class="btn">Browse Cars</a>
            </div>

            <div class="box">
                <img src="images/81fqnytx.png" alt="Ken's Belongings">
                <h3>Ken's Merch</h3>
                <p>Ken is downsizing! Explore a collection of Ken's Merch, from designer outfits to rare collectibles. Don't miss out on this opportunity to own a piece of nostalgia.</p>
                <a href="#catalog" class="btn">Browse Belongings</a>
            </div>

        </div>

    </section>


    <!-- about section ends -->

    <!-- catalog section starts  -->

    <section id="catalog" class="catalog">

        <h1 class="heading">our catalog</h1>

        <div class="box-container">

        <?php
            $select_products = $conn->prepare("SELECT * FROM `products`");
            $select_products->execute();
            if ($select_products->rowCount() > 0) {
                while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
            ?>
                    <div class="box">
                        <div class="price">Rs<?= $fetch_products['price'] ?>/-</div>
                        <img src="uploaded_img/<?= $fetch_products['image'] ?>" alt="">
                        <div class="name"><?= $fetch_products['name'] ?></div>
                        <form action="" method="post">
                            <input type="hidden" name="pid" value="<?= $fetch_products['id'] ?>">
                            <input type="hidden" name="name" value="<?= $fetch_products['name'] ?>">
                            <input type="hidden" name="price" value="<?= $fetch_products['price'] ?>">
                            <input type="hidden" name="image" value="<?= $fetch_products['image'] ?>">
                            <input type="number" name="qty" class="qty" min="1" max="10" onkeypress="if(this.value.length == 2) return false;" value="1">
                            <input type="submit" class="btn" name="add_to_cart" value="add to cart">
                        </form>
                    </div>
            <?php
                }
            } else {
                echo '<p class="empty">no products added yet!</p>';
            }
            ?>

        </div>

    </section>

    <!-- catalog section ends -->

    <!-- order section starts  -->

    <section class="order" id="order">

        <h1 class="heading">order now</h1>

        <form action="" method="post">

            <div class="display-orders">

                <?php
                $grand_total = 0;
                $cart_item[] = '';
                $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $select_cart->execute([$user_id]);
                if ($select_cart->rowCount() > 0) {
                    while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                        $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
                        $grand_total += $sub_total;
                        $cart_item[] = $fetch_cart['name'] . ' ( ' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ' ) - ';
                        $total_products = implode($cart_item);
                        echo '<p>' . $fetch_cart['name'] . ' <span>(' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')</span></p>';
                    }
                } else {
                    echo '<p class="empty"><span>your cart is empty!</span></p>';
                }
                ?>

            </div>

            <div class="grand-total"> grand total : <span>$<?= $grand_total; ?>/-</span></div>

            <input type="hidden" name="total_products" value="<?= $total_products; ?>">
            <input type="hidden" name="total_price" value="<?= $grand_total; ?>">

            <div class="flex">
                <div class="inputBox">
                    <span>your name : </span>
                    <input type="text" name="name" class="box" required placeholder="enter your name" maxlength="20">
                </div>

                <div class="inputBox">
                    <span>your number : </span>
                    <input type="number" name="number" class="box" required placeholder="enter your number" min="0">
                </div>

                <div class="inputBox">
                    <span>your address : </span>
                    <input type="text" name="address" class="box" required placeholder="enter your address" maxlength="50">
                </div>

                <div class="inputBox">
                    <span>choose payment method : </span>
                    <select name="method" class="box">
                        <option value="cash">cash on delivery</option>
                        <option value="credit card">credit card</option>
                        <option value="Pay Pal">Pay Pal</option>
                        <option value="Debit card">Debit card</option>
                        <option value="bitcoin">bitcoin</option>
                    </select>
                </div>

            </div>

            <input type="submit" value="order now" class="btn" name="order">

        </form>

    </section>

    <!-- order section ends -->

    <!-- faq section starts  -->

    <section class="faq" id="faq">

        <h1 class="heading">Frequently Asked Questions</h1>

        <div class="accordion-container">

            <div class="accordion active">
                <div class="accordion-heading">
                    <span>How does the sale work?</span>
                    <i class="fas fa-angle-down"></i>
                </div>
                <p class="accordion-content">
                    It's simple! Browse through Ken's items, select what you want to purchase, provide your information, make the payment, and wait for the delivery or pickup instructions.
                </p>
            </div>

            <div class="accordion">
                <div class="accordion-heading">
                    <span>How long does it take for delivery?</span>
                    <i class="fas fa-angle-down"></i>
                </div>
                <p class="accordion-content">
                    Delivery times vary depending on your location and the item purchased. We aim to get your order to you as soon as possible, typically within a few days.
                </p>
            </div>

            <div class="accordion">
                <div class="accordion-heading">
                    <span>What condition are Ken's belongings in?</span>
                    <i class="fas fa-angle-down"></i>
                </div>
                <p class="accordion-content">
                    Ken takes pride in maintaining his belongings. Each item is carefully inspected and described accurately in the listing. However, as these are pre-owned items, some signs of wear may be present.
                </p>
            </div>

        </div>

    </section>



    <!-- faq section ends -->

    <!-- footer section starts  -->

    <section class="footer">

        <div class="box-container">

            <div class="box">
                <i class="fas fa-phone"></i>
                <h3>phone number</h3>
                <p>+230 57846418</p>
            </div>

            <div class="box">
                <i class="fas fa-map-marker-alt"></i>
                <h3>our address</h3>
                <p>Port Louis, Mauritius</p>
            </div>

            <div class="box">
                <i class="fas fa-clock"></i>
                <h3>opening hours</h3>
                <p>08:00 - 18:00</p>
            </div>

            <div class="box">
                <i class="fas fa-envelope"></i>
                <h3>email address</h3>
                <p>thekenstore.mu</p>
            </div>

        </div>



    </section>

    <!-- footer section ends -->

    <script src="script.js"></script>

</body>

</html>