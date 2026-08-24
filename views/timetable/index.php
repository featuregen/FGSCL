<!-- Timetable Hub -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <p style="color: var(--gray-500); margin: 0;">Manage school timetable and period slots</p>
    <a href="<?= APP_URL ?>/timetable/periods" class="btn btn-secondary">
        <i class="bi bi-clock"></i> Period Setup
    </a>
</div>

<?php if (empty($periods)): ?>
    <!-- No Periods Warning -->
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 48px;">
            <i class="bi bi-clock" style="font-size: 48px; color: var(--gray-300);"></i>
            <h3 style="margin-top: 16px;">Set Up Periods First</h3>
            <p style="color: var(--gray-500);">Before creating a timetable, you need to define the period/slot structure for your school.</p>
            <a href="<?= APP_URL ?>/timetable/periods" class="btn btn-primary"><i class="bi bi-clock"></i> Configure Periods</a>
        </div>
    </div>
<?php else: ?>
    <!-- Class Grid -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
                <i class="bi bi-calendar-week" style="color: #7B1FA2;"></i> Select Class & Section
            </h3>
        </div>
        <div class="card-body">
            <p style="font-size: 12px; color: var(--gray-400); margin-bottom: 16px;">Choose a class and section to view or edit the timetable</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                <?php foreach ($classes as $c): ?>
                    <?php
                        $sections = [];
                        if (!empty($c['sections_data'])) {
                            foreach (explode('|', $c['sections_data']) as $sd) {
                                [$sid, $sname] = explode(':', $sd);
                                $sections[] = ['id' => $sid, 'name' => $sname];
                            }
                        }
                    ?>
                    <div class="card" style="border: 1px solid var(--gray-100);">
                        <div class="card-body" style="padding: 16px;">
                            <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 12px;">
                                <i class="bi bi-mortarboard" style="color: #1f9e8b;"></i> <?= htmlspecialchars($c['name']) ?>
                            </h4>
                            <?php if (!empty($sections)): ?>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <?php foreach ($sections as $sec): ?>
                                        <a href="<?= APP_URL ?>/timetable/class-view?class_id=<?= $c['id'] ?>&section_id=<?= $sec['id'] ?>"
                                           class="btn btn-sm" style="background: #F3E5F5; color: #7B1FA2; border: none; font-weight: 600; padding: 6px 16px; border-radius: 8px;">
                                            Section <?= htmlspecialchars($sec['name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span style="font-size: 12px; color: var(--gray-400);">No sections</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($classes)): ?>
                <div style="text-align: center; padding: 32px; color: var(--gray-400);">
                    <i class="bi bi-building" style="font-size: 32px;"></i>
                    <p style="margin-top: 8px;">No classes found. Create classes in School Setup first.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Period Overview -->
    <div class="card" style="margin-top: 20px;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; margin: 0;"><i class="bi bi-clock" style="color: #E65100;"></i> Period Structure</h3>
            <a href="<?= APP_URL ?>/timetable/periods" style="font-size: 12px; color: #1565C0; text-decoration: none;">Edit Periods →</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div style="display: flex; overflow-x: auto; padding: 16px; gap: 8px;">
                <?php foreach ($periods as $p): ?>
                    <?php
                        $colors = ['class' => ['#E0F2F1','#1f9e8b'], 'break' => ['#FFF3E0','#E65100'], 'lunch' => ['#FFF8E1','#F9A825'], 'assembly' => ['#E8EAF6','#3F51B5']];
                        $pc = $colors[$p['period_type']] ?? ['#F5F5F5','#666'];
                    ?>
                    <div style="min-width: 100px; padding: 10px 14px; border-radius: 10px; background: <?= $pc[0] ?>; text-align: center; flex-shrink: 0;">
                        <div style="font-size: 12px; font-weight: 700; color: <?= $pc[1] ?>;"><?= $p['short_name'] ?? $p['name'] ?></div>
                        <div style="font-size: 10px; color: var(--gray-400); margin-top: 2px;">
                            <?= date('g:i A', strtotime($p['start_time'])) ?> – <?= date('g:i A', strtotime($p['end_time'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
