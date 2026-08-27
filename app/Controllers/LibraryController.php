<?php
/**
 * Library Management Controller
 * Handles book catalog, circulation (issue/return), overdue fines, and borrowing history
 */
class LibraryController
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('auth/login');
        }
    }

    /**
     * Main Library Dashboard
     */
    public function index()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId) {
            Response::abort(403);
            return;
        }

        $tab = $_GET['tab'] ?? 'books';
        $search = trim($_GET['search'] ?? '');
        $category = trim($_GET['category'] ?? 'all');

        // Fetch Books with optional search / filter
        $bookParams = [$schoolId];
        $bookWhere = "school_id = ?";
        if (!empty($search)) {
            $bookWhere .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ? OR rack_no LIKE ?)";
            $s = "%{$search}%";
            $bookParams = array_merge($bookParams, [$s, $s, $s, $s]);
        }
        if ($category !== 'all' && !empty($category)) {
            $bookWhere .= " AND category = ?";
            $bookParams[] = $category;
        }

        $books = Database::fetchAll(
            "SELECT * FROM library_books WHERE {$bookWhere} ORDER BY title ASC",
            $bookParams
        );

        // Fetch Active Issues
        $issues = Database::fetchAll(
            "SELECT li.*, lb.title as book_title, lb.author as book_author, lb.isbn,
                    u.full_name as borrower_name, u.email as borrower_email, u.user_type as borrower_type,
                    issuer.full_name as issuer_name,
                    DATEDIFF(CURDATE(), li.due_date) as overdue_days
             FROM library_issues li
             JOIN library_books lb ON li.book_id = lb.id
             JOIN users u ON li.user_id = u.id
             LEFT JOIN users issuer ON li.issued_by = issuer.id
             WHERE li.school_id = ?
             ORDER BY CASE WHEN li.status = 'issued' THEN 1 ELSE 2 END, li.due_date ASC",
            [$schoolId]
        );

        // Categories list
        $categories = Database::fetchAll(
            "SELECT DISTINCT category FROM library_books WHERE school_id = ? AND category IS NOT NULL AND category != '' ORDER BY category ASC",
            [$schoolId]
        );

        // Stats calculation
        $totalCopies = Database::fetch("SELECT SUM(quantity) as total, SUM(available_quantity) as avail FROM library_books WHERE school_id = ?", [$schoolId]);
        $issuedCount = Database::fetch("SELECT COUNT(*) as cnt FROM library_issues WHERE school_id = ? AND status = 'issued'", [$schoolId])['cnt'] ?? 0;
        $overdueCount = Database::fetch("SELECT COUNT(*) as cnt FROM library_issues WHERE school_id = ? AND status = 'issued' AND due_date < CURDATE()", [$schoolId])['cnt'] ?? 0;

        $stats = [
            'total_titles'  => count($books),
            'total_copies'  => (int)($totalCopies['total'] ?? 0),
            'available'     => (int)($totalCopies['avail'] ?? 0),
            'issued'        => $issuedCount,
            'overdue'       => $overdueCount
        ];

        // Fetch student and staff members for issuing books
        $members = Database::fetchAll(
            "SELECT u.id, u.full_name, u.user_type, r.name as role_name,
                    c.name as class_name, sec.name as section_name
             FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             LEFT JOIN student_details sd ON u.id = sd.user_id
             LEFT JOIN classes c ON sd.class_id = c.id
             LEFT JOIN sections sec ON sd.section_id = sec.id
             WHERE u.school_id = ? AND u.is_active = 1 AND u.user_type IN ('student', 'staff', 'teacher')
             ORDER BY u.full_name ASC",
            [$schoolId]
        );

        // Available books for issue dropdown
        $availableBooks = Database::fetchAll(
            "SELECT id, title, author, isbn, rack_no, available_quantity 
             FROM library_books 
             WHERE school_id = ? AND available_quantity > 0 AND status = 'active'
             ORDER BY title ASC",
            [$schoolId]
        );

        Response::view('library/index', [
            'pageTitle'      => 'Library Management',
            'breadcrumbs'    => [['label' => 'Library']],
            'tab'            => $tab,
            'books'          => $books,
            'issues'         => $issues,
            'categories'     => $categories,
            'stats'          => $stats,
            'members'        => $members,
            'availableBooks' => $availableBooks,
            'search'         => $search,
            'category'       => $category
        ]);
    }

    /**
     * Save / Update Book
     */
    public function saveBook()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $id        = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $title     = trim($_POST['title'] ?? '');
        $author    = trim($_POST['author'] ?? '');
        $isbn      = trim($_POST['isbn'] ?? '');
        $publisher = trim($_POST['publisher'] ?? '');
        $category  = trim($_POST['category'] ?? 'General');
        $rackNo    = trim($_POST['rack_no'] ?? '');
        $quantity  = max(1, (int)($_POST['quantity'] ?? 1));
        $price     = (float)($_POST['price'] ?? 0.0);
        $edition   = trim($_POST['edition'] ?? '');
        $desc      = trim($_POST['description'] ?? '');

        if (empty($title) || empty($author)) {
            Session::flash('error', 'Book title and author are required.');
            Response::redirect('library');
            return;
        }

        if ($id) {
            // Updating existing book
            $current = Database::fetch("SELECT * FROM library_books WHERE id = ? AND school_id = ?", [$id, $schoolId]);
            if (!$current) {
                Response::abort(404);
                return;
            }
            $diff = $quantity - $current['quantity'];
            $newAvailable = max(0, $current['available_quantity'] + $diff);

            Database::update('library_books', [
                'title'              => $title,
                'author'             => $author,
                'isbn'               => $isbn,
                'publisher'          => $publisher,
                'category'           => $category,
                'rack_no'            => $rackNo,
                'quantity'           => $quantity,
                'available_quantity' => $newAvailable,
                'price'              => $price,
                'edition'            => $edition,
                'description'        => $desc
            ], 'id = ? AND school_id = ?', [$id, $schoolId]);

            Session::flash('success', 'Book details updated successfully.');
        } else {
            // Insert new book
            Database::insert('library_books', [
                'school_id'          => $schoolId,
                'title'              => $title,
                'author'             => $author,
                'isbn'               => $isbn,
                'publisher'          => $publisher,
                'category'           => $category,
                'rack_no'            => $rackNo,
                'quantity'           => $quantity,
                'available_quantity' => $quantity,
                'price'              => $price,
                'edition'            => $edition,
                'description'        => $desc,
                'status'             => 'active'
            ]);

            Session::flash('success', 'Book added to catalog.');
        }

        Response::redirect('library?tab=books');
    }

    /**
     * Delete Book
     */
    public function deleteBook($id = null)
    {
        $schoolId = Session::schoolId();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        if (!$schoolId || !$id) {
            Response::abort(403);
            return;
        }

        // Check if book has active issues
        $activeIssues = Database::fetch(
            "SELECT COUNT(*) as cnt FROM library_issues WHERE book_id = ? AND status = 'issued'",
            [$id]
        )['cnt'] ?? 0;

        if ($activeIssues > 0) {
            Session::flash('error', 'Cannot delete book: Copies are currently issued to students or staff.');
            Response::redirect('library?tab=books');
            return;
        }

        Database::delete('library_books', 'id = ? AND school_id = ?', [$id, $schoolId]);
        Session::flash('success', 'Book removed from library catalog.');
        Response::redirect('library?tab=books');
    }

    /**
     * Issue Book to Student or Staff
     */
    public function issueBook()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $bookId    = (int)($_POST['book_id'] ?? 0);
        $userId    = (int)($_POST['user_id'] ?? 0);
        $issueDate = trim($_POST['issue_date'] ?? date('Y-m-d'));
        $dueDate   = trim($_POST['due_date'] ?? date('Y-m-d', strtotime('+14 days')));
        $remarks   = trim($_POST['remarks'] ?? '');

        if (!$bookId || !$userId || !$dueDate) {
            Session::flash('error', 'Please select a book, borrower, and return due date.');
            Response::redirect('library?tab=issues');
            return;
        }

        // Check book availability
        $book = Database::fetch("SELECT * FROM library_books WHERE id = ? AND school_id = ?", [$bookId, $schoolId]);
        if (!$book || $book['available_quantity'] <= 0) {
            Session::flash('error', 'Selected book is out of stock / no copies currently available.');
            Response::redirect('library?tab=issues');
            return;
        }

        // Create issue record
        Database::insert('library_issues', [
            'school_id'   => $schoolId,
            'book_id'     => $bookId,
            'user_id'     => $userId,
            'issue_date'  => $issueDate,
            'due_date'    => $dueDate,
            'status'      => 'issued',
            'remarks'     => $remarks,
            'issued_by'   => Session::userId()
        ]);

        // Decrement available copies
        Database::query(
            "UPDATE library_books SET available_quantity = available_quantity - 1 WHERE id = ?",
            [$bookId]
        );

        Session::flash('success', "Book '{$book['title']}' issued successfully.");
        Response::redirect('library?tab=issues');
    }

    /**
     * Return Book & Calculate Late Fine
     */
    public function returnBook()
    {
        $schoolId = Session::schoolId();
        if (!$schoolId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::abort(403);
            return;
        }

        $issueId    = (int)($_POST['issue_id'] ?? 0);
        $returnDate = trim($_POST['return_date'] ?? date('Y-m-d'));
        $fineAmount = (float)($_POST['fine_amount'] ?? 0.0);
        $finePaid   = (float)($_POST['fine_paid'] ?? 0.0);
        $remarks    = trim($_POST['remarks'] ?? '');

        $issue = Database::fetch("SELECT * FROM library_issues WHERE id = ? AND school_id = ?", [$issueId, $schoolId]);
        if (!$issue || $issue['status'] !== 'issued') {
            Session::flash('error', 'Issue record not found or already returned.');
            Response::redirect('library?tab=issues');
            return;
        }

        // Update issue record
        Database::update('library_issues', [
            'return_date' => $returnDate,
            'fine_amount' => $fineAmount,
            'fine_paid'   => $finePaid,
            'status'      => 'returned',
            'remarks'     => $remarks
        ], 'id = ? AND school_id = ?', [$issueId, $schoolId]);

        // Increment available quantity in catalog
        Database::query(
            "UPDATE library_books SET available_quantity = available_quantity + 1 WHERE id = ?",
            [$issue['book_id']]
        );

        Session::flash('success', 'Book returned and stock updated.');
        Response::redirect('library?tab=issues');
    }
}