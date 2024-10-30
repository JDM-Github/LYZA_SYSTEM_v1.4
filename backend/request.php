<?php

require_once("backend/config.php");
require_once("backend/database.php");
require_once("backend/session.php");

class RequestSQL
{
    static function isOffline()
    {
        return !(@fsockopen('www.google.com', 80) == null);
    }

    static function isOnline()
    {
        if (isset($_SESSION['online'], $_SESSION['last_online_check'])) {
            $lastCheck = $_SESSION['last_online_check'];

            if ((time() - $lastCheck) < ONLINE_CHECK_INTERVAL) {
                return $_SESSION['online'];
            }
        }

        $isOnline = !(@fsockopen('www.google.com', 80) == null);
        $_SESSION['online'] = $isOnline;
        $_SESSION['last_online_check'] = time();
        return $isOnline;
    }

    // static function checkOnline()
    // {


    //     if (isOffline("google.com")) {
    //         return false;
    //     } else {
    //         return true;
    //     }
    // }
    static function debugAlert($data)
    {
        echo "<script>alert('" . $data . "')</script>";
    }

    static function getCountQuery($countQuery, $limit)
    {
        $database = new MySQLDatabase();
        $countResult = $database->query($countQuery);
        $totalRecords = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalRecords / $limit);

        return $totalPages;
    }

    static function getAllCategories()
    {
        $database = new MySQLDatabase();
        return $database->query("SELECT DISTINCT productCategory AS category_name FROM products");
    }

    static function getAllBranches()
    {
        $database = new MySQLDatabase();
        return $database->query("SELECT DISTINCT branchName AS branch_name, id, branchImg, coordinates, addressLine, city, province, operatingHours FROM branch");
    }

    static function getAllStaff($isAdmin = false, $assigned = null)
    {
        $database = new MySQLDatabase();
        $query = "
            SELECT DISTINCT u.id AS id, u.userName
            AS userName, u.firstName AS firstName, u.lastName AS lastName FROM staff s JOIN users u ON s.userId = u.id
            WHERE u.isAdmin = FALSE";
        if (!$isAdmin)
            $query .= " AND u.assignedBranch = 1";

        if ($assigned)
            $query .= " AND u.assignedBranch = $assigned";

        return $database->query($query);
    }

    static function getAllRevenue($groupBy = null)
    {
        $database = new MySQLDatabase();
        $query = "SELECT SUM(t.totalPrice) AS total_price FROM transactions t WHERE 1=1";
        if ($groupBy) {
            switch ($groupBy) {
                case 'daily':
                    $query .= " AND DATE(t.createdAt) = DATE(NOW())";
                    break;
                case 'weekly':
                    $query .= " AND WEEK(t.createdAt) = WEEK(NOW())";
                    break;
                case 'monthly':
                    $query .= " AND MONTH(t.createdAt) = MONTH(NOW())";
                    break;
                case 'semi-annually':
                    $query .= " AND QUARTER(t.createdAt) = QUARTER(NOW())";
                    break;
                case 'annually':
                    $query .= " AND YEAR(t.createdAt) = YEAR(NOW())";
                    break;
            }
        }
        $query .= " LIMIT 1";
        return $database->query($query);
    }

    static function getLowStockItems()
    {
        $database = new MySQLDatabase();
        $query = "SELECT COUNT(*) AS lowStockCount FROM products WHERE productStock <= 10";
        $result = $database->query($query);

        if ($result) {
            $row = $result->fetch_assoc();
            return (int) $row['lowStockCount'];
        } else {
            return 0;
        }
    }

    static function getOutOfStockItems()
    {
        $database = new MySQLDatabase();
        $query = "SELECT COUNT(*) AS lowStockCount FROM products WHERE productStock = 0";
        $result = $database->query($query);

        if ($result) {
            $row = $result->fetch_assoc();
            return (int) $row['lowStockCount'];
        } else {
            return 0;
        }
    }

    static function hasSession($name)
    {
        $session = new Session();
        return $session->has($name);
    }

    static function unsetSession($name)
    {
        $session = new Session();
        return $session->remove($name);
    }

    static function setSession($name, $data)
    {
        $session = new Session();
        $session->set($name, $data);
    }

    static function getSession($data)
    {
        $session = new Session();
        if (!$session->has($data))
            return null;
        return $session->get($data);
    }

    static function setBranchPage($br, $category = null, $status = null, $search = null, $archived = null, $branch_target = null)
    {
        $session = new Session();

        if ($session->has($br)) {
            $branch = ($session->get($br));
            if (
                $branch['category'] != $category ||
                $branch['status'] != $status ||
                $branch['search'] != $search ||
                $branch['archived'] != $archived ||
                $branch['branch'] != $archived
            ) {
                $session->set($br . '-page', 1);
            }
        }
        $session->set($br, [
            'category' => $category,
            'status' => $status,
            'search' => $search,
            'archived' => $archived,
            'branch' => $branch_target,
        ]);
    }

    static function getAllProductIDS($ids)
    {
        $productOrderedIds = json_decode($ids, true)['id']; // [1, 2]
        $parameters = str_repeat('?,', count($productOrderedIds) - 1) . '?';

        $database = new MySQLDatabase();

        $query = "
            SELECT
                po.id,
                b.branchName AS branchName,
                p.productName,
                po.numberProduct,
                p.productPrice
            FROM productOrdered po
            JOIN products p ON po.productId = p.id
            JOIN branch b ON p.branchId = b.id
            WHERE po.id IN ($parameters)
        ";

        $connection = $database->getConnection();
        $result = $connection->execute_query($query, $productOrderedIds);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        return $data;
    }

    static function getMostSoldProducts($groupBy)
    {
        $database = new MySQLDatabase();
        $query = "
            SELECT p.productName, SUM(po.numberProduct) AS totalSold
            FROM products p
            JOIN productOrdered po ON p.id = po.productId
            WHERE 1=1";

        if ($groupBy) {
            switch ($groupBy) {
                case 'daily':
                    $query .= " AND DATE(po.createdAt) = DATE(NOW())";
                    break;
                case 'weekly':
                    $query .= " AND WEEK(po.createdAt) = WEEK(NOW())";
                    break;
                case 'monthly':
                    $query .= " AND MONTH(po.createdAt) = MONTH(NOW())";
                    break;
                case 'semi-annually':
                    $query .= " AND QUARTER(po.createdAt) = QUARTER(NOW())";
                    break;
                case 'annually':
                    $query .= " AND YEAR(po.createdAt) = YEAR(NOW())";
                    break;
            }
        }

        $query .= " GROUP BY p.id ORDER BY totalSold DESC LIMIT 5";
        return $database->query($query);
    }

    static function getAllProductForCache($branch_target)
    {
        $session = new Session();
        $database = new MySQLDatabase();

        $query = "SELECT p.*, b.branchName FROM products p 
            JOIN branch b ON p.branchId = b.id WHERE 1=1";

        if ($branch_target) {
            $branch_target = $database->escape($branch_target);
            $query .= " AND b.branchName = '$branch_target'";
        }

        $result = $database->query($query);
        $productCache = [];

        while ($row = $result->fetch_assoc()) {
            $productCache[] = [
                'id' => $row['id'],
                'branchId' => $row['branchId'],
                'barCode' => $row['barCode'],
                'productName' => $row['productName'],
                'productPrice' => $row['productPrice'],
                'productStock' => $row['productStock'],
                'productCategory' => $row['productCategory'],
                'productImage' => $row['productImage'],
                'productDescription' => $row['productDescription'],
                'isArchived' => $row['isArchived'],
                'createdAt' => $row['createdAt'],
                'branchName' => $row['branchName'],
            ];
        }
        if (!empty($branch_target)) {
            $session->set("product-cache-{$branch_target}", $productCache);
        }
    }

    static function getProductsFromCache($branch_target)
    {
        $session = new Session();
        $cachedProducts = $session->get("product-cache-{$branch_target}");
        if ($cachedProducts) {
            return $cachedProducts;
        } else {
            return [];
        }
    }

    static function getAllProduct(
        $branch = 'branch-pos',
        $category = null,
        $status = null,
        $search = null,
        $archived = null,
        $branch_target = null,
        $limit = 7
    ) {
        $session = new Session();
        $database = new MySQLDatabase();

        RequestSQL::setBranchPage($branch, $category, $status, $search, $archived, $branch_target);
        $page = $session->getOrSet($branch . '-page', 1);
        if ($limit)
            $target_limit = $limit;
        else
            $target_limit = 999;
        $offset = ($page - 1) * $target_limit;

        $query = "SELECT p.*, b.branchName FROM products p 
            JOIN branch b ON p.branchId = b.id WHERE 1=1";
        $countQuery = "SELECT COUNT(*) AS total FROM products p JOIN branch b ON p.branchId = b.id WHERE 1=1";
        $additionalQuery = "";

        if ($category && $category != '-- Select Category --') {
            $category = $database->escape($category);
            $additionalQuery .= " AND productCategory = '$category'";
        }

        if ($branch_target) {
            $branch_target = $database->escape($branch_target);
            $additionalQuery .= " AND b.branchName = '$branch_target'";
        }

        if ($archived && $archived === 'archived')
            $additionalQuery .= " AND isArchived = TRUE";
        else
            $additionalQuery .= " AND isArchived = FALSE";


        if ($search) {
            $search = $database->escape($search);
            $additionalQuery .= " AND (productName LIKE '%$search%' OR productDescription LIKE '%$search%')";
        }

        if ($status) {
            if ($status == 'good') {
                $additionalQuery .= " AND productStock > 10";
            } elseif ($status == 'critical') {
                $additionalQuery .= " AND productStock <= 10 AND productStock > 0";
            } elseif ($status == 'out-of-stock') {
                $additionalQuery .= " AND productStock = 0";
            }
        }
        $query .= $additionalQuery;
        $countQuery .= $additionalQuery;

        if ($limit)
            $query .= " LIMIT $target_limit OFFSET $offset";

        $result = $database->query($query);
        $totalPages = RequestSQL::getCountQuery($countQuery, $target_limit);

        return [
            'result' => $result,
            'page' => $page,
            'total' => $totalPages
        ];
    }

    static function getAllTransaction(
        $date = null,
        $groupBy = null,
        $order = null,
        $staff = null,
        $target = 'branch',
        $branch_target = null
    ) {
        $session = new Session();
        $database = new MySQLDatabase();

        if ($session->has($target . '-transaction')) {
            $branch = ($session->get($target . '-transaction'));
            if (
                $branch['date'] != $date ||
                $branch['branch'] != $branch_target ||
                $branch['groupBy'] != $groupBy ||
                $branch['order'] != $order ||
                $branch['staff'] != $staff
            ) {
                $session->set($target . '-transaction-page', 1);
            }
        }
        $session->set($target . '-transaction', [
            'date' => $date,
            'branch' => $branch_target,
            'groupBy' => $groupBy,
            'order' => $order,
            'staff' => $staff,
        ]);
        $page = $session->getOrSet($target . '-transaction-page', 1);
        $limit = 7;
        $offset = ($page - 1) * $limit;

        $query = "
            SELECT 
                t.id,
                t.branchId,
                t.totalPrice AS total_amount,
                t.cashPrice AS cash_received,
                t.changePrice AS cash_change,
                t.createdAt AS transaction_date,
                s.userName AS staff_acc,
                t.productOrderedIds AS product_ordered_list
            FROM transactions AS t
            JOIN staff st ON t.staffId = st.id
            JOIN users s ON st.userId = s.id
            WHERE 1=1";

        $countQuery = "
            SELECT COUNT(*) AS total 
            FROM transactions AS t
            JOIN staff st ON t.staffId = st.id
            JOIN users s ON st.userId = s.id
            WHERE 1=1";
        $additionalQuery = "";

        if ($branch_target) {
            $branch_target = $database->escape($branch_target);
            $additionalQuery .= " AND t.branchId = '$branch_target'";
        }

        if ($staff && $staff != '-- Select Staff --') {
            $staff = $database->escape($staff);
            $additionalQuery .= " AND t.staffId = '$staff'";
        }

        if ($date) {
            $additionalQuery .= " AND DATE(t.createdAt) = '$date'";
        }

        if ($groupBy) {
            switch ($groupBy) {
                case 'daily':
                    $additionalQuery .= " AND DATE(t.createdAt) = DATE(NOW())";
                    break;
                case 'weekly':
                    $additionalQuery .= " AND WEEK(t.createdAt) = WEEK(NOW())";
                    break;
                case 'monthly':
                    $additionalQuery .= " AND MONTH(t.createdAt) = MONTH(NOW())";
                    break;
                case 'semi-annually':
                    $additionalQuery .= " AND QUARTER(t.createdAt) = QUARTER(NOW())";
                    break;
                case 'annually':
                    $additionalQuery .= " AND YEAR(t.createdAt) = YEAR(NOW())";
                    break;
            }
        }

        $query .= $additionalQuery;
        $countQuery .= $additionalQuery;
        $query .= " ORDER BY t.createdAt $order LIMIT $limit OFFSET $offset";
        $result = $database->query($query);
        $totalPages = RequestSQL::getCountQuery($countQuery, $limit);

        return [
            'result' => $result,
            'page' => $page,
            'total' => $totalPages
        ];
    }



    // -----------------------------------------------------------------------------------
    // ADMIN REQUEST
    // -----------------------------------------------------------------------------------

    static function getAllStockHistory($branch_target = null, $staff = null, $groupBy = null)
    {
        $session = new Session();
        $database = new MySQLDatabase();

        if ($session->has('admin-stock-history')) {
            $branch = ($session->get('admin-stock-history'));
            if (
                $branch['branch'] != $branch_target ||
                $branch['staff'] != $staff ||
                $branch['groupBy'] != $groupBy
            ) {
                $session->set('admin-stock-history-page', 1);
            }
        }
        $session->set('admin-stock-history', [
            'branch' => $branch_target,
            'staff' => $staff,
            'groupBy' => $groupBy,
        ]);

        $page = $session->getOrSet('admin-stock-history-page', 1);
        $limit = 7;
        $offset = ($page - 1) * $limit;

        $query = "
            SELECT 
                s.id AS id,
                s.staffId AS staffId,
                u.firstName AS firstName,
                u.lastName AS lastName, 
                s.quantity AS quantity,
                p.productName AS productName,
                b.branchName AS branchName
            FROM stockHistory s
            JOIN users u ON u.id = s.staffId
            JOIN products p ON p.id = s.productId
            JOIN branch b ON b.id = s.branchId
            WHERE 1=1";

        $countQuery = "
            SELECT COUNT(*) AS total 
            FROM stockHistory s
            JOIN users u ON u.id = s.staffId
            JOIN products p ON p.id = s.productId
            JOIN branch b ON b.id = s.branchId
            WHERE 1=1";

        if ($branch_target) {
            $query .= " AND s.branchId = '$branch_target'";
            $countQuery .= " AND s.branchId = '$branch_target'";
        }

        if ($staff) {
            $staff = $database->escape($staff);
            $query .= " AND s.staffId = '$staff'";
            $countQuery .= " AND s.staffId = '$staff'";
        }

        if ($groupBy) {
            switch ($groupBy) {
                case 'daily':
                    $query .= " AND DATE(s.createdAt) = DATE(NOW())";
                    $countQuery .= " AND DATE(s.createdAt) = DATE(NOW())";
                    break;
                case 'weekly':
                    $query .= " AND WEEK(s.createdAt) = WEEK(NOW())";
                    $countQuery .= " AND WEEK(s.createdAt) = WEEK(NOW())";
                    break;
                case 'monthly':
                    $query .= " AND MONTH(s.createdAt) = MONTH(NOW())";
                    $countQuery .= " AND MONTH(s.createdAt) = MONTH(NOW())";
                    break;
                case 'semi-annually':
                    $query .= " AND QUARTER(s.createdAt) = QUARTER(NOW())";
                    $countQuery .= " AND QUARTER(s.createdAt) = QUARTER(NOW())";
                    break;
                case 'annually':
                    $query .= " AND YEAR(s.createdAt) = YEAR(NOW())";
                    $countQuery .= " AND YEAR(s.createdAt) = YEAR(NOW())";
                    break;
            }
        }

        $result = $database->query($query . " ORDER BY s.createdAt DESC LIMIT $limit OFFSET $offset");
        $totalPages = RequestSQL::getCountQuery($countQuery, $limit);

        return [
            'result' => $result,
            'page' => $page,
            'total' => $totalPages
        ];
    }

    static function getAllStockAdmin($branch_target = null, $staff = null, $groupBy = null)
    {
        $database = new MySQLDatabase();

        $query = "
            SELECT 
                s.id AS id,
                s.staffId AS staffId,
                u.firstName AS firstName,
                u.lastName AS lastName, 
                s.quantity AS quantity,
                p.productName AS productName,
                b.branchName AS branchName
            FROM stockHistory s
            JOIN users u ON u.id = s.staffId
            JOIN products p ON p.id = s.productId
            JOIN branch b ON b.id = s.branchId
            WHERE 1=1";

        $countQuery = "
            SELECT COUNT(*) AS total 
            FROM stockHistory s
            JOIN users u ON u.id = s.staffId
            JOIN products p ON p.id = s.productId
            JOIN branch b ON b.id = s.branchId
            WHERE 1=1";

        if ($branch_target) {
            $query .= " AND s.branchId = '$branch_target'";
            $countQuery .= " AND s.branchId = '$branch_target'";
        }

        if ($staff) {
            $staff = $database->escape($staff);
            $query .= " AND s.staffId = '$staff'";
            $countQuery .= " AND s.staffId = '$staff'";
        }

        if ($groupBy) {
            switch ($groupBy) {
                case 'daily':
                    $query .= " AND DATE(s.createdAt) = DATE(NOW())";
                    $countQuery .= " AND DATE(s.createdAt) = DATE(NOW())";
                    break;
                case 'weekly':
                    $query .= " AND WEEK(s.createdAt) = WEEK(NOW())";
                    $countQuery .= " AND WEEK(s.createdAt) = WEEK(NOW())";
                    break;
                case 'monthly':
                    $query .= " AND MONTH(s.createdAt) = MONTH(NOW())";
                    $countQuery .= " AND MONTH(s.createdAt) = MONTH(NOW())";
                    break;
                case 'semi-annually':
                    $query .= " AND QUARTER(s.createdAt) = QUARTER(NOW())";
                    $countQuery .= " AND QUARTER(s.createdAt) = QUARTER(NOW())";
                    break;
                case 'annually':
                    $query .= " AND YEAR(s.createdAt) = YEAR(NOW())";
                    $countQuery .= " AND YEAR(s.createdAt) = YEAR(NOW())";
                    break;
            }
        }

        $result = $database->query($query . " ORDER BY s.createdAt DESC");
        return $result;
    }
    static function getAllAdminLatestTransaction($groupBy = null)
    {
        $session = new Session();
        $database = new MySQLDatabase();

        if ($session->has('admin-transactions')) {
            $branch = ($session->get('admin-transactions'));
            if (
                $branch['groupBy'] != $groupBy
            ) {
                $session->set('admin-transactions-page', 1);
            }
        }
        $session->set('admin-transactions', [
            'groupBy' => $groupBy
        ]);

        $page = $session->getOrSet('admin-transactions-page', 1);
        $limit = 7;
        $offset = ($page - 1) * $limit;

        $query = "
            SELECT 
                t.id,
                t.totalPrice AS total_amount,
                t.cashPrice AS cash_received,
                t.changePrice AS cash_change,
                t.createdAt AS transaction_date,
                s.userName AS staff_acc,
                t.productOrderedIds AS product_ordered_list
            FROM transactions AS t
            JOIN staff st ON t.staffId = st.id
            JOIN users s ON st.userId = s.id
            WHERE 1=1";

        $countQuery = "
            SELECT COUNT(*) AS total 
            FROM transactions AS t
            JOIN staff st ON t.staffId = st.id
            JOIN users s ON st.userId = s.id WHERE 1=1";

        if ($groupBy) {
            switch ($groupBy) {
                case 'daily':
                    $query .= " AND DATE(t.createdAt) = DATE(NOW())";
                    $countQuery .= " AND DATE(t.createdAt) = DATE(NOW())";
                    break;
                case 'weekly':
                    $query .= " AND WEEK(t.createdAt) = WEEK(NOW())";
                    $countQuery .= " AND WEEK(t.createdAt) = WEEK(NOW())";
                    break;
                case 'monthly':
                    $query .= " AND MONTH(t.createdAt) = MONTH(NOW())";
                    $countQuery .= " AND MONTH(t.createdAt) = MONTH(NOW())";
                    break;
                case 'semi-annually':
                    $query .= " AND QUARTER(t.createdAt) = QUARTER(NOW())";
                    $countQuery .= " AND QUARTER(t.createdAt) = QUARTER(NOW())";
                    break;
                case 'annually':
                    $query .= " AND YEAR(t.createdAt) = YEAR(NOW())";
                    $countQuery .= " AND YEAR(t.createdAt) = YEAR(NOW())";
                    break;
            }
        }

        $result = $database->query($query . " ORDER BY t.createdAt DESC LIMIT $limit OFFSET $offset");
        $totalPages = RequestSQL::getCountQuery($countQuery, $limit);

        return [
            'result' => $result,
            'page' => $page,
            'total' => $totalPages
        ];
    }


    static function getAllAdminTransaction()
    {
        $session = new Session();
        $database = new MySQLDatabase();

        $page = $session->getOrSet('admin-transaction-page', 1);
        $limit = 7;
        $offset = ($page - 1) * $limit;

        $query = "
            SELECT 
                t.id,
                t.totalPrice AS total_amount,
                t.cashPrice AS cash_received,
                t.changePrice AS cash_change,
                t.createdAt AS transaction_date,
                s.userName AS staff_acc,
                t.productOrderedIds AS product_ordered_list
            FROM transactions AS t
            JOIN staff st ON t.staffId = st.id
            JOIN users s ON st.userId = s.id
            ORDER BY t.createdAt DESC LIMIT $limit OFFSET $offset";

        $countQuery = "
            SELECT COUNT(*) AS total 
            FROM transactions AS t
            JOIN staff st ON t.staffId = st.id
            JOIN users s ON st.userId = s.id";

        $result = $database->query($query);
        $totalPages = RequestSQL::getCountQuery($countQuery, $limit);

        return [
            'result' => $result,
            'page' => $page,
            'total' => $totalPages
        ];
    }

    static function getAdminStaff($branch_target = null, $status = null, $search = null)
    {
        $database = new MySQLDatabase();
        $session = new Session();

        if ($session->has('admin-account')) {
            $branch = ($session->get('admin-account'));
            if (
                $branch['branch'] != $branch_target ||
                $branch['status'] != $status ||
                $branch['search'] != $search
            ) {
                $session->set('admin-account-page', 1);
            }
        }
        $session->set('admin-account', [
            'branch' => $branch_target,
            'status' => $status,
            'search' => $search,
        ]);

        $page = $session->getOrSet('admin-account-page', 1);
        $limit = 7;
        $offset = ($page - 1) * $limit;

        $query = "
            SELECT 
                u.id AS id,
                u.firstName AS firstName,
                u.lastName AS lastName,
                u.userName AS userName,
                u.email AS email,
                u.isAdmin AS isAdmin,
                b.branchName AS branchName,
                u.userStatus AS userStatus
            FROM staff s
            JOIN users u ON s.userId = u.id
            JOIN branch b ON b.id = u.assignedBranch
            WHERE 1=1 AND u.isAdmin = FALSE";

        $countQuery = "
            SELECT COUNT(*) AS total 
            FROM staff s
            JOIN users u ON s.userId = u.id
            JOIN branch b ON b.id = u.assignedBranch
            WHERE 1=1 AND u.isAdmin = FALSE";

        if ($branch_target) {
            $query .= " AND b.branchName = '$branch_target'";
            $countQuery .= " AND b.branchName = '$branch_target'";
        }

        if ($status) {
            $query .= " AND u.userStatus = '$status'";
            $countQuery .= " AND u.userStatus = '$status'";
        }

        if ($search) {
            $search = $database->escape($search);
            $new_query = " AND (
                u.firstName LIKE '%$search%' OR
                u.lastName LIKE '%$search%' OR
                u.userName LIKE '%$search%' OR
                u.email LIKE '%$search%'
            )";
            $query .= $new_query;
            $countQuery .= $new_query;
        }

        $query .= " ORDER BY s.createdAt DESC LIMIT $limit OFFSET $offset";
        $result = $database->query($query);
        $totalPages = RequestSQL::getCountQuery($countQuery, $limit);

        return [
            'result' => $result,
            'page' => $page,
            'total' => $totalPages
        ];
    }

}


?>