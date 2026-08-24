<!-- Monthly Attendance Report -->
<div class="card mb-4">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="<?= APP_URL ?>/attendance/report" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <div style="width: 180px;">
                <label class="form-label" style="font-size: 12px;">Class</label>
                <select class="form-control" name="class_id" id="reportClass" onchange="loadSections(this.value)">
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="width: 160px;">
                <label class="form-label" style="font-size: 12px;">Section</label>
                <select class="form-control" name="section_id" id="reportSection">
                    <option value="">Select</option>
                    <?php foreach ($sections as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $sectionId == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="width: 160px;">
                <label class="form-label" style="font-size: 12px;">Month</label>
                <input type="month" class="form-control" name="month" value="<?= $month ?>">
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-bar-chart"></i> Generate</button>
        </form>
    </div>
</div>

<?php if ($classId && $sectionId && !empty($students)): ?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <?= htmlspecialchars($selectedClass['name'] ?? '') ?> – <?= htmlspecialchars($selectedSection['name'] ?? '') ?>
            · <?= date('F Y', strtotime($month . '-01')) ?>
        </h3>
        <span style="font-size: 12px; color: var(--gray-400);"><?= count($students) ?> students</span>
    </div>
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px; min-width: 900px;">
            <thead>
                <tr style="background: var(--gray-50);">
                    <th style="padding: 8px 12px; text-align: left; position: sticky; left: 0; background: var(--gray-50); z-index: 2; min-width: 180px; border-right: 2px solid var(--gray-100);">Student</th>
                    <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                        <?php
                            $dayDate = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                            $dow = date('D', strtotime($dayDate));
                            $isSun = date('w', strtotime($dayDate)) == 0;
                        ?>
                        <th style="padding: 6px 2px; text-align: center; min-width: 28px; <?= $isSun ? 'background:#FFF3E0;' : '' ?>">
                            <div style="font-size: 10px; color: var(--gray-400);"><?= $dow[0] ?></div>
                            <div><?= $d ?></div>
                        </th>
                    <?php endfor; ?>
                    <th style="padding: 8px; text-align: center; min-width: 40px; border-left: 2px solid var(--gray-100);">P</th>
                    <th style="padding: 8px; text-align: center; min-width: 40px;">A</th>
                    <th style="padding: 8px; text-align: center; min-width: 40px;">%</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                    <?php
                        $pCount = 0; $aCount = 0; $total = 0;
                        foreach ($report[$s['id']] ?? [] as $st) {
                            $total++;
                            if ($st === 'present' || $st === 'late') $pCount++;
                            else $aCount++;
                        }
                        $pct = $total > 0 ? round(($pCount / $total) * 100) : 0;
                    ?>
                    <tr style="border-bottom: 1px solid var(--gray-50);">
                        <td style="padding: 8px 12px; font-weight: 600; position: sticky; left: 0; background: white; z-index: 1; border-right: 2px solid var(--gray-100);">
                            <?= $s['roll_number'] ? '<span style="color:var(--gray-400);font-weight:400;">' . $s['roll_number'] . '.</span> ' : '' ?>
                            <?= htmlspecialchars($s['full_name']) ?>
                        </td>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                            <?php
                                $dayDate = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                                $isSun = date('w', strtotime($dayDate)) == 0;
                                $st = $report[$s['id']][$d] ?? null;
                                $cellColors = ['present'=>['#4CAF50','P'],'absent'=>['#F44336','A'],'late'=>['#FF9800','L'],'half_day'=>['#2196F3','H'],'excused'=>['#9C27B0','E']];
                                $cc = $st ? ($cellColors[$st] ?? ['#999','?']) : null;
                            ?>
                            <td style="padding: 4px 2px; text-align: center; <?= $isSun ? 'background:#FFF8E1;' : '' ?>">
                                <?php if ($cc): ?>
                                    <span style="display: inline-block; width: 20px; height: 20px; border-radius: 4px; background: <?= $cc[0] ?>20; color: <?= $cc[0] ?>; font-size: 10px; font-weight: 700; line-height: 20px;"><?= $cc[1] ?></span>
                                <?php elseif (!$isSun): ?>
                                    <span style="color: var(--gray-200);">·</span>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                        <td style="padding: 8px; text-align: center; font-weight: 700; color: #4CAF50; border-left: 2px solid var(--gray-100);"><?= $pCount ?></td>
                        <td style="padding: 8px; text-align: center; font-weight: 700; color: #F44336;"><?= $aCount ?></td>
                        <td style="padding: 8px; text-align: center; font-weight: 700; color: <?= $pct >= 75 ? '#4CAF50' : ($pct >= 50 ? '#FF9800' : '#F44336') ?>;"><?= $pct ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php elseif ($classId && $sectionId): ?>
<div class="card"><div class="card-body" style="text-align:center;padding:48px;"><i class="bi bi-people" style="font-size:40px;color:var(--gray-300);"></i><h3 style="margin-top:12px;">No students found</h3></div></div>
<?php endif; ?>

<script>
function loadSections(classId) {
    const sel = document.getElementById('reportSection');
    sel.innerHTML = '<option value="">Loading...</option>';
    fetch('<?= APP_URL ?>/attendance/get-sections?class_id=' + classId)
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">Select</option>';
            data.forEach(s => { sel.innerHTML += '<option value="'+s.id+'">'+s.name+'</option>'; });
        });
}
</script>
