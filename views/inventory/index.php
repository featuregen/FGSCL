<div class="content-header">
    <h2 class="content-title">Inventory Management</h2>
    <button class="btn btn-primary" onclick="openModal('createModal')"><i class="bi-plus"></i> Add New</button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name / Details</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="4" class="text-center text-muted py-4">No records found.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="createModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: flex-end;">
    <div class="card" style="width: 400px; max-width: 100vw; height: 100vh; margin: 0; border-radius: 0; display: flex; flex-direction: column;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px;">
            <h3 style="font-size: 16px; margin: 0;">Add New</h3>
            <button class="btn-icon" onclick="document.getElementById('createModal').style.display='none'"><i class="bi-x"></i></button>
        </div>
        <div class="card-body" style="padding: 20px;">
            <p class="text-muted">Form fields will go here.</p>
        </div>
    </div>
</div>
<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
</script>