<?php
session_start();
require_once "backend/request.php";
require_once "backend/functions.php";

RequestSQL::isOnline();
if (!isset($_SESSION['account'])) {
    header('Location: index.php');
    exit;
}

include "branch/branch_header.php";
include "branch/branch_navigation.php";
require_once("modals/modals.php");
if (isset($_SESSION['success-message'])) {
    echo "<script>showSuccessModal('{$_SESSION['success-message']}');</script>";
    unset($_SESSION['success-message']);
} elseif (isset($_SESSION['error-message'])) {
    echo "<script>showErrorModal('{$_SESSION['error-message']}');</script>";
    unset($_SESSION['error-message']);
}
?>

<!-- Main Body -------------------------------------------------------------------------------------------------------------->

<section class="container-fluid custom-main-wrapper col-md-12 overflow-y-scroll">
    <style>
        .border-dark-green {
            background: #56AB91;
        }

        .border-dark-green:hover {
            background: #369B71;
        }
    </style>
    <!-- Main Layout -->
    <div class="container">
        <div class="d-flex my-3">
            <?php include "branch/branch_sidebar.php"; ?>

            <?php
            $page = isset($_GET['page']) ? $_GET['page'] : 'pos';
            switch ($page) {
                case 'transactions':
                    include "branch/branch_transaction.php";
                    break;
                case 'stocks':
                    include "branch/branch_stock.php";
                    break;
                case 'pos':
                default:
                    include "branch/branch_pos.php";
                    break;
            }
            ?>
        </div>
    </div>
</section>

<?php include "branch/branch_footer.php"; ?>