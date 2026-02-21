<?php
$pageTitle = 'Manage Appointments';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('admin');

$appointments = $pdo->query("
    SELECT a.*, u.full_name as patient_name, d_user.full_name as doctor_name, doc.specialty
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    JOIN doctors doc ON a.doctor_id = doc.id
    JOIN users d_user ON doc.user_id = d_user.id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-calendar-alt" style="color: var(--primary-light);"></i> All Appointments</h1>
        <p>View all appointments across the system</p>
    </div>

    <?php if (count($appointments) > 0): ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Specialty</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $i => $apt): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($apt['patient_name']) ?></strong></td>
                    <td><?= htmlspecialchars($apt['doctor_name']) ?></td>
                    <td><span class="badge badge-doctor"><?= htmlspecialchars($apt['specialty']) ?></span></td>
                    <td><?= date('M d, Y', strtotime($apt['appointment_date'])) ?></td>
                    <td><?= date('h:i A', strtotime($apt['appointment_time'])) ?></td>
                    <td><span class="badge badge-<?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span></td>
                    <td><?= htmlspecialchars($apt['notes'] ?: '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No appointments yet</h3>
            <p>Appointments will appear here once patients start booking.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
