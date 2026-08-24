<!-- Teacher Timetable View -->
<style>
    .teacher-tt { width: 100%; border-collapse: collapse; }
    .teacher-tt th, .teacher-tt td { border: 1px solid var(--gray-100); padding: 0; text-align: center; vertical-align: middle; }
    .teacher-tt th { padding: 10px 8px; font-size: 12px; font-weight: 700; color: var(--gray-600); background: var(--gray-50); }
    .teacher-tt .slot { min-height: 55px; padding: 8px 6px; }
    .teacher-tt .slot.has-class { background: #F8FFFE; }
    .teacher-tt .slot .cls { font-size: 12px; font-weight: 700; color: #7B1FA2; }
    .teacher-tt .slot .subj { font-size: 11px; color: #1f9e8b; font-weight: 600; margin-top: 2px; }
</style>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 700; margin: 0;">
            <i class="bi bi-person-workspace" style="color: #1f9e8b;"></i>
            <?= htmlspecialchars($teacher['full_name']) ?>'s Timetable
        </h3>
        <a href="<?= APP_URL ?>/timetable" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <?php $dayNames = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6]; ?>
        <table class="teacher-tt">
            <thead>
                <tr>
                    <th style="width: 70px;">Day</th>
                    <?php foreach ($periods as $p): ?>
                        <th style="min-width: 85px;">
                            <div><?= htmlspecialchars($p['short_name'] ?? $p['name']) ?></div>
                            <div style="font-weight: 400; font-size: 10px; color: var(--gray-400);">
                                <?= date('g:i', strtotime($p['start_time'])) ?>–<?= date('g:i', strtotime($p['end_time'])) ?>
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dayNames as $label => $num): ?>
                    <tr>
                        <td style="padding: 10px; font-weight: 700; font-size: 13px; background: var(--gray-50); color: var(--gray-600);"><?= $label ?></td>
                        <?php foreach ($periods as $p): ?>
                            <?php if ($p['period_type'] !== 'class'): ?>
                                <td><div class="slot" style="background: #FFF8E1;"><span style="font-size: 10px; color: #F9A825; font-weight: 600;"><?= $p['period_type'] === 'lunch' ? '🍽' : '☕' ?></span></div></td>
                            <?php else: ?>
                                <?php $entry = $schedule[$num][$p['id']] ?? null; ?>
                                <td>
                                    <div class="slot <?= $entry ? 'has-class' : '' ?>">
                                        <?php if ($entry): ?>
                                            <div class="cls"><?= htmlspecialchars($entry['class_name']) ?> <?= htmlspecialchars($entry['section_name']) ?></div>
                                            <div class="subj"><?= htmlspecialchars($entry['subject_code'] ?? $entry['subject_name']) ?></div>
                                        <?php else: ?>
                                            <span style="font-size: 11px; color: var(--gray-200);">Free</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
