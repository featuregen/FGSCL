
<div class="content-header">
    <h2 class="content-title">Noticeboard & Broadcasts</h2>
    <?php if (Session::hasPermission('communication.send')): ?>
    <a href="<?= APP_URL ?>/communication/create" class="btn btn-primary">
        <i class="bi-megaphone"></i> Compose Message
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Title & Content</th>
                    <th>Targets</th>
                    <th>Sent By</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($communications as $comm): ?>
                <tr>
                    <td>
                        <?php
                        $typeIcon = [
                            'notice' => '<i class="bi-clipboard text-info"></i> Notice',
                            'email' => '<i class="bi-envelope text-primary"></i> Email',
                            'sms' => '<i class="bi-chat-left-text text-success"></i> SMS',
                            'push' => '<i class="bi-bell text-warning"></i> Push'
                        ][$comm['type']] ?? $comm['type'];
                        echo $typeIcon;
                        ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($comm['title']) ?></strong>
                        <div class="text-xs text-muted text-truncate" style="max-width: 300px;">
                            <?= htmlspecialchars(strip_tags($comm['content'])) ?>
                        </div>
                        <?php if ($comm['attachment']): ?>
                            <a href="<?= APP_URL ?>/public/uploads/communication/<?= $comm['attachment'] ?>" target="_blank" class="text-xs text-primary"><i class="bi-paperclip"></i> Attachment</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        $roles = json_decode($comm['target_roles'], true) ?: [];
                        echo implode(', ', array_map('ucfirst', $roles));
                        ?>
                    </td>
                    <td><?= htmlspecialchars($comm['sent_by_name']) ?></td>
                    <td><?= date('d M, h:i A', strtotime($comm['created_at'])) ?></td>
                    <td>
                        <button class="btn-icon text-primary" onclick="viewMessage(<?= htmlspecialchars(json_encode($comm)) ?>)">
                            <i class="bi-eye"></i>
                        </button>
                        <?php if (Session::hasPermission('communication.delete')): ?>
                        <form action="<?= APP_URL ?>/communication/delete/<?= $comm['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this message?');">
                            <?= Session::csrfField() ?>
                            <button type="submit" class="btn-icon text-danger">
                                <i class="bi-trash"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($communications)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No messages found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Message Modal -->
<div id="viewMessageModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 600px; max-width: 95vw;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="modal-title" id="msgTitle" style="font-size: 16px; font-weight: 700; margin: 0;">Message</h3>
            <button class="btn-icon" onclick="closeModal('viewMessageModal')" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray-500);"><i class="bi-x"></i></button>
        </div>
        <div class="card-body">
            <div class="p-3 bg-light rounded mb-3" id="msgContent"></div>
            <div id="msgAttachment" class="mb-3"></div>
            <div class="text-xs text-muted">
                Sent by: <span id="msgSender"></span> on <span id="msgDate"></span>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
function viewMessage(comm) {
    document.getElementById('msgTitle').textContent = comm.title;
    document.getElementById('msgContent').innerHTML = comm.content.replace(/\n/g, '<br>');
    document.getElementById('msgSender').textContent = comm.sent_by_name;
    document.getElementById('msgDate').textContent = new Date(comm.created_at).toLocaleString();
    
    const attDiv = document.getElementById('msgAttachment');
    if (comm.attachment) {
        attDiv.innerHTML = `<a href="<?= APP_URL ?>/public/uploads/communication/${comm.attachment}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi-paperclip"></i> View Attachment</a>`;
    } else {
        attDiv.innerHTML = '';
    }
    
    openModal('viewMessageModal');
}
</script>
