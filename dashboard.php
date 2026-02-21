<?php
$pageTitle = 'Doctor Dashboard';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('doctor');

$userId = getUserId();

// Get doctor profile
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE user_id = ?");
$stmt->execute([$userId]);
$doctor = $stmt->fetch();

if (!$doctor) {
    echo "Doctor profile not found.";
    exit;
}

// Today's appointments
$stmt = $pdo->prepare("
    SELECT a.*, u.full_name as patient_name, u.phone as patient_phone
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = ? AND a.appointment_date = CURDATE()
    ORDER BY a.appointment_time ASC
");
$stmt->execute([$doctor['id']]);
$todayAppointments = $stmt->fetchAll();

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ?");
$stmt->execute([$doctor['id']]);
$totalApt = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'pending'");
$stmt->execute([$doctor['id']]);
$pendingApt = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE()");
$stmt->execute([$doctor['id']]);
$todayCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'completed'");
$stmt->execute([$doctor['id']]);
$completedApt = $stmt->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="welcome-banner">
        <div>
            <h2>Good day, <?= htmlspecialchars(getUserName()) ?>! 🩺</h2>
            <p>Here's an overview of your schedule today</p>
        </div>
        <i class="fas fa-user-md welcome-icon"></i>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-info">
                <h3><?= $todayCount ?></h3>
                <p>Today's Appointments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3><?= $pendingApt ?></h3>
                <p>Pending Approval</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-info">
                <h3><?= $totalApt ?></h3>
                <p>Total Appointments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h3><?= $completedApt ?></h3>
                <p>Completed</p>
            </div>
        </div>
    </div>

    <h2 style="font-size: 1.3rem; margin-bottom: 1rem;"><i class="fas fa-calendar-day" style="color: var(--primary-light);"></i> Today's Schedule</h2>

    <?php if (count($todayAppointments) > 0): ?>
    <div class="appointment-list">
        <?php foreach ($todayAppointments as $apt): ?>
        <div class="appointment-card">
            <div class="appointment-info">
                <h4><?= htmlspecialchars($apt['patient_name']) ?></h4>
                <div class="appointment-meta">
                    <span><i class="fas fa-clock"></i> <?= date('h:i A', strtotime($apt['appointment_time'])) ?></span>
                    <?php if ($apt['patient_phone']): ?>
                        <span><i class="fas fa-phone"></i> <?= htmlspecialchars($apt['patient_phone']) ?></span>
                    <?php endif; ?>
                    <?php if ($apt['notes']): ?>
                        <span><i class="fas fa-sticky-note"></i> <?= htmlspecialchars($apt['notes']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <span class="badge badge-<?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-coffee"></i>
            <h3>No appointments today</h3>
            <p>Enjoy your free time! Check your full schedule in the Appointments page.</p>
            <a href="/PamudiNew/doctor/appointments.php" class="btn btn-secondary"><i class="fas fa-calendar-check"></i> View All Appointments</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
