<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('admin');

// Get stats
$totalDoctors = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'")->fetchColumn();
$totalPatients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn();
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$pendingAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();
$todayAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()")->fetchColumn();
$completedAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'completed'")->fetchColumn();

// Recent appointments
$recentAppointments = $pdo->query("
    SELECT a.*, u.full_name as patient_name, d_user.full_name as doctor_name, doc.specialty
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    JOIN doctors doc ON a.doctor_id = doc.id
    JOIN users d_user ON doc.user_id = d_user.id
    ORDER BY a.created_at DESC
    LIMIT 5
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="welcome-banner">
        <div>
            <h2>Welcome, Admin!</h2>
            <p>Manage your medical center from this dashboard</p>
        </div>
        <i class="fas fa-shield-alt welcome-icon"></i>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-user-md"></i></div>
            <div class="stat-info">
                <h3><?= $totalDoctors ?></h3>
                <p>Total Doctors</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon accent"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3><?= $totalPatients ?></h3>
                <p>Total Patients</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3><?= $pendingAppointments ?></h3>
                <p>Pending Appointments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-info">
                <h3><?= $todayAppointments ?></h3>
                <p>Today's Appointments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-info">
                <h3><?= $totalAppointments ?></h3>
                <p>Total Appointments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h3><?= $completedAppointments ?></h3>
                <p>Completed</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history"></i> Recent Appointments</h3>
            <a href="/PamudiNew/admin/manage_appointments.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <?php if (count($recentAppointments) > 0): ?>
        <div class="table-container" style="border: none; border-radius: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentAppointments as $apt): ?>
                    <tr>
                        <td><?= htmlspecialchars($apt['patient_name']) ?></td>
                        <td><?= htmlspecialchars($apt['doctor_name']) ?></td>
                        <td><?= date('M d, Y', strtotime($apt['appointment_date'])) ?></td>
                        <td><?= date('h:i A', strtotime($apt['appointment_time'])) ?></td>
                        <td><span class="badge badge-<?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No appointments yet</h3>
            <p>Appointments will appear here once patients start booking.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
