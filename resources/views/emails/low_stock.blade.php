<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Notification</title>
</head>
<body>
    <h2>Low Stock Notification</h2>
    <p>Hello Admin,</p>
    <p>This is to inform you that the stock of the following product is running low:</p>
    
    <ul>
        <li><strong>Product Name:</strong> {{ $product->name }}</li>
        <li><strong>Current Stock:</strong> {{ $product->stock }}</li>
    </ul>

    <p>Please take necessary action to replenish the stock as soon as possible.</p>
    
    <p>Thank you,</p>
    <p>Your Application Team</p>
</body>
</html>
