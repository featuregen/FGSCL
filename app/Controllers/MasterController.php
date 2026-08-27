<?php
/**
 * Master Data Controller
 * Centralized master data manager for dropdowns, categories, houses, document types, and configurations
 */
class MasterController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    /**
     * Main Master Data Hub
     */
    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) {
            Response::abort(403);
            return;
        }

        $activeCategory = $_GET['cat'] ?? 'houses';

        // Defined categories
        $categories = [
            'houses'         => ['label' => 'Student Houses / Clubs', 'icon' => 'bi-flag-fill'],
            'religions'      => ['label' => 'Religions', 'icon' => 'bi-brightness-high-fill'],
            'castes'         => ['label' => 'Social Categories / Castes', 'icon' => 'bi-diagram-3-fill'],
            'blood_groups'   => ['label' => 'Blood Groups', 'icon' => 'bi-droplet-fill'],
            'document_types' => ['label' => 'Document Types', 'icon' => 'bi-file-earmark-text-fill'],
            'expense_heads'  => ['label' => 'Expense Categories', 'icon' => 'bi-cash-coin']
        ];

        // Seed defaults if empty
        $existingCount = Database::fetch("SELECT COUNT(*) as cnt FROM master_data WHERE school_id = ?", [$schoolId])['cnt'] ?? 0;
        if ($existingCount === 0) {
            $defaults = [
                'houses' => [
                    ['name' => 'Red House (Ruby)', 'code' => 'RED'],
                    ['name' => 'Blue House (Sapphire)', 'code' => 'BLU'],
                    ['name' => 'Green House (Emerald)', 'code' => 'GRN'],
                    ['name' => 'Yellow House (Topaz)', 'code' => 'YEL']
                ],
                'religions' => [
                    ['name' => 'Hinduism', 'code' => 'HIN'],
                    ['name' => 'Islam', 'code' => 'ISL'],
                    ['name' => 'Christianity', 'code' => 'CHR'],
                    ['name' => 'Sikhism', 'code' => 'SIK'],
                    ['name' => 'Buddhism', 'code' => 'BUD'],
                    ['name' => 'Jainism', 'code' => 'JAI'],
                    ['name' => 'Others', 'code' => 'OTH']
                ],
                'castes' => [
                    ['name' => 'General (GEN)', 'code' => 'GEN'],
                    ['name' => 'Other Backward Class (OBC)', 'code' => 'OBC'],
                    ['name' => 'Scheduled Caste (SC)', 'code' => 'SC'],
                    ['name' => 'Scheduled Tribe (ST)', 'code' => 'ST'],
                    ['name' => 'Economically Weaker Section (EWS)', 'code' => 'EWS']
                ],
                'blood_groups' => [
                    ['name' => 'A+', 'code' => 'A+'],
                    ['name' => 'A-', 'code' => 'A-'],
                    ['name' => 'B+', 'code' => 'B+'],
                    ['name' => 'B-', 'code' => 'B-'],
                    ['name' => 'O+', 'code' => 'O+'],
                    ['name' => 'O-', 'code' => 'O-'],
                    ['name' => 'AB+', 'code' => 'AB+'],
                    ['name' => 'AB-', 'code' => 'AB-']
                ],
                'document_types' => [
                    ['name' => 'Birth Certificate', 'code' => 'DOB_CERT'],
                    ['name' => 'Previous School Transfer Certificate (TC)', 'code' => 'TC'],
                    ['name' => 'Previous Marks Card / Report Card', 'code' => 'MARKSHEET'],
                    ['name' => 'Aadhaar Card / National ID', 'code' => 'NAT_ID'],
                    ['name' => 'Medical Fitness Record', 'code' => 'MED_FIT'],
                    ['name' => 'Address Proof (Utility Bill / Passport)', 'code' => 'ADDR_PRF']
                ],
                'expense_heads' => [
                    ['name' => 'Electricity & Power Utility', 'code' => 'EXP_ELEC'],
                    ['name' => 'Internet & Telecommunication', 'code' => 'EXP_NET'],
                    ['name' => 'Building Maintenance & Repairs', 'code' => 'EXP_MAINT'],
                    ['name' => 'Printing & Office Stationery', 'code' => 'EXP_STAT'],
                    ['name' => 'Sports & Annual Function Events', 'code' => 'EXP_EVENT']
                ]
            ];

            foreach ($defaults as $catKey => $items) {
                foreach ($items as $idx => $item) {
                    Database::insert('master_data', [
                        'school_id'  => $schoolId,
                        'category'   => $catKey,
                        'name'       => $item['name'],
                        'code'       => $item['code'],
                        'sort_order' => $idx + 1,
                        'is_active'  => 1
                    ]);
                }
            }
        }

        // Fetch items for the active category
        $items = Database::fetchAll(
            "SELECT * FROM master_data WHERE school_id = ? AND category = ? ORDER BY sort_order ASC, name ASC",
            [$schoolId, $activeCategory]
        );

        // Fetch counts for all categories
        $counts = [];
        foreach (array_keys($categories) as $cKey) {
            $counts[$cKey] = Database::fetch(
                "SELECT COUNT(*) as cnt FROM master_data WHERE school_id = ? AND category = ?",
                [$schoolId, $cKey]
            )['cnt'] ?? 0;
        }

        Response::view('masters/index', [
            'pageTitle'      => 'Master Data Hub',
            'breadcrumbs'    => [['label' => 'Masters']],
            'categories'     => $categories,
            'activeCategory' => $activeCategory,
            'items'          => $items,
            'counts'         => $counts
        ]);
    }

    /**
     * Save Master Item
     */
    public function save()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $id       = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $category = trim($_POST['category'] ?? 'houses');
        $name     = trim($_POST['name'] ?? '');
        $code     = trim($_POST['code'] ?? '');

        if (empty($name)) {
            Session::flash('error', 'Item name is required.');
            Response::redirect('masters?cat=' . urlencode($category));
            return;
        }

        if ($id) {
            Database::update('master_data', [
                'name' => $name,
                'code' => $code
            ], 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Item updated.');
        } else {
            Database::insert('master_data', [
                'school_id' => $schoolId,
                'category'  => $category,
                'name'      => $name,
                'code'      => $code ?: strtoupper(substr($name, 0, 4))
            ]);
            Session::flash('success', 'New item added.');
        }

        Response::redirect('masters?cat=' . urlencode($category));
    }

    /**
     * Delete Master Item
     */
    public function delete($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);
        $cat = $_POST['category'] ?? 'houses';

        if ($schoolId && $id) {
            Database::delete('master_data', 'id = ? AND school_id = ?', [$id, $schoolId]);
            Session::flash('success', 'Item deleted.');
        }

        Response::redirect('masters?cat=' . urlencode($cat));
    }
}