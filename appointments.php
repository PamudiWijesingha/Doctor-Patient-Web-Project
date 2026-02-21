<?php
$pageTitle = 'My Appointments';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('doctor');

$userId = getUserId();
$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$userId]);
$doctor = $stmt->fetch();

// Handle status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $aptId = intval($_GET['id']);
    $action = $_GET['action'];
    $validActions = ['confirm' => 'confirmed', 'complete' => 'completed', 'cancel' => 'cancelled'];
    
    if (isset($validActions[$action])) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
        $stmt->execute([$validActions[$action], $aptId, $doctor['id']]);
        header('Location: /PamudiNew/doctor/appointments.php?success=Appointment ' . $validActions[$action] . ' successfully');
        exit;
    }
}

$stmt = $pdo->prepare("
    SELECT a.*, u.full_name as patient_name, u.phone as patient_phone, u.email as patient_email
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$doctor['id']]);
$appointments = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-calendar-check" style="color: var(--primary-light);"></i> My Appointments</h1>
        <p>Manage all your patient appointments</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if (count($appointments) > 0): ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $apt): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($apt['patient_name']) ?></strong>
                        <br><small style="color: var(--text-muted);"><?= htmlspecialchars($apt['patient_phone'] ?: $apt['patient_email']) ?></small>
                    </td>
                    <td><?= date('M d, Y', strtotime($apt['appointment_date'])) ?></td>
                    <td><?= date('h:i A', strtotime($apt['appointment_time'])) ?></td>
                    <td><?= htmlspecialchars($apt['notes'] ?: '-') ?></td>
                    <td><span class="badge badge-<?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span></td>
                    <td>
                        <div class="actions">
                            <?php if ($apt['status'] === 'pending'): ?>
                                <a href="?action=confirm&id=<?= $apt['id'] ?>" class="btn btn-sm btn-success" title="Confirm"><i class="fas fa-check"></i></a>
                                <a href="?action=cancel&id=<?= $apt['id'] ?>" class="btn btn-sm btn-danger" title="Cancel" onclick="return confirm('Cancel this appointment?')"><i class="fas fa-times"></i></a>
                            <?php elseif ($apt['status'] === 'confirmed'): ?>
                                <a href="?action=complete&id=<?= $apt['id'] ?>" class="btn btn-sm btn-success" title="Mark Complete"><i class="fas fa-check-double"></i></a>
                                <a href="?action=cancel&id=<?= $apt['id'] ?>" class="btn btn-sm btn-danger" title="Cancel" onclick="return confirm('Cancel this appointment?')"><i class="fas fa-times"></i></a>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.82rem;">—</span>
                            <?php endif; ?>
                        </div>
                    </td>
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
            <p>You don't have any appointments scheduled. Patients can book appointments from your profile.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
