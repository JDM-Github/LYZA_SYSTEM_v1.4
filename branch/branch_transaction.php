<div class="content ms-3 col-sm-11 content-section" id="transactions">

    <!-- Search / Category Navigation -->
    <div class="card shadow p-0 bg-body-tertiary rounded border-0 mb-3">
        <div class="input-group input-group border-0 justify-content-between p-2 pb-0">
            <p class=" fw-bold border-start border-3 border-success px-4 mb-3 me-5 m-1 align-content-center">
                Recent Transactions
            </p>
            <form action="backend/redirector.php" method="post">
                <input type="hidden" name="type" value="upload-transaction">
                <input type="hidden" name="direct" value="branch.php?page=transactions">
                <button class="btn btn-secondary m-1 mb-3 rounded" type="submit">
                    Upload Offline Transaction
                </button>
            </form>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="card card shadow p-3 bg-body-tertiary rounded border-0">
        <div class="input-group input-group-sm border-0 align-content-center">
            <p class=" fw-bold border-start border-3 border-success px-4 mb-3 me-5 align-content-center">Filter</p>

            <form action="" class="form-control border-0 d-flex bg-body-tertiary" method="post">
                <?php
                $staffs = RequestSQL::getAllStaff();
                $sessionDate = '';
                $sessionGroup = '';
                $sessionOrder = '';
                $sessionStaff = '';

                $branch = RequestSQL::getSession('branch-transaction');
                if ($branch) {
                    $sessionDate = $branch['date'];
                    $sessionGroup = $branch['groupBy'];
                    $sessionOrder = $branch['order'];
                    $sessionStaff = $branch['staff'];
                }
                $selectedDate = isset($_POST['item-date']) ? $_POST['item-date'] : $sessionDate;
                $selectedGroup = isset($_POST['group-by']) ? $_POST['group-by'] : $sessionGroup;
                $selectedOrder = isset($_POST['order-by']) ? $_POST['order-by'] : $sessionOrder;
                $selectedStaff = isset($_POST['staff']) ? $_POST['staff'] : $sessionStaff;


                function isSelected($option, $selectedValue)
                {
                    return $option === $selectedValue ? 'selected' : '';
                }
                ?>
                <input class="form-control rounded mb-3 me-3" type="date" name="item-date"
                    value="<?php echo $selectedDate; ?>">
                <select class="form-select rounded mb-3 me-3" name="group-by" id="group-by">
                    <option value="">-- Group By --</option>
                    <option value="daily" <?php echo isSelected('daily', $selectedGroup); ?>>Daily</option>
                    <option value="weekly" <?php echo isSelected('weekly', $selectedGroup); ?>>Weekly</option>
                    <option value="monthly" <?php echo isSelected('monthly', $selectedGroup); ?>>Monthly</option>
                    <option value="semi-annually" <?php echo isSelected('semi-annually', $selectedGroup); ?>>
                        Semi-Annually</option>
                    <option value="annually" <?php echo isSelected('annually', $selectedGroup); ?>>Annually</option>
                </select>

                <select class="form-select rounded mb-3 me-3" name="order-by" id="order-by">
                    <option value="asc" <?php echo isSelected('asc', $selectedOrder); ?>>ASCENDING</option>
                    <option value="desc" <?php echo isSelected('desc', $selectedOrder); ?>>DESCENDING</option>
                </select>

                <select class="form-select rounded mb-3 me-3" name="staff" id="staff">
                    <option value="">-- Select Staff --</option>
                    <?php
                    if ($staffs) {
                        while ($staff = $staffs->fetch_assoc()) {
                            $staffID = $staff['id'];
                            $staffName = $staff['userName'];
                            echo "<option value='{$staffID}' " . isSelected($staffID, $selectedStaff) . ">{$staffName}</option>";
                        }
                    }
                    ?>
                </select>

                <button class="btn btn-secondary mb-3 rounded" type="submit">Search</button>
            </form>

        </div>

        <div>
            <?php
            $data = RequestSQL::getAllTransaction(
                $selectedDate,
                $selectedGroup,
                $selectedOrder,
                $selectedStaff,
                'branch',
                RequestSQL::getSession('account')['assignedBranch']
            );
            $transactions = $data['result'];
            $currentPage = $data['page'];
            $totalPages = $data['total'];
            BranchClass::loadAllTransaction($transactions);
            BranchClass::loadPaginator($currentPage, $totalPages, 'branch-transaction-page');
            ?>
        </div>

    </div>
</div>


<script>
    function printReceipt(modalId, total, cash, change, staff) {
        const modalContent = document.querySelector(`#${modalId} .modal-body`).innerHTML;

        const printWindow = window.open('', '_blank', 'width=600,height=400');
        printWindow.document.open();
        printWindow.document.write(`
        <html>
            <head>
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

                    .receipt th, .receipt td {
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
                <div class="receipt">
                    <h2>Lyza Drugmart</h2>
                    <p>Thank you for your purchase!</p>
                    <p><strong>Staff:</strong> ${staff}</p>
                    <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                    <hr>
                    
                    <table>
                        ${modalContent}
                    </table>

                    <div class="total-section">
                        <div class="total-item">
                            <span>Total:</span>
                            <span>₱${total}</span>
                        </div>
                        <div class="total-item">
                            <span>Cash Received:</span>
                            <span>₱${cash}</span>
                        </div>
                        <div class="total-item">
                            <span>Change:</span>
                            <span>₱${change}</span>
                        </div>
                    </div>

                    <hr>
                    <p>Thank you for on Lyza Store!</p>
                    <p>Visit again!</p>
                </div>
            </body>
        </html>
    `);
        printWindow.document.close();
        printWindow.print();
        printWindow.close();
    }

</script>
<?php include_once "modals/branch/transaction_model.php" ?>