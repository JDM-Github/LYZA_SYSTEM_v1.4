<!-- Item Browser -->
<div class="content ms-3 flex-fill content-section active" id="pos">

    <!-- Search / Category Navigation -->

    <div class=" card shadow p-2 bg-body-tertiary rounded border-0 mb-3 ">
        <form action="" method="post">
            <div class="input-group input-group border-0 align-content-center p-2 pb-0">
                <p class=" fw-bold border-start border-3 border-success px-4 mb-3 me-5 align-content-center">
                    Point of Sale
                </p>

                <?php if (RequestSQL::getSession('online')) { ?>
                    <?php
                    $branch = RequestSQL::getSession('branch-pos');
                    $sessionSearch = '';
                    if ($branch)
                        $sessionSearch = $branch['search'];
                    $searchValue = isset($_POST['search-value']) ? $_POST['search-value'] : $sessionSearch;
                    ?>

                    <input type="text" class="form-control rounded-start mb-3" placeholder="Search..." name='search-value'
                        aria-describedby="search-button-product" value="<?php echo $searchValue ?>">

                    <button class="btn btn-sm rounded-end border px-3 mb-3 me-3" type="submit" id="search-button-product">
                        <i class="bi bi-search"></i>
                    </button>
                <? } ?>

            </div>
        </form>
    </div>

    <!-- Product Grid -->


    <div class="card card shadow p-3 bg-body-tertiary rounded border-0">
        <div class="input-group input-group-sm border-0 align-content-center">
            <?php if (RequestSQL::getSession('online')) { ?>
                <p class=" fw-bold border-start border-3 border-success px-4 me-5 align-content-center">
                    Products
                </p>
            <?php } ?>
            <form action="" class="form-control border-0 d-flex bg-body-tertiary" method="post">
                <?php if (RequestSQL::getSession('online')) { ?>
                    <?php
                    $categories = RequestSQL::getAllCategories();
                    $sessionCategory = '';
                    if ($branch)
                        $sessionCategory = $branch['category'];
                    $selectedCategory = isset($_POST['item-category']) ? $_POST['item-category'] : $sessionCategory;

                    function isSelected($option, $selectedValue)
                    {
                        return $option === $selectedValue ? 'selected' : '';
                    }
                    ?>

                    <select class="form-select rounded mb-3 me-3" name="item-category" id="item-category">
                        <option value="">-- Select Category --</option>
                        <?php
                        if ($categories) {
                            while ($category = $categories->fetch_assoc()) {
                                $categoryName = $category['category_name'];
                                echo "<option value='{$categoryName}' " . isSelected($categoryName, $selectedCategory) . ">{$categoryName}</option>";
                            }
                        }
                        ?>
                    </select>
                    <button class="btn btn-secondary mb-3 rounded" type="submit">Search</button>
                <?php } ?>
            </form>
        </div>

        <?php if (RequestSQL::getSession('online')) { ?>
            <div>
                <?php
                RequestSQL::getAllProductForCache(RequestSQL::getSession('account')['branchName']);
                $data = RequestSQL::getAllProduct(
                    'branch-pos',
                    $selectedCategory,
                    null,
                    $searchValue,
                    null,
                    RequestSQL::getSession('account')['branchName']
                );
                $products = $data['result'];
                $currentPage = $data['page'];
                $totalPages = $data['total'];
                BranchClass::loadAllPosProduct($products);
                ?>
                <div class="d-flex justify-content-between">
                    <?php
                    BranchClass::loadPaginator($currentPage, $totalPages, 'branch-pos-page');
                    ?>
                    <a class="btn btn-success mb-3 rounded border-dark-green" target="_blank"
                        href="modals/reciept.php">Print Last
                        Transaction</a>
                </div>


            </div>
        <?php } else { ?>
            <form action="backend/redirector.php" method="POST">
                <input type="hidden" name="type" value="branch-add-cart">
                <div class="mb-3">
                    <label for="productBarcode" class="form-label">Product Barcode</label>
                    <input type="text" class="form-control" id="productBarcode" name="productBarcode"
                        placeholder="Enter product barcode (Can use scanner)" required>
                </div>

                <button class='btn btn-secondary mb-3 me-2 rounded' type='submit'>Add Product</button>
                <a class="btn btn-success mb-3 rounded border-dark-green" target="_blank" href="modals/reciept.php">Print
                    Last
                    Transaction</a>
            </form>
        <?php } ?>
    </div>
</div>

<!-- Cart and Payment -------------------------------------------------------------------------->

<div class="card shadow p-3 bg-body-tertiary rounded border-0 ms-3 custom-cart-payment content-section active" id="pos">
    <div class=" d-flex justify-content-between">
        <p class=" fw-bold border-start border-3 border-success px-4 me-5 mb-0">
            Item Cart
        </p>
        <?php
        if (isset($_POST['reset-cart']))
            RequestSQL::setSession('branch-cart-product', []);
        ?>
        <form method="POST" action="">
            <button class="badge bg-secondary p-2" name="reset-cart">Reset</button>
        </form>
    </div>

    <!-- Item list on Cart-->
    <div class="card p-2 mt-3 rounded border-0">
        <table class="table table-borderless">
            <tr>
                <th class="align-content-center">
                    <small><span class="text-muted">Item/s</span></small>
                </th>
                <th class="align-content-center">
                    <small><span class="text-muted">Qty.</span></small>
                </th>
                <th class="align-content-center">
                    <small><span class="text-muted">Amount</span></small>
                </th>
            </tr>

            <?php $branchProducts = RequestSQL::getSession('branch-cart-product'); ?>
            <?php if ($branchProducts): ?>
                <?php foreach ($branchProducts as $branchProd): ?>
                    <tr>
                        <td class="align-content-center">
                            <small><span><?php echo htmlspecialchars($branchProd['product_name']); ?></span></small>
                        </td>
                        <td class="align-content-center">
                            <form method="POST" action="backend/redirector.php">
                                <input type='hidden' name='type' value='branch-add-cart'>
                                <input type="hidden" name="product_id"
                                    value="<?php echo htmlspecialchars($branchProd['product_id']); ?>">
                                <input type="hidden" name="branch_id"
                                    value="<?php echo htmlspecialchars($branchProd['branch_id']); ?>">
                                <input type="hidden" name="productBarcode"
                                    value="<?php echo htmlspecialchars($branchProd['product_barcode']); ?>">
                                <input type="hidden" name="product_name"
                                    value="<?php echo htmlspecialchars($branchProd['product_name']); ?>">
                                <input type="hidden" name="product_price"
                                    value="<?php echo htmlspecialchars($branchProd['product_price']); ?>">
                                <input type="hidden" name="product_stock"
                                    value="<?php echo htmlspecialchars($branchProd['product_stock']); ?>">

                                <button class="btn px-2 border" type="submit" name="action" value="decrement">-</button>
                                <small><span
                                        id="product-quantity"><?php echo htmlspecialchars($branchProd['quantity']); ?></span></small>
                                <button class="btn px-2 border" type="submit" name="action" value="increment">+</button>
                            </form>
                        </td>
                        <td class="align-content-center">
                            <small><span id="total-price-<?php echo htmlspecialchars($branchProd['product_id']); ?>">₱
                                    <?php echo number_format($branchProd['product_price'] * $branchProd['quantity'], 2); ?></span></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

        </table>
    </div>

    <div class="mt-3">

        <div class="d-flex align-content-center rounded p-2 custom-total mb-2">
            <?php
            $total = 0;
            if ($branchProducts) {
                foreach ($branchProducts as $product)
                    $total += $product['product_price'] * $product['quantity'];
            }
            ?>
            <span class="py-0 flex-fill fs-4 text-white">Total:</span>
            <span class="fw-bold flex-fill fs-4 ms-0 ps-0 text-end text-white">
                ₱ <?php echo number_format($total, 2); ?></span>
            <div id="total-amount" style="display: none"><?php echo $total; ?></div>
        </div>

        <div class="input-group align-content-center">
            <label class="form-label text-muted me-5 ps-2 py-0" for="cash-input">Cash Received:</label>
            <span class="input-group-text">₱</span>
            <input class="form-control rounded p-0 mb-2 text-end" placeholder="00.00" id="cash-input">
        </div>

        <div class="d-flex justify-content-between">
            <span class="text-muted ps-2">Change:</span>
            <span id="change-display" class="text-muted text-end pe-2 mb-3">₱ 00.00</span>
        </div>

        <div class="justify-content-center">
            <div class="btn-group">
                <button class="btn shadow rounded border-0 custom-calc-btn" onclick="appendToInput(7)"> <span
                        class="fs-5">7</span> </button>
                <button class="btn shadow rounded border-0 custom-calc-btn" onclick="appendToInput(8)"> <span
                        class="fs-5">8</span> </button>
                <button class="btn shadow rounded border-0 custom-calc-btn" onclick="appendToInput(9)"> <span
                        class="fs-5">9</span> </button>
            </div>
            <div class="btn-group">
                <button class="btn shadow rounded border-0 custom-calc-btn" onclick="appendToInput(4)"> <span
                        class="fs-5">4</span> </button>
                <button class="btn shadow rounded border-0 custom-calc-btn" onclick="appendToInput(5)"> <span
                        class="fs-5">5</span> </button>
                <button class="btn shadow rounded border-0 custom-calc-btn" onclick="appendToInput(6)"> <span
                        class="fs-5">6</span> </button>
            </div>
            <div class="btn-group">
                <button class="btn shadow rounded border-0 custom-calc-btn" onclick="appendToInput(1)"> <span
                        class="fs-5">1</span> </button>
                <button class="btn shadow rounded border-0 custom-calc-btn" onclick="appendToInput(2)"> <span
                        class="fs-5">2</span> </button>
                <button class="btn shadow rounded border-0 custom-calc-btn" onclick="appendToInput(3)"> <span
                        class="fs-5">3</span> </button>
            </div>
            <div class="btn-group">
                <button class="btn shadow rounded border-0 custom-calc-btn" onclick="appendToInput('.')"> <span
                        class="fs-5">.</span> </button>
                <button class="btn shadow rounded border-0 custom-calc-btn" onclick="appendToInput(0)"> <span
                        class="fs-5">0</span> </button>
                <button class="btn shadow rounded border-0 custom-calc-btn" onclick="clearInput()"> <span
                        class="fs-5">Del</span> </button>
            </div>
        </div>

        <div class="justify-content-center d-flex mt-3">
            <form method="POST" action="backend/redirector.php" onsubmit="return validateTransaction()">
                <input type="hidden" name="type" value="branch-add-transaction">
                <input type="hidden" id="total-input" name="total" value="0">
                <input type="hidden" id="received-input" name="received" value="0">
                <input type="hidden" id="change-input" name="change" value="0">

                <button class="btn flex-fill rounded border-1 custom-receipt-btn p-2 fs-4" type="submit">Save
                    Order</button>
            </form>
        </div>

    </div>
</div>

<div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive"
    aria-atomic="true" style="position: absolute; top: 20px; right: 20px; display: none;">
    <div class="d-flex">
        <div class="toast-body">Insufficient Cash Received!</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" aria-label="Close"
            onclick="closeToast('errorToast')"></button>
    </div>
</div>



<?php
if (RequestSQL::hasSession('success-message')) {
    echo '<div class="alert alert-success fixed-alert" id="message-alert" role="alert">' . htmlspecialchars(RequestSQL::getSession('success-message')) . '</div>';
    RequestSQL::unsetSession('success-message');
}

if (RequestSQL::hasSession('error-message')) {
    echo '<div class="alert alert-danger fixed-alert" id="message-alert" role="alert">' . htmlspecialchars(RequestSQL::getSession('error-message')) . '</div>';
    RequestSQL::unsetSession('error-message');
}
?>

<script>
    document.addEventListener('input', function () {
        // alert('DETECT');
    });
    document.addEventListener("DOMContentLoaded", function () {
        var alert = document.getElementById('message-alert');
        if (alert) {
            setTimeout(function () {
                alert.style.display = 'none';
            }, 3000);
        }
    });

    function updateHiddenInputs() {
        const totalAmount = parseFloat(document.getElementById('total-amount').innerText.replace(/[₱\s,]/g, '')) || 0;
        const cashReceived = parseFloat(document.getElementById('cash-input').value) || 0;
        const change = cashReceived - totalAmount;

        document.getElementById('total-input').value = totalAmount.toFixed(2);
        document.getElementById('received-input').value = cashReceived.toFixed(2);
        document.getElementById('change-input').value = change.toFixed(2);
    }

    function calculateChange() {
        const cashReceived = parseFloat(document.getElementById('cash-input').value) || 0;
        const totalAmount = parseFloat(document.getElementById('total-amount').innerText) || 0;
        const change = cashReceived - totalAmount;
        document.getElementById('change-display').innerText = '₱ ' + change.toFixed(2);
        updateHiddenInputs();
    }

    function appendToInput(value) {
        const input = document.getElementById('cash-input');
        input.value += value;
        calculateChange();
    }

    function clearInput() {
        document.getElementById('cash-input').value = '';
        calculateChange();
    }

    function validateTransaction() {

        const total = parseFloat(document.getElementById('total-amount').innerText.replace(/[₱\s,]/g, '')) || 0;
        const received = parseFloat(document.getElementById('cash-input').value) || 0;

        if (received < total) {
            showToast('errorToast');
            return false;
        }
        return true;
    }

    function showToast(toastId) {
        var toast = document.getElementById(toastId);
        toast.style.display = 'block';
        setTimeout(function () {
            toast.style.display = 'none';
        }, 3000);
    }

    function closeToast(toastId) {
        document.getElementById(toastId).style.display = 'none';
    }
</script>