<?php
$pageTitle = 'Patient Dashboard';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('patient');

$userId = getUserId();

// Get upcoming appointments
$stmt = $pdo->prepare("
    SELECT a.*, d_user.full_name as doctor_name, doc.specialty
    FROM appointments a
    JOIN doctors doc ON a.doctor_id = doc.id
    JOIN users d_user ON doc.user_id = d_user.id
    WHERE a.patient_id = ? AND a.appointment_date >= CURDATE() AND a.status IN ('pending', 'confirmed')
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 5
");
$stmt->execute([$userId]);
$upcomingAppointments = $stmt->fetchAll();

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ?");
$stmt->execute([$userId]);
$totalApt = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND status = 'pending'");
$stmt->execute([$userId]);
$pendingApt = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$completedApt = $stmt->fetchColumn();

$totalDoctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="welcome-banner">
        <div>
            <h2>Hello, <?= htmlspecialchars(getUserName()) ?>! 👋</h2>
            <p>Welcome to your health dashboard. Book appointments and manage your healthcare.</p>
        </div>
        <i class="fas fa-heartbeat welcome-icon"></i>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-user-md"></i></div>
            <div class="stat-info">
                <h3><?= $totalDoctors ?></h3>
                <p>Available Doctors</p>
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
            <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3><?= $pendingApt ?></h3>
                <p>Pending</p>
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

    <div class="d-flex justify-between align-center mb-2 flex-wrap gap-2">
        <h2 style="font-size: 1.3rem;"><i class="fas fa-calendar-check" style="color: var(--primary-light);"></i> Upcoming Appointments</h2>
        <a href="/PamudiNew/patient/doctors.php" class="btn btn-primary"><i class="fas fa-plus"></i> Book New Appointment</a>
    </div>

    <?php if (count($upcomingAppointments) > 0): ?>
    <div class="appointment-list">
        <?php foreach ($upcomingAppointments as $apt): ?>
        <div class="appointment-card">
            <div class="appointment-info">
                <h4><?= htmlspecialchars($apt['doctor_name']) ?></h4>
                <div class="appointment-meta">
                    <span><i class="fas fa-stethoscope"></i> <?= htmlspecialchars($apt['specialty']) ?></span>
                    <span><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($apt['appointment_date'])) ?></span>
                    <span><i class="fas fa-clock"></i> <?= date('h:i A', strtotime($apt['appointment_time'])) ?></span>
                </div>
            </div>
            <span class="badge badge-<?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-calendar-plus"></i>
            <h3>No upcoming appointments</h3>
            <p>Browse our doctors and book your first appointment!</p>
            <a href="/PamudiNew/patient/doctors.php" class="btn btn-primary"><i class="fas fa-user-md"></i> Browse Doctors</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
