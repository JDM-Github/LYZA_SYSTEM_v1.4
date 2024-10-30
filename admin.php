<?php
session_start();
require_once "backend/request.php";
require_once "backend/functions.php";
$_SESSION['online'] = RequestSQL::isOffline();
if (!$_SESSION['online']) {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['account']) || $_SESSION['account']['isAdmin'] == '0') {
    header('Location: index.php');
    exit;
}

include "admin/admin_header.php";
include "admin/admin_navigation.php";
require_once("modals/modals.php");
if (isset($_SESSION['success-message'])) {
    echo "<script>showSuccessModal('{$_SESSION['success-message']}');</script>";
    unset($_SESSION['success-message']);
} elseif (isset($_SESSION['error-message'])) {
    echo "<script>showErrorModal('{$_SESSION['error-message']}');</script>";
    unset($_SESSION['error-message']);
}
?>
<!-- Main Body --------------------------------------------------------------------------------------------------------->

<section class="container-fluid custom-main-wrapper col-md-12 overflow-y-scroll">

    <!-- Main Layout -->
    <div class="container">
        <div class="custom-content-wrapper my-3">

            <!-- Sidebar -->
            <?php include "admin/admin_sidebar.php"; ?>

            <?php
            $page = isset($_GET['page']) ? $_GET['page'] : 'transaction';
            switch ($page) {
                case 'transaction-report':
                    include "admin/admin_transactions.php";
                    break;
                case 'stock-report':
                    include "admin/admin_stock_reports.php";
                    break;
                case 'product-report':
                    include "admin/admin_product_reports.php";
                    break;
                case 'accounts':
                    include "admin/admin_accounts.php";
                    break;
                case 'transaction':
                default:
                    include "admin/admin_dashboard.php";
                    break;
            }
            ?>


        </div>
    </div>
</section>


<!-- Footer ----------------------------------------------------------------------------------------------------------->

<?php include "admin/admin_footer.php"; ?>