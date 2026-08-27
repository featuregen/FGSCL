<?php
/**
 * Inventory & Asset Management Controller
 * Handles item stock catalog, suppliers, categories, and item issues to staff/departments
 */
class InventoryController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    /**
     * Main Inventory Dashboard
     */
    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) {
            Response::abort(403);
            return;
        }

        $tab = $_GET['tab'] ?? 'items';
        $search = trim($_GET['search'] ?? '');
        $catFilter = trim($_GET['category_id'] ?? 'all');

        // 1. Fetch Categories
        $categories = Database::fetchAll(
            "SELECT ic.*, 
                    (SELECT COUNT(*) FROM inventory_items WHERE category_id = ic.id) as item_count
             FROM inventory_categories ic
             WHERE ic.school_id = ?
             ORDER BY ic.name ASC",
            [$schoolId]
        );

        // If no categories exist, seed defaults
        if (empty($categories)) {
            $defaultCats = ['Stationery & Office Supplies', 'Classroom Furniture', 'Sports Equipment', 'Science Lab Supplies', 'IT & Computer Hardware', 'Janitorial & Cleaning'];
            foreach ($defaultCats as $dc) {
                Database::insert('inventory_categories', ['school_id' => $schoolId, 'name' => $dc]);
            }
            $categories = Database::fetchAll("SELECT * FROM inventory_categories WHERE school_id = ? ORDER BY name ASC", [$schoolId]);
        }

        // 2. Fetch Suppliers
        $suppliers = Database::fetchAll(
            "SELECT isup.*, 
                    (SELECT COUNT(*) FROM inventory_items WHERE supplier_id = isup.id) as item_count
             FROM inventory_suppliers isup
             WHERE isup.school_id = ?
             ORDER BY isup.name ASC",
            [$schoolId]
        );

        // 3. Fetch Items with filters
        $itemParams = [$schoolId];
        $itemWhere = "ii.school_id = ?";
        if (!empty($search)) {
            $itemWhere .= " AND (ii.name LIKE ? OR ii.code LIKE ?)";
            $s = "%{$search}%";
            $itemParams = array_merge($itemParams, [$s, $s]);
        }
        if ($catFilter !== 'all' && !empty($catFilter)) {
            $itemWhere .= " AND ii.category_id = ?";
            $itemParams[] = (int)$catFilter;
        }

        $items = Database::fetchAll(
            "SELECT ii.*, ic.name as category_name, isup.name as supplier_name
             FROM inventory_items ii
             LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
             LEFT JOIN inventory_suppliers isup ON ii.supplier_id = isup.id
             WHERE {$itemWhere}
             ORDER BY ii.name ASC",
            $itemParams
        );

        // 4. Fetch Issued Items
        $issues = Database::fetchAll(
            "SELECT iissue.*, ii.name as item_name, ii.code as item_code, ii.unit,
                    u.full_name as issuer_name
             FROM inventory_issues iissue
             JOIN inventory_items ii ON iissue.item_id = ii.id
             LEFT JOIN users u ON iissue.issued_by = u.id
             WHERE iissue.school_id = ?
             ORDER BY CASE WHEN iissue.status = 'issued' THEN 1 ELSE 2 END, iissue.issue_date DESC",
            [$schoolId]
        );

        // 5. Staff list for issue dropdown
        $staffMembers = Database::fetchAll(
            "SELECT u.id, u.full_name, r.name as role_name, d.name as department_name
             FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             LEFT JOIN staff_details sd ON u.id = sd.user_id
             LEFT JOIN departments d ON sd.department_id = d.id
             WHERE u.school_id = ? AND u.is_active = 1
             ORDER BY u.full_name ASC",
            [$schoolId]
        );

        // Departments list
        $departments = Database::fetchAll("SELECT * FROM departments WHERE school_id = ? ORDER BY name ASC", [$schoolId]);

        // Calculate Stats
        $lowStockCount = 0;
        $totalStockQty = 0;
        foreach ($items as $it) {
            $totalStockQty += $it['quantity'];
            if ($it['available_quantity'] <= $it['min_quantity_alert']) {
                $lowStockCount++;
            }
        }

        $stats = [
            'total_items' => count($items),
            'total_stock' => $totalStockQty,
            'low_stock'   => $lowStockCount,
            'issued_count'=> Database::fetch("SELECT COUNT(*) as cnt FROM inventory_issues WHERE school_id = ? AND status = 'issued'", [$schoolId])['cnt'] ?? 0
        ];

        Response::view('inventory/index', [
            'pageTitle'    => 'Inventory & Assets',
            'breadcrumbs'  => [['label' => 'Inventory']],
            'tab'          => $tab,
            'items'        => $items,
            'categories'   => $categories,
            'suppliers'    => $suppliers,
            'issues'       => $issues,
            'staffMembers' => $staffMembers,
            'departments'  => $departments,
            'stats'        => $stats,
            'search'       => $search,
            'catFilter'    => $catFilter
        ]);
    }

    /**
     * Save / Update Item
     */
    public function saveItem()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $id         = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $supplierId = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
        $name       = trim($_POST['name'] ?? '');
        $code       = trim($_POST['code'] ?? '');
        $unit       = trim($_POST['unit'] ?? 'pcs');
        $unitPrice  = (float)($_POST['unit_price'] ?? 0.0);
        $quantity   = max(0, (int)($_POST['quantity'] ?? 0));
        $minAlert   = max(1, (int)($_POST['min_quantity_alert'] ?? 5));
        $desc       = trim($_POST['description'] ?? '');

        if (empty($name)) {
            Session::flash('error', 'Item name is required.');
            Response::redirect('inventory?tab=items');
            return;
        }

        if ($id) {
            $curr = Database::fetch("SELECT * FROM inventory_items WHERE id = ? AND school_id = ?", [$id, $schoolId]);
            $diff = $quantity - $curr['quantity'];
            $newAvail = max(0, $curr['available_quantity'] + $diff);

            Database::update('inventory_items', [
                'category_id'        => $categoryId,
                'supplier_id'        => $supplierId,
                'name'               => $name,
                'code'               => $code,
                'unit'               => $unit,
                'unit_price'         => $unitPrice,
                'quantity'           => $quantity,
                'available_quantity' => $newAvail,
                'min_quantity_alert' => $minAlert,
                'description'        => $desc
            ], 'id = ? AND school_id = ?', [$id, $schoolId]);

            Session::flash('success', 'Item updated in inventory.');
        } else {
            Database::insert('inventory_items', [
                'school_id'          => $schoolId,
                'category_id'        => $categoryId,
                'supplier_id'        => $supplierId,
                'name'               => $name,
                'code'               => $code ?: ('ITEM-' . rand(1000, 9999)),
                'unit'               => $unit,
                'unit_price'         => $unitPrice,
                'quantity'           => $quantity,
                'available_quantity' => $quantity,
                'min_quantity_alert' => $minAlert,
                'description'        => $desc,
                'status'             => 'active'
            ]);

            Session::flash('success', 'Item added to inventory.');
        }

        Response::redirect('inventory?tab=items');
    }

    /**
     * Delete Item
     */
    public function deleteItem($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id) {
            Response::abort(403);
            return;
        }

        // Check if currently issued
        $activeIssues = Database::fetch("SELECT COUNT(*) as cnt FROM inventory_issues WHERE item_id = ? AND status = 'issued'", [$id])['cnt'] ?? 0;
        if ($activeIssues > 0) {
            Session::flash('error', 'Cannot delete item: Stock units are currently issued out.');
            Response::redirect('inventory?tab=items');
            return;
        }

        Database::delete('inventory_items', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Item deleted.');
        Response::redirect('inventory?tab=items');
    }

    /**
     * Issue Item to Staff or Department
     */
    public function issueItem()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $itemId    = (int)($_POST['item_id'] ?? 0);
        $toType    = $_POST['issued_to_type'] ?? 'staff';
        $toId      = !empty($_POST['issued_to_id']) ? (int)$_POST['issued_to_id'] : null;
        $toName    = trim($_POST['issued_to_name'] ?? '');
        $qty       = max(1, (int)($_POST['quantity'] ?? 1));
        $issueDate = trim($_POST['issue_date'] ?? date('Y-m-d'));
        $returnDate= !empty($_POST['return_date']) ? $_POST['return_date'] : null;
        $remarks   = trim($_POST['remarks'] ?? '');

        $item = Database::fetch("SELECT * FROM inventory_items WHERE id = ? AND school_id = ?", [$itemId, $schoolId]);
        if (!$item || $item['available_quantity'] < $qty) {
            Session::flash('error', "Insufficient stock available. Current available: " . ($item['available_quantity'] ?? 0));
            Response::redirect('inventory?tab=issues');
            return;
        }

        // Resolve name if staff
        if ($toType === 'staff' && $toId) {
            $user = Database::fetch("SELECT full_name FROM users WHERE id = ?", [$toId]);
            $toName = $user['full_name'] ?? $toName;
        }

        Database::insert('inventory_issues', [
            'school_id'       => $schoolId,
            'item_id'         => $itemId,
            'issued_to_type'  => $toType,
            'issued_to_id'    => $toId,
            'issued_to_name'  => $toName,
            'quantity'        => $qty,
            'issue_date'      => $issueDate,
            'return_date'     => $returnDate,
            'status'          => 'issued',
            'remarks'         => $remarks,
            'issued_by'       => Session::userId()
        ]);

        Database::query("UPDATE inventory_items SET available_quantity = available_quantity - ? WHERE id = ?", [$qty, $itemId]);

        Session::flash('success', "Item issued successfully.");
        Response::redirect('inventory?tab=issues');
    }

    /**
     * Return or Mark Item as Consumed
     */
    public function returnItem()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $issueId   = (int)($_POST['issue_id'] ?? 0);
        $action    = $_POST['action'] ?? 'returned'; // 'returned' or 'consumed'
        $returnDate= trim($_POST['return_date'] ?? date('Y-m-d'));

        $issue = Database::fetch("SELECT * FROM inventory_issues WHERE id = ? AND school_id = ?", [$issueId, $schoolId]);
        if (!$issue || $issue['status'] !== 'issued') {
            Session::flash('error', 'Invalid issue record or already returned.');
            Response::redirect('inventory?tab=issues');
            return;
        }

        Database::update('inventory_issues', [
            'status'      => $action === 'consumed' ? 'consumed' : 'returned',
            'return_date' => $returnDate
        ], 'id = ? AND school_id = ?', [$issueId, $schoolId]);

        // If returned, add back to available stock
        if ($action === 'returned') {
            Database::query(
                "UPDATE inventory_items SET available_quantity = available_quantity + ? WHERE id = ?",
                [$issue['quantity'], $issue['item_id']]
            );
        }

        Session::flash('success', $action === 'consumed' ? 'Item marked as consumed.' : 'Item returned to inventory stock.');
        Response::redirect('inventory?tab=issues');
    }

    /**
     * Save Category
     */
    public function saveCategory()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');

        if (!empty($name)) {
            Database::insert('inventory_categories', [
                'school_id'   => $schoolId,
                'name'        => $name,
                'description' => $desc
            ]);
            Session::flash('success', 'Category added.');
        }

        Response::redirect('inventory?tab=categories');
    }

    /**
     * Delete Category
     */
    public function deleteCategory($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);
        if ($schoolId && $id) {
            Database::delete('inventory_categories', 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Category deleted.');
        }
        Response::redirect('inventory?tab=categories');
    }

    /**
     * Save Supplier
     */
    public function saveSupplier()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $name    = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact_person'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (!empty($name)) {
            Database::insert('inventory_suppliers', [
                'school_id'      => $schoolId,
                'name'           => $name,
                'contact_person' => $contact,
                'phone'          => $phone,
                'email'          => $email,
                'address'        => $address
            ]);
            Session::flash('success', 'Supplier added.');
        }

        Response::redirect('inventory?tab=suppliers');
    }

    /**
     * Delete Supplier
     */
    public function deleteSupplier($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);
        if ($schoolId && $id) {
            Database::delete('inventory_suppliers', 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Supplier deleted.');
        }
        Response::redirect('inventory?tab=suppliers');
    }
}