<?php
$pageTitle = 'My Appointments';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('patient');

// Handle cancel
if (isset($_GET['cancel'])) {
    $cancelId = intval($_GET['cancel']);
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND patient_id = ? AND status IN ('pending', 'confirmed')");
    $stmt->execute([$cancelId, getUserId()]);
    header('Location: /PamudiNew/patient/my_appointments.php?success=Appointment cancelled successfully');
    exit;
}

$stmt = $pdo->prepare("
    SELECT a.*, d_user.full_name as doctor_name, doc.specialty, doc.consultation_fee
    FROM appointments a
    JOIN doctors doc ON a.doctor_id = doc.id
    JOIN users d_user ON doc.user_id = d_user.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([getUserId()]);
$appointments = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1><i class="fas fa-calendar-alt" style="color: var(--primary-light);"></i> My Appointments</h1>
            <p>View and manage all your appointments</p>
        </div>
        <a href="/PamudiNew/patient/doctors.php" class="btn btn-primary"><i class="fas fa-plus"></i> Book New</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if (count($appointments) > 0): ?>
    <div class="appointment-list">
        <?php foreach ($appointments as $apt): ?>
        <div class="appointment-card">
            <div class="appointment-info">
                <h4><?= htmlspecialchars($apt['doctor_name']) ?></h4>
                <div class="appointment-meta">
                    <span><i class="fas fa-stethoscope"></i> <?= htmlspecialchars($apt['specialty']) ?></span>
                    <span><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($apt['appointment_date'])) ?></span>
                    <span><i class="fas fa-clock"></i> <?= date('h:i A', strtotime($apt['appointment_time'])) ?></span>
                    <span><i class="fas fa-money-bill-wave"></i> Rs. <?= number_format($apt['consultation_fee'], 2) ?></span>
                </div>
                <?php if ($apt['notes']): ?>
                    <p style="margin-top: 0.5rem; color: var(--text-muted); font-size: 0.85rem;"><i class="fas fa-sticky-note"></i> <?= htmlspecialchars($apt['notes']) ?></p>
                <?php endif; ?>
            </div>
            <div class="d-flex align-center gap-1">
                <span class="badge badge-<?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span>
                <?php if (in_array($apt['status'], ['pending', 'confirmed'])): ?>
                    <a href="/PamudiNew/patient/my_appointments.php?cancel=<?= $apt['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this appointment?')">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No appointments yet</h3>
            <p>You haven't booked any appointments. Browse our doctors and book your first one!</p>
            <a href="/PamudiNew/patient/doctors.php" class="btn btn-primary"><i class="fas fa-user-md"></i> Browse Doctors</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
