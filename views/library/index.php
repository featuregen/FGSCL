
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 class="content-title" style="margin: 0; font-size: 24px; font-weight: 700; color: var(--text-primary, #1e293b);">Library Management</h2>
        <p class="text-muted" style="margin: 4px 0 0 0; font-size: 14px;">Manage book inventory, circulation, issues, returns, and overdue tracking</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-primary" onclick="openBookModal()">
            <i class="bi bi-plus-circle"></i> Add New Book
        </button>
        <button class="btn btn-secondary" onclick="openIssueModal()">
            <i class="bi bi-journal-arrow-up"></i> Issue Book
        </button>
    </div>
</div>

<!-- Stat Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 24px;">
    <div class="card" style="padding: 18px; border-left: 4px solid #6366f1; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(99, 102, 241, 0.12); display: flex; align-items: center; justify-content: center; color: #6366f1; font-size: 20px;">
            <i class="bi bi-book"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Total Titles</div>
            <div style="font-size: 24px; font-weight: 700; color: #6366f1;"><?= number_format($stats['total_titles']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #10b981; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 20px;">
            <i class="bi bi-check2-circle"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Available Stock</div>
            <div style="font-size: 24px; font-weight: 700; color: #10b981;"><?= number_format($stats['available']) ?> / <?= number_format($stats['total_copies']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #0ea5e9; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(14, 165, 233, 0.12); display: flex; align-items: center; justify-content: center; color: #0ea5e9; font-size: 20px;">
            <i class="bi bi-journal-check"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Currently Issued</div>
            <div style="font-size: 24px; font-weight: 700; color: #0ea5e9;"><?= number_format($stats['issued']) ?></div>
        </div>
    </div>

    <div class="card" style="padding: 18px; border-left: 4px solid #ef4444; display: flex; align-items: center; gap: 14px;">
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(239, 68, 68, 0.12); display: flex; align-items: center; justify-content: center; color: #ef4444; font-size: 20px;">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;">Overdue Books</div>
            <div style="font-size: 24px; font-weight: 700; color: #ef4444;"><?= number_format($stats['overdue']) ?></div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div style="display: flex; gap: 12px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color, #e2e8f0); padding-bottom: 8px;">
    <a href="<?= APP_URL ?>/library?tab=books" class="btn" style="background: <?= $tab === 'books' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'books' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-bookshelf"></i> Book Catalog
    </a>
    <a href="<?= APP_URL ?>/library?tab=issues" class="btn" style="background: <?= $tab === 'issues' ? 'var(--primary-color, #0f766e)' : 'transparent' ?>; color: <?= $tab === 'issues' ? '#fff' : 'var(--text-primary, #334155)' ?>; font-weight: 600; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-arrow-left-right"></i> Circulation / Issued Books (<?= count($issues) ?>)
    </a>
</div>

<?php if ($tab === 'books'): ?>
<!-- Book Catalog Search & Filter -->
<div class="card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="<?= APP_URL ?>/library" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
        <input type="hidden" name="tab" value="books">
        <div style="flex: 1; min-width: 240px;">
            <input type="text" name="search" class="form-control" placeholder="Search by title, author, ISBN, rack..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div style="min-width: 180px;">
            <select name="category" class="form-control" onchange="this.form.submit()">
                <option value="all">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
        <?php if (!empty($search) || $category !== 'all'): ?>
            <a href="<?= APP_URL ?>/library?tab=books" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Books Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 14px 18px;">Book Details</th>
                    <th style="padding: 14px 18px;">Author & Publisher</th>
                    <th style="padding: 14px 18px;">Category / Rack</th>
                    <th style="padding: 14px 18px;">ISBN / Edition</th>
                    <th style="padding: 14px 18px;">Copies (Avail / Total)</th>
                    <th style="padding: 14px 18px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($books)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                            <i class="bi bi-journal-x" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                            No books found in catalog. Click "Add New Book" to start cataloging.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($books as $b): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; color: var(--text-primary, #1e293b); font-size: 14px;">
                                    <?= htmlspecialchars($b['title']) ?>
                                </div>
                                <?php if (!empty($b['price']) && $b['price'] > 0): ?>
                                    <div style="font-size: 12px; color: var(--text-muted, #64748b);">Price: <?= number_format($b['price'], 2) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 600; font-size: 13px;"><?= htmlspecialchars($b['author']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);"><?= htmlspecialchars($b['publisher'] ?: '-') ?></div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <span class="badge" style="background: rgba(99, 102, 241, 0.12); color: #4f46e5; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                    <?= htmlspecialchars($b['category'] ?: 'General') ?>
                                </span>
                                <?php if (!empty($b['rack_no'])): ?>
                                    <div style="font-size: 12px; color: var(--text-muted, #64748b); margin-top: 4px;">Rack: <?= htmlspecialchars($b['rack_no']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px; color: var(--text-muted, #64748b);">
                                <div>ISBN: <?= htmlspecialchars($b['isbn'] ?: 'N/A') ?></div>
                                <div>Ed: <?= htmlspecialchars($b['edition'] ?: '1st') ?></div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 16px; font-weight: 700; color: <?= $b['available_quantity'] > 0 ? '#10b981' : '#ef4444' ?>;">
                                        <?= $b['available_quantity'] ?>
                                    </span>
                                    <span style="color: var(--text-muted, #94a3b8); font-size: 13px;">/ <?= $b['quantity'] ?> copies</span>
                                </div>
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <button class="btn btn-sm btn-secondary" onclick='editBook(<?= json_encode($b) ?>)' title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="<?= APP_URL ?>/library/delete-book" style="display: inline;" onsubmit="return confirm('Delete this book from the catalog?')">
                                        <?= Session::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" style="background: #ef4444; color: #fff; border: none; border-radius: 6px;" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($tab === 'issues'): ?>
<!-- Circulation / Issued Books Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-surface-secondary, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                    <th style="padding: 14px 18px;">Book</th>
                    <th style="padding: 14px 18px;">Borrower</th>
                    <th style="padding: 14px 18px;">Issue Date</th>
                    <th style="padding: 14px 18px;">Due Date</th>
                    <th style="padding: 14px 18px;">Status / Overdue</th>
                    <th style="padding: 14px 18px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($issues)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                            <i class="bi bi-check-circle" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                            No books are currently issued out.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($issues as $iss): ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #f1f5f9);">
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; color: var(--text-primary, #1e293b);"><?= htmlspecialchars($iss['book_title']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);">By <?= htmlspecialchars($iss['book_author']) ?></div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 600;"><?= htmlspecialchars($iss['borrower_name']) ?></div>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b); text-transform: capitalize;"><?= htmlspecialchars($iss['borrower_type']) ?></div>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <?= date('d M Y', strtotime($iss['issue_date'])) ?>
                            </td>
                            <td style="padding: 14px 18px; font-size: 13px;">
                                <?= date('d M Y', strtotime($iss['due_date'])) ?>
                            </td>
                            <td style="padding: 14px 18px;">
                                <?php if ($iss['status'] === 'issued'): ?>
                                    <?php if ($iss['overdue_days'] > 0): ?>
                                        <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                            <i class="bi bi-clock-history"></i> Overdue (<?= $iss['overdue_days'] ?> days)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(14, 165, 233, 0.15); color: #0284c7; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                            Active Issue
                                        </span>
                                    <?php endif; ?>
                                <?php elseif ($iss['status'] === 'returned'): ?>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                        Returned (<?= date('d M Y', strtotime($iss['return_date'])) ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <?php if ($iss['status'] === 'issued'): ?>
                                    <button class="btn btn-sm btn-primary" onclick='openReturnModal(<?= json_encode($iss) ?>)'>
                                        <i class="bi bi-box-arrow-in-down-left"></i> Return Book
                                    </button>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: var(--text-muted, #94a3b8);">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Add / Edit Book Modal -->
<div id="bookModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 600px; max-height: 90vh; display: flex; flex-direction: column; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;" id="bookModalTitle">Add New Book</h3>
            <button class="btn-icon" onclick="closeModal('bookModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/library/save-book">
            <?= Session::csrfField() ?>
            <input type="hidden" name="id" id="book_id">
            <div class="card-body" style="padding: 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Book Title *</label>
                    <input type="text" name="title" id="book_title" class="form-control" placeholder="e.g. Higher Engineering Mathematics" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Author *</label>
                        <input type="text" name="author" id="book_author" class="form-control" placeholder="Author Name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Publisher</label>
                        <input type="text" name="publisher" id="book_publisher" class="form-control" placeholder="Publisher Name">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Category / Subject</label>
                        <input type="text" name="category" id="book_category" class="form-control" placeholder="e.g. Mathematics, Science, Fiction" list="catList">
                        <datalist id="catList">
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= htmlspecialchars($c['category']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Rack / Shelf No</label>
                        <input type="text" name="rack_no" id="book_rack" class="form-control" placeholder="e.g. Rack A-3">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Total Copies *</label>
                        <input type="number" name="quantity" id="book_quantity" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">ISBN</label>
                        <input type="text" name="isbn" id="book_isbn" class="form-control" placeholder="ISBN-13">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Price</label>
                        <input type="number" step="0.01" name="price" id="book_price" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Description / Edition</label>
                    <input type="text" name="edition" id="book_edition" class="form-control" placeholder="e.g. 5th Edition">
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('bookModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Book</button>
            </div>
        </form>
    </div>
</div>

<!-- Issue Book Modal -->
<div id="issueModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Issue Book to Member</h3>
            <button class="btn-icon" onclick="closeModal('issueModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/library/issue-book">
            <?= Session::csrfField() ?>
            <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Book *</label>
                    <select name="book_id" class="form-control" required>
                        <option value="">-- Choose Available Book --</option>
                        <?php foreach ($availableBooks as $ab): ?>
                            <option value="<?= $ab['id'] ?>">
                                <?= htmlspecialchars($ab['title']) ?> (by <?= htmlspecialchars($ab['author']) ?>) - [<?= $ab['available_quantity'] ?> in stock]
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Select Borrower (Student / Staff) *</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">-- Choose Student or Staff Member --</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= $m['id'] ?>">
                                <?= htmlspecialchars($m['full_name']) ?> (<?= htmlspecialchars($m['class_name'] ? "Class {$m['class_name']}" : ($m['role_name'] ?? $m['user_type'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Issue Date *</label>
                        <input type="date" name="issue_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Due Date *</label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Remarks</label>
                    <input type="text" name="remarks" class="form-control" placeholder="Optional notes (e.g. Good condition)">
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('issueModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-journal-plus"></i> Confirm Issue</button>
            </div>
        </form>
    </div>
</div>

<!-- Return Book Modal -->
<div id="returnModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 480px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-color, #e2e8f0);">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Process Book Return</h3>
            <button class="btn-icon" onclick="closeModal('returnModal')" style="border: none; background: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/library/return-book">
            <?= Session::csrfField() ?>
            <input type="hidden" name="issue_id" id="return_issue_id">
            <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; gap: 14px;">
                <div style="background: var(--bg-surface-secondary, #f8fafc); padding: 14px; border-radius: 8px; border: 1px solid var(--border-color, #e2e8f0);">
                    <div style="font-weight: 700; font-size: 15px;" id="return_book_title"></div>
                    <div style="font-size: 13px; color: var(--text-muted, #64748b);" id="return_borrower_info"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Return Date *</label>
                    <input type="date" name="return_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Late Fine (Amount)</label>
                        <input type="number" step="0.01" name="fine_amount" id="return_fine_amount" class="form-control" value="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Fine Collected</label>
                        <input type="number" step="0.01" name="fine_paid" id="return_fine_paid" class="form-control" value="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 6px;">Condition / Remarks</label>
                    <input type="text" name="remarks" class="form-control" placeholder="e.g. Returned in good condition">
                </div>
            </div>
            <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('returnModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-down-left"></i> Confirm Return</button>
            </div>
        </form>
    </div>
</div>

<script>
function openBookModal() {
    document.getElementById('bookModalTitle').innerText = 'Add New Book';
    document.getElementById('book_id').value = '';
    document.getElementById('book_title').value = '';
    document.getElementById('book_author').value = '';
    document.getElementById('book_publisher').value = '';
    document.getElementById('book_category').value = '';
    document.getElementById('book_rack').value = '';
    document.getElementById('book_quantity').value = '1';
    document.getElementById('book_isbn').value = '';
    document.getElementById('book_price').value = '';
    document.getElementById('book_edition').value = '';
    document.getElementById('bookModal').style.display = 'flex';
}

function editBook(book) {
    document.getElementById('bookModalTitle').innerText = 'Edit Book Details';
    document.getElementById('book_id').value = book.id;
    document.getElementById('book_title').value = book.title;
    document.getElementById('book_author').value = book.author;
    document.getElementById('book_publisher').value = book.publisher || '';
    document.getElementById('book_category').value = book.category || '';
    document.getElementById('book_rack').value = book.rack_no || '';
    document.getElementById('book_quantity').value = book.quantity;
    document.getElementById('book_isbn').value = book.isbn || '';
    document.getElementById('book_price').value = book.price || '';
    document.getElementById('book_edition').value = book.edition || '';
    document.getElementById('bookModal').style.display = 'flex';
}

function openIssueModal() {
    document.getElementById('issueModal').style.display = 'flex';
}

function openReturnModal(issue) {
    document.getElementById('return_issue_id').value = issue.id;
    document.getElementById('return_book_title').innerText = issue.book_title;
    document.getElementById('return_borrower_info').innerText = 'Borrower: ' + issue.borrower_name + ' | Due: ' + issue.due_date;
    
    // Auto calculate suggested fine if overdue ($5 / day default for example)
    let fine = 0;
    if (issue.overdue_days > 0) {
        fine = issue.overdue_days * 5;
    }
    document.getElementById('return_fine_amount').value = fine.toFixed(2);
    document.getElementById('return_fine_paid').value = fine.toFixed(2);
    document.getElementById('returnModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>

