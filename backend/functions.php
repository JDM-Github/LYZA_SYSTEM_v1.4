<?php


class AdminClass
{
    static function isSelected($option, $selectedValue)
    {
        return $option === $selectedValue ? 'selected' : '';
    }
    static function loadAllAccounts($accounts)
    {
        echo "<table class='table table-sm table-hover'>";
        if ($accounts->num_rows != 0) {
            while ($account = $accounts->fetch_assoc()) {
                $status = strtolower($account['userStatus']);

                $isRemoved = $status === 'removed';
                $isActive = $status === 'active';
                $isDisabled = $status === 'disabled';

                $isAdmin = $account['isAdmin'] == "1" ? "ADMIN" : "CLIENT";
                $isStatus = $account['isAdmin'] == "1" ? "badge bg-success" : "badge bg-secondary";

                echo
                    "<tr>
                        <td class='align-content-center ps-4'>
                            <small><span class='text-muted'>Account ID</span></small><br>
                            <small><span class='badge text-bg-secondary'>{$account['id']}</span></small>
                        </td>

                        <td class='align-content-center ps-4'>
                            <small><span class='text-muted'>Full Name</span></small><br>
                            <small><span>{$account['firstName']} {$account['lastName']}</span></small>
                        </td>

                        <td class='align-content-center ps-4'>
                            <small><span class='text-muted'>Email</span></small><br>
                            <small><span>{$account['email']}</span></small>
                        </td>

                        <td class='align-content-center ps-4'>
                            <small><span class='text-muted'>Assigned Branch</span></small><br>
                            <small><span>{$account['branchName']}</span></small>
                        </td>

                        <td class='align-content-center ps-4'>
                            <small><span class='text-muted'>STATUS</span></small><br>
                            <small><span class='{$isStatus}'>{$isAdmin}</span></small>
                        </td>

                        <td class='align-content-center ms-auto'>
                            <form action='backend/redirector.php' method='POST' onsubmit='return confirmDeactivate()'>
                                <input type='hidden' name='user_id' value='{$account['id']}'>
                                <input type='hidden' name='userStatus' value='disabled'>
                                <input type='hidden' name='type' value='admin-set-user-status'>
                                <button class='btn btn-danger p-1' type='submit' id='deactivate-btn' 
                                    " . ($isDisabled || $isRemoved ? "disabled" : "") . ">
                                    <small><i class='bi bi-trash-fill me-2'></i><span>Deactivate</span></small>
                                </button>
                            </form>
                        </td>

                        <td class='align-content-center ms-auto'>
                            <form action='backend/redirector.php' method='POST' onsubmit='return confirmActivate()'>
                                <input type='hidden' name='user_id' value='{$account['id']}'>
                                <input type='hidden' name='userStatus' value='active'>
                                <input type='hidden' name='type' value='admin-set-user-status'>

                                <button class='btn btn-success p-1' type='submit' id='activate-btn' 
                                        " . ($isActive || $isRemoved ? "disabled" : "") . ">
                                    <small><i class='bi bi-check-circle-fill me-2'></i><span>Activate</span></small>
                                </button>
                            </form>
                        </td>
                            
                        <td class='align-content-center ms-auto'>
                            <form action='backend/redirector.php' method='POST' onsubmit='return confirmRemoval()'>
                                <input type='hidden' name='user_id' value='{$account['id']}'>
                                <input type='hidden' name='userStatus' value='removed'>
                                <input type='hidden' name='type' value='admin-set-user-status'>

                                <button class='btn btn-secondary p-1' type='submit' id='removed-btn' 
                                    " . ($isRemoved ? 'disabled' : '') . ">
                                    <small><i class='bi bi-x-circle-fill me-2'></i><span>Removed</span></small>
                                </button>
                            </form>
                        </td>

                    </tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center'>No accounts found</td></tr>";
        }
        echo "</table>";
    }

    static function loadAllStockHistory($histories)
    {
        echo "<table class='table table-sm table-hover'>";
        if ($histories->num_rows != 0) {
            while ($history = $histories->fetch_assoc()) {
                echo
                    "<tr>
                        <td class='align-content-center ps-4'>
                            <small><span class='text-muted'>Stock ID</span></small><br>
                            <small><span class='badge text-bg-secondary'>{$history['id']}</span></small>
                        </td>

                        <td class='align-content-center ps-4'>
                            <small><span class='text-muted'>Branch Name</span></small><br>
                            <small><span>{$history['branchName']}</span></small>
                        </td>

                        <td class='align-content-center ps-4'>
                            <small><span class='text-muted'>Product Name</span></small><br>
                            <small><span>{$history['productName']}</span></small>
                        </td>

                        <td class='align-content-center ps-4'>
                            <small><span class='text-muted'>Stock Quantity</span></small><br>
                            <small><span>{$history['quantity']}</span></small>
                        </td>

                        <td class='align-content-center ps-4'>
                            <small><span class='text-muted'>Stock Quantity</span></small><br>
                            <small><span>{$history['firstName']} {$history['lastName']}</span></small>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center'>No stock history found</td></tr>";
        }
        echo "</table>";
    }

    static function loadAllLatestTransaction($transactions)
    {
        echo "<table class='table table-sm table-hover'>";
        if ($transactions->num_rows != 0) {
            foreach ($transactions as $row) {
                $modalId = 'details-modal-' . $row['id'];

                $products = RequestSQL::getAllProductIDS($row['product_ordered_list']);
                $all_products = '';

                foreach ($products as $product) {
                    $all_products .= "
                    <span class='d-flex justify-content-between'>
                        <span class='text-muted'>" . $product['numberProduct'] . "x " . htmlspecialchars($product['productName']) . "</span>
                        <span class='text-muted fw-bold'>" . number_format($product['productPrice'] * $product['numberProduct'], 2) . "</span>
                    </span>";
                }
                echo "
                <tr>
                    <td class='align-content-center ps-4'>
                        <small><span class='text-muted'>Transaction ID</span></small><br>
                        <small><span class='fw-bold'>{$row['id']}</span></small>
                    </td>
                    <td class='align-content-center'>
                        <small><span class='text-muted'>Amount</span></small><br>
                        <small><span class='fw-bold'>₱&nbsp;{$row['total_amount']}</span></small>
                    </td>
                    <td class='align-content-center'>
                        <small>{$row['transaction_date']}</small>
                    </td>
                    <td class='align-content-center'>
                        <a class='link-offset-2 link-underline link-underline-opacity-0 d-flex' href='#' id='details-modal' data-bs-toggle='modal' data-bs-target='#$modalId'>
                            <small><i class='bi bi-three-dots fs-4 text-secondary'></i></small>
                        </a>

                        <!-- Details Modal -----> 
                        <div class='modal fade shadow border-0' id='$modalId' tabindex='-1' aria-labelledby='{$modalId}-label' aria-hidden='true'>
                            <div class='modal-dialog'>

                                <div class='modal-content'>
                                    <!------- Modal Header ----------> 
                                    <div class='modal-header border-0'>
                                        <h1 class='modal-title fs-5' id='{$modalId}-label'></h1>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>

                                    <!------- Modal Body ----------> 
                                    <div class='modal-body px-5'>
                                        <span class='text-muted fw-bold'>Transaction Details</span><br>
                                        <span class='text-muted'>Timestamp: {$row['transaction_date']}</span><br>
                                        <span class='text-muted'>Account: {$row['staff_acc']}</span><br><br>
                                        
                                        <div class='d-flex justify-content-between'>
                                            <span class='text-muted fw-bold'>Product</span>
                                            <span class='text-muted fw-bolder'>Amount</span>
                                        </div>
                                        $all_products

                                        <div class='justify-content-center'>
                                            <small>
                                                <span class='text-muted'>
                                                - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                                                </span>
                                            </small>
                                        </div>
                                        
                                        <div class='d-flex justify-content-between'>
                                            <small><span class='text-muted'>Total Amount Payable    :</span></small>
                                            <span class='text-muted fw-bolder text-end'>₱ {$row['total_amount']}</span></small>
                                        </div> 
                                        
                                        <div class='justify-content-center'>
                                            <small>
                                                <span class='text-muted'>
                                                - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                                                </span>
                                            </small>
                                        </div> 
                                        <div class='d-flex justify-content-between'>
                                            <small><span class='text-muted'>Cash received:</span></small>
                                            <span class='text-muted text-end'>₱ {$row['cash_received']}</span></small>
                                        </div> 
                                        <div class='d-flex justify-content-between mb-3'>
                                            <small><span class='text-muted'>Change :</span></small>
                                            <span class='text-muted fw-bolder text-end'>₱ {$row['cash_change']}</span></small>
                                        </div> 
                                    </div>      
                                </div>
                            </div>
                        </div>

                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center'>No transaction found</td></tr>";
        }
        echo "</table>";
    }


    static function loadAllStock($products)
    {
        echo "<table class='table table-sm table-hover'>";
        echo '  <tr >
                    <th class="ps-4">
                        <small><span class="fw-bold">Branch Name</span></small>
                    </th>
                    <th class="ps-4">
                        <small><span class="fw-bold">Category</span></small>
                    </th>
                    <th>
                        <small><span class="fw-bold">Item</span></small>
                    </th>
                    <th>
                        <small><span class="fw-bold">Stock</span></small>
                    </th>
                    <th>
                        <small><span class="fw-bold">Status</span></small>
                    </th>
                    <th>
                        <small><span class="fw-bold">Price</span></small>
                    </th>
                    <th colspan=2>
                        <small><span class="fw-bold">Action</span></small>
                    </th>
                </tr>';

        if ($products->num_rows != 0) {
            while ($product = $products->fetch_assoc()) {
                $stockStatus = '';
                $statusClass = '';

                if ($product['productStock'] == 0) {
                    $stockStatus = 'Out of Stock';
                    $statusClass = 'badge bg-secondary';
                } elseif ($product['productStock'] <= 10) {
                    $stockStatus = 'Critical';
                    $statusClass = 'badge bg-danger';
                } else {
                    $stockStatus = 'Good';
                    $statusClass = 'badge bg-success';
                }

                $isArchived = $product['isArchived'] == "0" ? 'Archived' : 'Unarchived';
                $modalId = 'change-modal-' . $product['id'];
                echo
                    "<tr>
                        <td class='align-content-center ps-4'>
                            <small><span>{$product['branchName']}</span></small>
                        </td>
                        <td class='align-content-center ps-4'>
                            <small><span class='badge text-bg-secondary'>{$product['productCategory']}</span></small>
                        </td>
                        <td class='align-content-center'>
                            <span>{$product['productName']}</span>
                        </td>
                        <td class='align-content-center'>
                            <small><span class='fw-bold'>x{$product['productStock']}</span></small>
                        </td>
                        <td class='align-content-center'>
                            <small><span class='{$statusClass}'>{$stockStatus}</span></small>
                        </td>
                        <td class='align-content-center'>
                            <small><span class='fw-bold'>₱ {$product['productPrice']}</span></small>
                        </td>

                        <td class='align-content-center d-flex'>
                            <form method='post' action='backend/redirector.php' onsubmit='return confirmArchive(\"{$isArchived}\")'>
                                <input type='hidden' name='type' value='archive-product'>
                                <input type='hidden' name='id' value='{$product['id']}'>
                                <input type='hidden' name='is_archived' value='{$isArchived}'>
                                <button class='btn custom-remove-btn p-1 me-2' type='submit'>
                                    <small><i class='bi bi-trash-fill me-2'></i></i><span>{$isArchived}</span></small>
                                </button>
                            </form>
                            <button class='btn btn-secondary p-1' type='button' data-bs-toggle='modal' data-bs-target='#$modalId'>
                                <small>Change Price</small>
                            </button>

                            <div class='modal fade' id='$modalId' tabindex='-1' aria-labelledby='$modalId-label'
                                aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='$modalId-label'>Change {$product['productName']} Price</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <form id='$modalId-changeProductPrice' method='POST' action='backend/redirector.php'>
                                                <div class='mb-3'>
                                                    <label for='product_price' class='form-label'>New Price</label>
                                                    <input type='hidden' name='type' value='admin-change-price'>
                                                    <input type='hidden' name='product_id' value='{$product['id']}'>
                                                    <input type='number' class='form-control' id='product_price' name='product_price'
                                                        placeholder='Enter new price' required>
                                                </div>
                                            </form>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-danger' data-bs-dismiss='modal'>Close</button>
                                            <button type='submit' class='btn btn-secondary' form='$modalId-changeProductPrice'>Change Price</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center'>No products found</td></tr>";
        }
        echo "</table>";
    }
}



class BranchClass
{
    static function loadBranchDesign($branch)
    {
        echo "
            <button type='button' class='btn border-0 float-end' data-bs-toggle='modal' data-bs-target='#branch-modal-{$branch['id']}'>
                <i class='bi bi-map-fill fs-2 text-white'></i>
            </button>

            <!-- Modal -->
            <div class='modal fade' id='branch-modal-{$branch['id']}' tabindex='-1' aria-labelledby='branch-modal-label-{$branch['id']}' aria-hidden='true'>
                <div class='modal-dialog modal-lg modal-dialog-centered'>
                    <div class='modal-content'>

                        <div class='modal-header border-0'>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
    
                        <!-- Google Map Embed -->
                        <iframe
                            src='{$branch['pb']}'
                            width='100%' height='450' style='border:0;' allowfullscreen='' loading='lazy'
                            referrerpolicy='no-referrer-when-downgrade'>
                        </iframe>

                        <div class='modal-body border-0 p-4'>

                            <!-- Details -->
                            <h3 class='fs-5 fw-bolder border-bottom border-1 border-success py-3'>Lyza Drugmart {$branch['branch_name']}</h3>
                            <div class='py-3'>
                                <p><i class='bi bi-compass text-success me-3 py-2 fs-5'></i>{$branch['coordinates']}</p>
                                <p><i class='bi bi-signpost-split text-success me-3 py-2 fs-5'></i>{$branch['addressLine']}</p>
                                <p><i class='bi bi-geo-alt text-success me-3 py-2 fs-5'></i>{$branch['city']}, {$branch['province']}</p>
                                <p><i class='bi bi-clock-history text-success me-3 py-2 fs-5'></i>{$branch['operatingHours']}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
    }
    static function loadAllPosProduct($products)
    {
        echo "<table class='table table-sm table-hover'>";
        if ($products->num_rows != 0) {
            while ($product = $products->fetch_assoc()) {
                if ($product['isArchived'] || $product['productStock'] <= 0)
                    continue;

                echo "<tr>";
                echo "<td class='align-content-center ps-4'>";
                echo "<small><span class='badge text-bg-secondary'>" . htmlspecialchars($product['productCategory']) . "</span></small><br>";
                echo "<span class='fw-bold'>" . htmlspecialchars($product['productName']) . "</span>";
                echo "</td>";
                echo "<td class='align-content-center'>";
                echo "<small><span>x" . htmlspecialchars($product['productStock']) . " in stock</span></small>";
                echo "</td>";
                echo "<td class='align-content-center'>";
                echo "<small><span>₱ " . htmlspecialchars($product['productPrice']) . "</span></small>";
                echo "</td>";
                echo "<td class='align-content-center'>";
                echo "<form method='post' action='backend/redirector.php'>";
                echo "<input type='hidden' name='type' value='branch-add-cart'>";
                echo "<input type='hidden' name='product_id' value='" . htmlspecialchars($product['id']) . "'>";
                echo "<input type='hidden' name='branch_id' value='" . htmlspecialchars($product['branchId']) . "'>";
                echo "<input type='hidden' name='product_barcode' value='" . htmlspecialchars($product['barCode']) . "'>";
                echo "<input type='hidden' name='product_name' value='" . htmlspecialchars($product['productName']) . "'>";
                echo "<input type='hidden' name='product_price' value='" . htmlspecialchars($product['productPrice']) . "'>";
                echo "<input type='hidden' name='product_stock' value='" . htmlspecialchars($product['productStock']) . "'>";
                echo "<button class='btn btn-secondary' type='submit'><small>Add</small></button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center'>No products found</td></tr>";
        }
        echo "</table>";
    }


    static function loadAllTransaction($transactions)
    {
        echo "<table class='table table-sm table-hover'>";
        if ($transactions->num_rows != 0) {
            foreach ($transactions as $row) {

                $modalId = 'details-modal-' . $row['id'];
                $products = RequestSQL::getAllProductIDS($row['product_ordered_list']);
                $all_products = '';

                foreach ($products as $product) {
                    $all_products .= "<tr>";
                    $all_products .= "<td class='align-content-center ps-4'><span>" . htmlspecialchars($product['branchName']) . "</span></td>";
                    $all_products .= "<td class='align-content-center'><small><span>" . htmlspecialchars($product['productName']) . "</span></small></td>";
                    $all_products .= "<td class='align-content-center'><small><span>" . htmlspecialchars($product['numberProduct']) . "</span></small></td>";
                    $all_products .= "<td class='align-content-center'><small><span>₱" . htmlspecialchars($product['productPrice']) . "</span></small></td>";
                    $all_products .= "<td class='align-content-center'><small><span>₱" . htmlspecialchars($product['productPrice'] * $product['numberProduct']) . "</span></small></td>";
                    $all_products .= "</tr>";
                }

                echo "
            <tr>
                <td class='align-content-center ps-4'>
                    <small><span class='text-muted'>Transaction ID</span></small><br>
                    <small><span class='fw-bold'>{$row['id']}</span></small>
                </td>
                <td class='align-content-center'>
                    <small><span class='text-muted'>Total</span></small><br>
                    <small><span class='fw-bold'>₱&nbsp;{$row['total_amount']}</span></small>
                </td>
                <td class='align-content-center'>
                    <small><span class='text-muted'>Cash</span></small><br>
                    <small><span class='text-muted'><strong>₱&nbsp;{$row['cash_received']}</strong></span></small>
                </td>
                <td class='align-content-center'>
                    <small><span class='text-muted'>Change</span></small><br>
                    <small><span class='text-muted'>₱&nbsp;{$row['cash_change']}</span></small>
                </td>
                <td class='align-content-center'>
                    <small><span class='text-muted'>Transaction Date</span></small><br>
                    <small>{$row['transaction_date']}</small>
                </td>
                <td class='align-content-center'>
                    <small><span class='text-muted'>Staff</span></small><br>
                    <span class='text-muted'>{$row['staff_acc']}</span>
                </td>
                <td class='align-content-center'>
                    <input type='hidden' id='product_ordered_list' value='" . htmlspecialchars($row['product_ordered_list']) . "'>
                    <button class='btn btn-secondary' type='button' data-bs-toggle='modal' data-bs-target='#$modalId'>
                        <small>Details</small>
                    </button>

                    <div class='modal fade' id='$modalId' tabindex='-1' aria-labelledby='{$modalId}-label' aria-hidden='true'>
                        <div class='modal-dialog modal-lg'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <h5 class='modal-title fw-bold border-start border-3 border-success px-4 mb-3 me-5 align-content-center'
                                        id='productDetailsModalLabel'>
                                        <b>Transaction Details</b>
                                    </h5>
                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                </div>
                                
                                <div class='card card shadow p-2 bg-body-tertiary rounded border-1'>
                                    <div class='modal-body'>
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
                                                $all_products
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>

                </td>
                <td class='align-content-center pe-4'>
                    <a class='link-offset-2 link-underline link-underline-opacity-0 d-flex' 
                        href='#' 
                        onclick=\"printReceipt('{$modalId}', '{$row['total_amount']}', '{$row['cash_received']}', '{$row['cash_change']}',
                            '{$row['staff_acc']}')\">
                        <small><i class='bi bi-printer-fill fw-bolder fs-4 text-secondary'></i></small>
                    </a>
                </td>
            </tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center'>No transaction found</td></tr>";
        }
        echo "</table>";
    }


    static function loadAllStock($products)
    {
        echo "<table class='table table-sm table-hover'>";
        echo '  <tr >
                    <th class="ps-4">
                        <small><span class="fw-bold">Branch Name</span></small>
                    </th>
                    <th class="ps-4">
                        <small><span class="fw-bold">Category</span></small>
                    </th>
                    <th>
                        <small><span class="fw-bold">Item</span></small>
                    </th>
                    <th>
                        <small><span class="fw-bold">Stock</span></small>
                    </th>
                    <th>
                        <small><span class="fw-bold">Status</span></small>
                    </th>
                    <th>
                        <small><span class="fw-bold">Price</span></small>
                    </th>
                    <th>
                        <small><span class="fw-bold">Action</span></small>
                    </th>
                </tr>';

        if ($products->num_rows != 0) {
            while ($product = $products->fetch_assoc()) {
                $stockStatus = '';
                $statusClass = '';

                if ($product['productStock'] == 0) {
                    $stockStatus = 'Out of Stock';
                    $statusClass = 'badge bg-secondary';
                } elseif ($product['productStock'] <= 10) {
                    $stockStatus = 'Critical';
                    $statusClass = 'badge bg-danger';
                } else {
                    $stockStatus = 'Good';
                    $statusClass = 'badge bg-success';
                }
                $isArchived = $product['productPrice'] ? 'Archived' : 'Unarchived';

                $modalId = 'details-modal-' . $product['id'];
                echo
                    "<tr>
                        <td class='align-content-center ps-4'>
                            <span>{$product['branchName']}</span>
                        </td>
                        <td class='align-content-center ps-4'>
                            <small><span class='badge text-bg-secondary'>{$product['productCategory']}</span></small>
                        </td>
                        <td class='align-content-center'>
                            <span>{$product['productName']}</span>
                        </td>
                        <td class='align-content-center'>
                            <small><span class='fw-bold'>x{$product['productStock']}</span></small>
                        </td>
                        <td class='align-content-center'>
                            <small><span class='{$statusClass}'>{$stockStatus}</span></small>
                        </td>
                        <td class='align-content-center'>
                            <small><span class='fw-bold'>₱ {$product['productPrice']}</span></small>
                        </td>
                        <td class='align-content-center'>
                            <button class='btn btn-sm btn-secondary rounded' type='button' data-bs-toggle='modal'
                                data-bs-target='#$modalId'>Restock</button>

                            <div class='modal fade' id='$modalId' tabindex='-1' aria-labelledby='$modalId-label' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='$modalId-label'>Restock {$product['productName']} Product</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <form id='restockForm' method='post' action='backend/redirector.php'>
                                                <div class='mb-3'>
                                                    <label for='quantity' class='form-label'>Quantity</label>
                                                    <input type='hidden' name='type' value='branch-stock-item'>
                                                    <input type='hidden' name='user_id' value='{$_SESSION['account']['id']}'>
                                                    <input type='hidden' name='product_id' value='{$product['id']}'>
                                                    <input type='hidden' name='branch_id' value='{$product['branchId']}'>
                                                    <input type='number' class='form-control' id='quantity' name='quantity' min='1' required>
                                                </div>
                                                <button type='submit' class='btn btn-secondary'>Restock</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class='btn btn-sm btn-secondary rounded' type='button' data-bs-toggle='modal'
                                data-bs-target='#u$modalId'>Unstock</button>

                            <div class='modal fade' id='u$modalId' tabindex='-1' aria-labelledby='u$modalId-label' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='u$modalId-label'>Unstock {$product['productName']} Product</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <form id='unrestockForm' method='post' action='backend/redirector.php'>
                                                <div class='mb-3'>
                                                    <label for='quantity' class='form-label'>Quantity</label>
                                                    <input type='hidden' name='type' value='branch-unstock-item'>
                                                    <input type='hidden' name='user_id' value='{$_SESSION['account']['id']}'>
                                                    <input type='hidden' name='product_id' value='{$product['id']}'>
                                                    <input type='hidden' name='branch_id' value='{$product['branchId']}'>
                                                    <input type='number' class='form-control' id='quantity' name='quantity' min='1' max='{$product['productStock']}' required>
                                                </div>
                                                <button type='submit' class='btn btn-secondary'>Unstock</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>

                    </tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center'>No products found</td></tr>";
        }
        echo "</table>";
    }


    static function loadPaginator($currentPage, $totalPages, $name)
    {
        echo '<nav class="d-flex justify-content-between">';
        echo '<ul class="pagination custom-pagination">';
        if ($currentPage > 1) {
            echo "
                <li class='page-item'>
                    <a class='page-link' href='#' data-page='" . ($currentPage - 1) . "'>
                        <i class='bi bi-arrow-left'></i>
                    </a>
                </li>";
        } else {
            echo
                "<li class='page-item disabled'>
                    <a class='page-link' href='#'>
                        <i class='bi bi-arrow-left'></i>
                    </a>
                </li>";
        }

        for ($i = 1; $i <= $totalPages; $i++) {
            $activeClass = ($i == $currentPage) ? 'active' : '';
            echo
                "<form action='backend/redirector.php' method='POST'>
                    <li class='page-item $activeClass'>
                        <input type='hidden' name='page' value='{$i}'>
                        <input type='hidden' name='type' value='$name'>
                        <button class='page-link' type='submit'>$i</button>
                    </li>
                </form>";
        }
        if ($currentPage < $totalPages) {
            echo
                "<li class='page-item'>
                    <a class='page-link' href='#' data-page='" . ($currentPage + 1) . "'>
                        <i class='bi bi-arrow-right'></i>
                    </a>
                </li>";
        } else {
            echo
                "<li class='page-item disabled'>
                    <a class='page-link' href='#'>
                        <i class='bi bi-arrow-right'></i>
                    </a>
                </li>";
        }
        echo '</ul>';
        // echo "<button class='btn btn-secondary' type='submit'><small>SUBMIT</small></button>";
        echo '</nav>';
    }
}


?>