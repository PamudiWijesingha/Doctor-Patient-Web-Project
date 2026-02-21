<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('doctor');

$userId = getUserId();

// Get doctor + user info
$stmt = $pdo->prepare("
    SELECT d.*, u.full_name, u.email, u.phone
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    WHERE d.user_id = ?
");
$stmt->execute([$userId]);
$doctor = $stmt->fetch();

// Get availability
$stmt = $pdo->prepare("
    SELECT * FROM doctor_availability 
    WHERE doctor_id = ? 
    ORDER BY FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), start_time
");
$stmt->execute([$doctor['id']]);
$availability = $stmt->fetchAll();

// Build availability map
$availMap = [];
foreach ($availability as $slot) {
    $availMap[$slot['day_of_week']] = [
        'start' => $slot['start_time'],
        'end' => $slot['end_time'],
        'is_available' => $slot['is_available']
    ];
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';

    if ($action === 'update_profile') {
        $bio = trim($_POST['bio'] ?? '');
        $qualifications = trim($_POST['qualifications'] ?? '');
        $consultation_fee = floatval($_POST['consultation_fee'] ?? 0);
        $phone = trim($_POST['phone'] ?? '');

        try {
            $stmt = $pdo->prepare("UPDATE doctors SET bio = ?, qualifications = ?, consultation_fee = ? WHERE user_id = ?");
            $stmt->execute([$bio, $qualifications, $consultation_fee, $userId]);

            $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?");
            $stmt->execute([$phone, $userId]);

            $success = 'Profile updated successfully!';
        } catch (Exception $e) {
            $error = 'Failed to update profile.';
        }
    } elseif ($action === 'update_availability') {
        try {
            // Delete existing availability
            $pdo->prepare("DELETE FROM doctor_availability WHERE doctor_id = ?")->execute([$doctor['id']]);

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $insertStmt = $pdo->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, is_available) VALUES (?, ?, ?, ?, 1)");

            foreach ($days as $day) {
                if (isset($_POST['day_' . $day]) && $_POST['day_' . $day] == '1') {
                    $start = $_POST['start_' . $day] ?? '17:00';
                    $end = $_POST['end_' . $day] ?? '21:00';
                    $insertStmt->execute([$doctor['id'], $day, $start . ':00', $end . ':00']);
                }
            }

            $success = 'Availability updated successfully!';
        } catch (Exception $e) {
            $error = 'Failed to update availability.';
        }
    }

    // Refresh data
    $stmt = $pdo->prepare("SELECT d.*, u.full_name, u.email, u.phone FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.user_id = ?");
    $stmt->execute([$userId]);
    $doctor = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM doctor_availability WHERE doctor_id = ? ORDER BY FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), start_time");
    $stmt->execute([$doctor['id']]);
    $availability = $stmt->fetchAll();
    $availMap = [];
    foreach ($availability as $slot) {
        $availMap[$slot['day_of_week']] = ['start' => $slot['start_time'], 'end' => $slot['end_time'], 'is_available' => $slot['is_available']];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-user" style="color: var(--primary-light);"></i> My Profile</h1>
        <p>View and update your professional profile & availability</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Profile Form -->
        <div class="form-card">
            <div class="text-center mb-3">
                <div class="stat-icon primary" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto 1rem;">
                    <i class="fas fa-user-md"></i>
                </div>
                <h2><?= htmlspecialchars($doctor['full_name']) ?></h2>
                <div class="doctor-specialty" style="justify-content: center;">
                    <i class="fas fa-stethoscope"></i> <?= htmlspecialchars($doctor['specialty']) ?>
                </div>
                <div class="doctor-rating" style="justify-content: center; margin-top: 0.5rem;">
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
            </div>

            <form method="POST" action="">
                <input type="hidden" name="action" value="update_profile">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email (read-only)</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($doctor['email']) ?>" readonly style="opacity: 0.6;">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($doctor['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-money-bill-wave"></i> Fee (Rs.)</label>
                        <input type="number" name="consultation_fee" class="form-control" value="<?= htmlspecialchars($doctor['consultation_fee']) ?>" step="0.01" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-graduation-cap"></i> Qualifications</label>
                    <input type="text" name="qualifications" class="form-control" value="<?= htmlspecialchars($doctor['qualifications']) ?>">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-file-medical-alt"></i> Bio</label>
                    <textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($doctor['bio'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>

        <!-- Availability Form -->
        <div class="form-card">
            <h2 style="font-size: 1.3rem; margin-bottom: 0.3rem;"><i class="fas fa-clock" style="color: var(--primary-light);"></i> Available Schedule</h2>
            <p class="subtitle" style="margin-bottom: 1.5rem;">Set the days and hours you're available for appointments</p>

            <form method="POST" action="">
                <input type="hidden" name="action" value="update_availability">

                <?php
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                foreach ($days as $day):
                    $isChecked = isset($availMap[$day]);
                    $startTime = $isChecked ? substr($availMap[$day]['start'], 0, 5) : '17:00';
                    $endTime = $isChecked ? substr($availMap[$day]['end'], 0, 5) : '21:00';
                ?>
                <div style="background: <?= $isChecked ? 'rgba(13,148,136,0.08)' : 'rgba(100,116,139,0.05)' ?>; border: 1px solid <?= $isChecked ? 'rgba(13,148,136,0.2)' : 'var(--border)' ?>; border-radius: 8px; padding: 10px 14px; margin-bottom: 0.6rem; transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; min-width: 130px; margin: 0;">
                            <input type="checkbox" name="day_<?= $day ?>" value="1" <?= $isChecked ? 'checked' : '' ?>
                                   style="width: 18px; height: 18px; accent-color: var(--primary);">
                            <strong style="color: var(--text-primary); font-size: 0.9rem;"><?= $day ?></strong>
                        </label>
                        <div style="display: flex; align-items: center; gap: 6px; flex: 1;">
                            <input type="time" name="start_<?= $day ?>" value="<?= $startTime ?>" class="form-control" style="padding: 6px 10px; font-size: 0.85rem;">
                            <span style="color: var(--text-muted);">to</span>
                            <input type="time" name="end_<?= $day ?>" value="<?= $endTime ?>" class="form-control" style="padding: 6px 10px; font-size: 0.85rem;">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-accent" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-calendar-check"></i> Update Availability
                </button>
            </form>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .container > div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
