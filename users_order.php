<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffd1dc;
            /* Barbie Pink */
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            /* White */
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #ff69b4;
            /* Hot Pink */
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .order {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 15px;
        }

        .order p {
            margin: 5px 0;
        }

        strong {
            color: #ff69b4;
            /* Hot Pink */
        }

        /* Button Style */
        .btn {
            background-color: #ff69b4;
            /* Hot Pink */
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #ff1493;
            /* Deep Pink */
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Your Orders</h1>
        <?php

        include 'config.php';


        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('location: index.php');
            exit;
        }

        $user_id = $_SESSION['user_id'];

        // Retrieve user orders from the database
        $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
        $select_orders->execute([$user_id]);

        // Check if orders exist
        if ($select_orders->rowCount() > 0) {
            // Loop through each order
            while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                // Display order details
                echo '<div class="order">';
                echo '<p><strong>Order Placed On:</strong> ' . $fetch_orders['placed_on'] . '</p>';
                echo '<p><strong>Name:</strong> ' . $fetch_orders['name'] . '</p>';
                echo '<p><strong>Number:</strong> ' . $fetch_orders['number'] . '</p>';
                echo '<p><strong>Address:</strong> ' . $fetch_orders['address'] . '</p>';
                echo '<p><strong>Payment Method:</strong> ' . $fetch_orders['method'] . '</p>';
                echo '<p><strong>Total Products:</strong> ' . $fetch_orders['total_products'] . '</p>';
                echo '<p><strong>Total Price:</strong> $' . $fetch_orders['total_price'] . '</p>';
                echo '</div>';
            }
        } else {
            // Display message if no orders found
            echo '<p>No orders found.</p>';
        }
        ?>
        <button onclick="window.location.href='index.php'" class="btn">Back to Home</button>
    </div>
</body>

</html>