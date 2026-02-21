<?php
$pageTitle = 'Manage Patients';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('admin');

$patients = $pdo->query("
    SELECT u.*, 
    (SELECT COUNT(*) FROM appointments WHERE patient_id = u.id) as appointment_count
    FROM users u 
    WHERE u.role = 'patient' 
    ORDER BY u.created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-users" style="color: var(--accent-light);"></i> Manage Patients</h1>
        <p>View all registered patients</p>
    </div>

    <?php if (count($patients) > 0): ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Appointments</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $i => $patient): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($patient['full_name']) ?></strong></td>
                    <td><?= htmlspecialchars($patient['email']) ?></td>
                    <td><?= htmlspecialchars($patient['phone'] ?: 'N/A') ?></td>
                    <td><span class="badge badge-doctor"><?= $patient['appointment_count'] ?></span></td>
                    <td><?= date('M d, Y', strtotime($patient['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <h3>No patients registered yet</h3>
            <p>Patients will appear here once they sign up.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
