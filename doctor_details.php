<?php
$pageTitle = 'Doctor Details';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('patient');

$doctorId = intval($_GET['id'] ?? 0);
if (!$doctorId) {
    header('Location: /PamudiNew/patient/doctors.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT d.*, u.full_name, u.email, u.phone
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    WHERE d.id = ?
");
$stmt->execute([$doctorId]);
$doctor = $stmt->fetch();

if (!$doctor) {
    header('Location: /PamudiNew/patient/doctors.php');
    exit;
}

// Get availability
$stmt = $pdo->prepare("
    SELECT * FROM doctor_availability 
    WHERE doctor_id = ? AND is_available = 1 
    ORDER BY FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), start_time
");
$stmt->execute([$doctorId]);
$availability = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div style="margin-bottom: 1.5rem;">
        <a href="/PamudiNew/patient/doctors.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Doctors</a>
    </div>

    <div class="doctor-detail">
        <!-- Profile Card -->
        <div class="doctor-profile-card">
            <div class="doctor-profile-image">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="doctor-profile-info">
                <h2><?= htmlspecialchars($doctor['full_name']) ?></h2>
                <div class="doctor-specialty" style="justify-content: center; margin-top: 0.3rem;">
                    <i class="fas fa-stethoscope"></i> <?= htmlspecialchars($doctor['specialty']) ?>
                </div>
                <div class="doctor-rating" style="justify-content: center; margin: 1rem 0;">
                    <div class="stars">
                        <?php
                        $rating = floatval($doctor['rating']);
                        for ($i = 1; $i <= 5; $i++):
                            if ($i <= floor($rating)): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($i - 0.5 <= $rating): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star empty"></i>
                            <?php endif;
                        endfor; ?>
                    </div>
                    <span class="rating-value"><?= number_format($rating, 1) ?></span>
                </div>
                <div style="margin: 1rem 0;">
                    <span class="fee-amount" style="font-size: 1.3rem;">Rs. <?= number_format($doctor['consultation_fee'], 2) ?></span>
                    <br><span class="fee-label">per consultation</span>
                </div>
                <a href="/PamudiNew/patient/book_appointment.php?doctor_id=<?= $doctor['id'] ?>" class="btn btn-primary btn-lg" style="width: 100%;">
                    <i class="fas fa-calendar-plus"></i> Book Appointment
                </a>
            </div>
        </div>

        <!-- Info Section -->
        <div class="doctor-info-section">
            <div class="info-block">
                <h3><i class="fas fa-graduation-cap"></i> Qualifications</h3>
                <p><?= htmlspecialchars($doctor['qualifications']) ?></p>
            </div>
            <div class="info-block">
                <h3><i class="fas fa-heartbeat"></i> Specialty</h3>
                <p><?= htmlspecialchars($doctor['specialty']) ?></p>
            </div>
            <div class="info-block">
                <h3><i class="fas fa-user-md"></i> About</h3>
                <p><?= nl2br(htmlspecialchars($doctor['bio'] ?: 'No bio available.')) ?></p>
            </div>
            <div class="info-block">
                <h3><i class="fas fa-star"></i> Rating</h3>
                <div class="doctor-rating">
                    <div class="stars" style="font-size: 1.2rem;">
                        <?php
                        for ($i = 1; $i <= 5; $i++):
                            if ($i <= floor($rating)): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($i - 0.5 <= $rating): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star empty"></i>
                            <?php endif;
                        endfor; ?>
                    </div>
                    <span class="rating-value" style="font-size: 1.2rem;"><?= number_format($rating, 1) ?> / 5.0</span>
                </div>
            </div>
            <div class="info-block">
                <h3><i class="fas fa-envelope"></i> Contact</h3>
                <p>
                    <i class="fas fa-envelope" style="color: var(--text-muted); margin-right: 6px;"></i> <?= htmlspecialchars($doctor['email']) ?><br>
                    <?php if ($doctor['phone']): ?>
                        <i class="fas fa-phone" style="color: var(--text-muted); margin-right: 6px;"></i> <?= htmlspecialchars($doctor['phone']) ?>
                    <?php endif; ?>
                </p>
            </div>

            <div class="info-block">
                <h3><i class="fas fa-clock"></i> Available Schedule</h3>
                <?php if (count($availability) > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.6rem;">
                    <?php foreach ($availability as $slot): ?>
                    <div style="background: rgba(13,148,136,0.08); border: 1px solid rgba(13,148,136,0.15); border-radius: 8px; padding: 10px 14px; font-size: 0.85rem;">
                        <strong style="color: var(--text-primary); display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-calendar-day" style="color: var(--primary-light); font-size: 0.75rem;"></i>
                            <?= htmlspecialchars($slot['day_of_week']) ?>
                        </strong>
                        <span style="color: var(--primary-light); font-size: 0.82rem;">
                            <?= date('g:i A', strtotime($slot['start_time'])) ?> - <?= date('g:i A', strtotime($slot['end_time'])) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color: var(--text-muted);">No availability set. Please contact the clinic for scheduling.</p>
                <?php endif; ?>
            </div>

            <div style="margin-top: 2rem;">
                <a href="/PamudiNew/patient/book_appointment.php?doctor_id=<?= $doctor['id'] ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-calendar-plus"></i> Book Appointment with <?= htmlspecialchars($doctor['full_name']) ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
