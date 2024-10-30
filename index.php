<?php
session_start();
require_once "backend/request.php";
require_once "backend/functions.php";

$_SESSION['cache_account'] = [];
if (file_exists("json/cache_account.json")) {
    $session_data = json_decode(file_get_contents("json/cache_account.json"), true);
    $_SESSION['cache_account'] = $session_data;
}
$_SESSION['online'] = RequestSQL::isOffline();


$_SESSION['account'] = null;
unset($_SESSION['account']);

$_SESSION['branch-cart-product'] = [];
unset($_SESSION['branch-cart-product']);

include "client/client_header.php";
include "client/client_navigation.php";


require_once("modals/modals.php");
if (isset($_SESSION['success-message'])) {
    echo "<script>showSuccessModal('{$_SESSION['success-message']}');</script>";
    unset($_SESSION['success-message']);
} elseif (isset($_SESSION['error-message'])) {
    echo "<script>showErrorModal('{$_SESSION['error-message']}');</script>";
    unset($_SESSION['error-message']);
}
?>

<section class="overflow-y-scroll">

    <!-- Banner ------------------------------------------------------------------------------------------------------------>
    <div class="banner-home mb-5">
        <img src="img/SamplePhoto.jpg">
        <div class="container banner-text mt-3">

            <!----- Banner Title/Label ----->
            <div class="mt-5">
                <h2 class="fw-bold display-2 py-4">Lyza Drugmart</h2>
                <p class="text-white me-5" style="max-width: 430px;">
                    Lyza Drugmart, your trusted local pharmacy in <strong>Sto. Tomas, Batangas,</strong> offers
                    affordable branded and generic pharmaceutical products to meet the community's healthcare needs.
                </p>
                <h5>
                    <span class="badge custom-btn-success p-2 fw-light ">
                        <i class="bi bi-clock-fill"></i>&nbsp;8:00 AM - 8:00 PM
                    </span>&nbsp;

                    <span class="badge custom-btn-success p-2 fw-light">
                        <i class="bi bi-geo-alt-fill"></i>&nbsp;Visit Us
                    </span>
                </h5>
            </div>
        </div>

    </div>
    </div>

    <!-- Content ------------------------------------------------------------------------------------------------------------>

    <div class="container mt-0">

        <!----- Product Categories ----->
        <h2 class=" fw-bold fs-3 border-start border-3 border-success px-4 mb-5 mt-0">
            Discover our Branches
        </h2>

        <!----- Branches ----->
        <div class="row mb-5">
            <?php
            $branches = RequestSQL::getAllBranches();
            if ($branches->num_rows != 0) {
                foreach ($branches as $branch) {
                    if ($branch['branch_name'] === 'All Branch')
                        continue;
                    ?>
            <div class="col-sm-6">
                <div class="card shadow bg-body-tertiary rounded border-0 custom-card-branch mb-3"
                    style="min-width: 410px; background-image: url('img/<?php echo $branch['branchImg'] ?>')">
                    <div class="custom-branch-text d-flex">
                        <div class="align-content-end px-4 pb-2">
                            <span class="badge bg-white text-success">Lyza Drugmart</span>
                            <h4 class="fw-bold fs-1 text-white">
                                <?php echo $branch['branch_name'] ?>
                            </h4>
                        </div>
                        <div class="flex-fill align-content-end pb-2 pe-2">
                            <?php BranchClass::loadBranchDesign($branch); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            }
            ?>
        </div>
        <!----- Product Categories ----->
        <h2 class=" fw-bold fs-3 border-start border-3 border-success px-4">
            Explore our Available Products
        </h2>

        <!----- Search Bar ----->
        <form method="post">
            <?php
            $client = RequestSQL::getSession('client');
            $sessionSearch = '';
            if ($client)
                $sessionSearch = $client['search'];

            $searchValue = isset($_POST['search-value']) ? $_POST['search-value'] : $sessionSearch;
            ?>

            <div class="input-group input-group-lg rounded-pill border-0 mt-5 mb-2">
                <input type="text" id="search-input" class="form-control rounded-start-pill display-5"
                    name='search-value' placeholder="Search Product..." aria-describedby="search-button-home"
                    value="<?php echo $searchValue ?>">
                <button class="btn rounded-end-circle custom-btn-success" type="submit" id="search-button-home">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
        <ul class="nav nav-pills mb-3 custom-pills">
            <?php
            $categories = RequestSQL::getAllCategories();
            $selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;

            $isActive = $selectedCategory ? '' : 'active';
            echo "
            <li class='nav-item'>
                <a class='nav-link $isActive' href='index.php'>All</a>
            </li>
            ";

            if ($categories->num_rows != 0) {
                foreach ($categories as $category) {
                    $isActive = $selectedCategory === $category['category_name'] ? 'active' : '';
                    echo "
                    <li class='nav-item'>
                        <a class='nav-link $isActive' href='index.php?category={$category['category_name']}#categories'>{$category['category_name']}</a>
                    </li>
                    ";
                }
            }
            ?>
        </ul>

        <!----- Product Grid Display ----->
        <div class="row g-3 mb-5" id="product-grid">
            <?php
            $product = RequestSQL::getAllProduct('client', $selectedCategory, null, $searchValue, null, null, null)['result'];

            while ($row = $product->fetch_assoc()): ?>
            <div class="col-md-3">

                <div class="card shadow bg-body-tertiary rounded border-0">

                    <img class="card-image rounded" src="img/<?php echo $row['productImage']; ?>" height="250"
                        width="auto" alt="<?php echo $row['productName']; ?>">

                    <div class="card-body">
                        <span class="badge text-bg-secondary p-1 mb-1">
                            <?php echo $row['productCategory']; ?>
                        </span>
                        <p class="m-0">
                            <?php echo $row['productName']; ?>
                        </p>
                        <div class="d-flex justify-content-between">
                            <p class="fw-bold m-0">₱
                                <?php echo $row['productPrice']; ?>
                            </p>
                            <p class="m-0">
                                <?php echo $row['productStock']; ?> item/s
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <?php endwhile; ?>
        </div>
    </div>

</section>

<!-- Banner-Foot --------------------------------------------------------------------------------------------------------->
<?php include "client/client_footer.php"; ?>