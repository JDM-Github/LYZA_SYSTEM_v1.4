<?php
session_start();
$transaction = $_SESSION['last_transaction'] ?? null;

if (!$transaction) {
    echo "No transaction found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Printable Receipt</title>
    <title>Transaction Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .receipt {
            width: 320px;
            margin: auto;
            text-align: center;
            background-color: #fff;
            border: 2px dashed black;
            padding: 20px;
        }

        .receipt h2 {
            margin: 0 0 10px;
            font-size: 14px;
            font-weight: bold;
        }

        .receipt p {
            margin: 4px 0;
            font-size: 12px;
            color: #333;
        }

        .receipt hr {
            border: none;
            border-top: 1px dashed #ccc;
            margin: 10px 0;
        }

        .receipt table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 10px;
        }

        .receipt th,
        .receipt td {
            padding: 4px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .receipt th {
            background-color: #f8f8f8;
            font-weight: bold;
        }

        .receipt .total-section {
            margin-top: 10px;
        }

        .receipt .total-section .total-item {
            display: flex;
            justify-content: space-between;
            width: 100%;
            font-size: 8px;
        }

        .receipt .thank-you {
            margin-top: 10px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>

<body>
    <?php
    $all_products = '';
    $products = $transaction['productOrderedIds'];
    foreach ($products as $product) {
        $all_products .= "<tr>";
        $all_products .= "<td class='align-content-center ps-4'><span>" . htmlspecialchars($product['branch_name']) . "</span></td>";
        $all_products .= "<td class='align-content-center'><small><span>" . htmlspecialchars($product['product_name']) . "</span></small></td>";
        $all_products .= "<td class='align-content-center'><small><span>" . htmlspecialchars($product['quantity']) . "</span></small></td>";
        $all_products .= "<td class='align-content-center'><small><span>₱" . htmlspecialchars($product['product_price']) . "</span></small></td>";
        $all_products .= "<td class='align-content-center'><small><span>₱" . htmlspecialchars($product['product_price'] * $product['quantity']) . "</span></small></td>";
        $all_products .= "</tr>";
    }
    ?>

    <div class="receipt">
        <h2>Lyza Drugmart</h2>
        <p>Thank you for your purchase!</p>
        <p><strong>Staff:</strong> <?php echo $transaction['staffUsername']; ?>
        </p>
        <p><strong>Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <hr>

        <table class='table table-sm table-hover' id='productDetailsTable'>
            <thead>
                <tr>
                    <th class='ps-4'><small><span class='fw-bold'>Branch Name</span></small></th>
                    <th class=''><small><span class='fw-bold'>Product Name</span></small></th>
                    <th class=''><small><span class='fw-bold'>Quantity</span></small></th>
                    <th class=''><small><span class='fw-bold'>Price</span></small></th>
                    <th class=''><small><span class='fw-bold'>Total Price</span></small></th>
                </tr>
            </thead>
            <tbody>
                <?php echo $all_products ?>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-item">
                <span>Total:</span>
                <span>₱$<?php echo $transaction['totalPrice']; ?></span>
            </div>
            <div class="total-item">
                <span>Cash Received:</span>
                <span>₱$<?php echo $transaction['cashPrice']; ?></span>
            </div>
            <div class="total-item">
                <span>Change:</span>
                <span>₱$<?php echo $transaction['changePrice']; ?></span>
            </div>
        </div>

        <hr>
        <p>Thank you for on Lyza Store!</p>
        <p>Visit again!</p>
    </div>

    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</body>

</html>