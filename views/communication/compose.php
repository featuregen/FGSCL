
<div class="content-header">
    <h2 class="content-title">Compose Message</h2>
    <a href="<?= APP_URL ?>/communication" class="btn btn-outline">
        <i class="bi-arrow-left"></i> Back to Noticeboard
    </a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-body">
        <form action="<?= APP_URL ?>/communication/store" method="POST" enctype="multipart/form-data">
            <?= Session::csrfField() ?>
            
            <div class="form-group">
                <label class="form-label">Message Type</label>
                <div class="d-flex gap-3">
                    <label class="d-flex align-items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="notice" checked>
                        <span><i class="bi-clipboard text-info"></i> Noticeboard</span>
                    </label>
                    <label class="d-flex align-items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="email">
                        <span><i class="bi-envelope text-primary"></i> Email Broadcast</span>
                    </label>
                    <label class="d-flex align-items-center gap-2 cursor-pointer">
                        <input type="radio" name="type" value="sms">
                        <span><i class="bi-chat-left-text text-success"></i> SMS Broadcast</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Target Audience <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap gap-3 mb-2">
                    <label class="d-flex align-items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="target_roles[]" value="student" checked> Students
                    </label>
                    <label class="d-flex align-items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="target_roles[]" value="parent" checked> Parents
                    </label>
                    <label class="d-flex align-items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="target_roles[]" value="teacher"> Teachers
                    </label>
                    <label class="d-flex align-items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="target_roles[]" value="staff"> Other Staff
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Specific Classes (Leave empty for all)</label>
                <select name="target_classes[]" class="form-control" multiple style="height: 100px;">
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Hold CTRL/CMD to select multiple classes.</small>
            </div>

            <div class="form-group">
                <label class="form-label">Subject / Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. Holiday Notice">
            </div>

            <div class="form-group">
                <label class="form-label">Message Content <span class="text-danger">*</span></label>
                <textarea name="content" class="form-control" rows="8" required placeholder="Write your message here..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Attachment (Optional)</label>
                <input type="file" name="attachment" class="form-control">
                <small class="text-muted">Max size: 5MB (PDF, JPG, PNG)</small>
            </div>

            <div class="mt-4 border-top pt-4 text-right">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi-send"></i> Send Broadcast
                </button>
            </div>
        </form>
    </div>
</div>
