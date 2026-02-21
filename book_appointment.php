<?php
$pageTitle = 'Book Appointment';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('patient');

$doctorId = intval($_GET['doctor_id'] ?? 0);
if (!$doctorId) {
    header('Location: /PamudiNew/patient/doctors.php');
    exit;
}

// Get doctor info
$stmt = $pdo->prepare("
    SELECT d.*, u.full_name
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

// Get doctor's availability
$stmt = $pdo->prepare("
    SELECT * FROM doctor_availability 
    WHERE doctor_id = ? AND is_available = 1 
    ORDER BY FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), start_time
");
$stmt->execute([$doctorId]);
$availability = $stmt->fetchAll();

// Build available days map (day_name => [{start, end}])
$availableDays = [];
$availableDayNumbers = [];
$dayNumberMap = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 0];
foreach ($availability as $slot) {
    $day = $slot['day_of_week'];
    $availableDays[$day][] = ['start' => $slot['start_time'], 'end' => $slot['end_time']];
    $availableDayNumbers[$dayNumberMap[$day]] = true;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['appointment_date'] ?? '';
    $time = $_POST['appointment_time'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($date) || empty($time)) {
        $error = 'Please select both date and time.';
    } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
        $error = 'Please select a future date.';
    } else {
        // Check if the selected day is available
        $dayName = date('l', strtotime($date));
        if (!isset($availableDays[$dayName])) {
            $error = "The doctor is not available on {$dayName}s. Please choose another date.";
        } else {
            // Check if the selected time falls within available slots
            $timeValid = false;
            foreach ($availableDays[$dayName] as $slot) {
                if ($time >= $slot['start'] && $time < $slot['end']) {
                    $timeValid = true;
                    break;
                }
            }
            if (!$timeValid) {
                $error = 'The selected time is outside the doctor\'s available hours for this day.';
            } else {
                // Check if slot is already booked
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM appointments 
                    WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'
                ");
                $stmt->execute([$doctorId, $date, $time]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'This time slot is already booked. Please choose another time.';
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, notes) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([getUserId(), $doctorId, $date, $time, $notes]);
                    $success = 'Appointment booked successfully! You will be notified when the doctor confirms.';
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div style="margin-bottom: 1.5rem;">
        <a href="/PamudiNew/patient/doctor_details.php?id=<?= $doctorId ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Doctor Profile</a>
    </div>

    <div class="form-container wide">
        <div class="form-card">
            <div class="text-center mb-3">
                <i class="fas fa-calendar-plus" style="font-size: 2.5rem; color: var(--primary-light);"></i>
            </div>
            <h2>Book Appointment</h2>
            <p class="subtitle">with <?= htmlspecialchars($doctor['full_name']) ?> — <?= htmlspecialchars($doctor['specialty']) ?></p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- Doctor Info Card -->
            <div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.5rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div class="stat-icon primary"><i class="fas fa-user-md"></i></div>
                    <div>
                        <strong><?= htmlspecialchars($doctor['full_name']) ?></strong><br>
                        <span style="color: var(--text-secondary); font-size: 0.85rem;"><?= htmlspecialchars($doctor['specialty']) ?> • Rs. <?= number_format($doctor['consultation_fee'], 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Available Schedule -->
            <div class="card" style="margin-bottom: 1.5rem; padding: 1.2rem 1.5rem;">
                <h3 style="font-size: 0.9rem; color: var(--primary-light); margin-bottom: 0.8rem; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-clock"></i> Available Schedule
                </h3>
                <?php if (count($availability) > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.6rem;">
                    <?php
                    $displayedDays = [];
                    foreach ($availability as $slot):
                        $day = $slot['day_of_week'];
                        if (in_array($day, $displayedDays)) continue;
                        $displayedDays[] = $day;
                        $startFormatted = date('g:i A', strtotime($slot['start_time']));
                        $endFormatted = date('g:i A', strtotime($slot['end_time']));
                    ?>
                    <div style="background: rgba(13,148,136,0.08); border: 1px solid rgba(13,148,136,0.15); border-radius: 8px; padding: 8px 12px; font-size: 0.85rem;">
                        <strong style="color: var(--text-primary);"><?= $day ?></strong><br>
                        <span style="color: var(--primary-light);"><?= $startFormatted ?> - <?= $endFormatted ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color: var(--text-muted); font-size: 0.85rem;">No availability set. Please contact the clinic.</p>
                <?php endif; ?>
            </div>

            <?php if (!$success): ?>
            <form method="POST" action="" id="bookingForm">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Appointment Date *</label>
                        <input type="date" name="appointment_date" id="appointmentDate" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($date ?? '') ?>" required>
                        <small id="dayMessage" style="color: var(--text-muted); font-size: 0.8rem; margin-top: 4px; display: block;"></small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Appointment Time *</label>
                        <select name="appointment_time" id="appointmentTime" class="form-control" required>
                            <option value="">Select a date first</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-notes-medical"></i> Notes (Optional)</label>
                    <textarea name="notes" class="form-control" placeholder="Describe your symptoms or reason for visit..."><?= htmlspecialchars($notes ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 0.5rem;">
                    <i class="fas fa-check-circle"></i> Confirm Booking
                </button>
            </form>

            <div id="availabilityData" data-availability='<?= json_encode($availableDays) ?>' style="display:none;"></div>
            <script src="/PamudiNew/assets/js/booking.js"></script>

            <?php else: ?>
                <div class="text-center mt-2">
                    <a href="/PamudiNew/patient/my_appointments.php" class="btn btn-primary"><i class="fas fa-calendar-alt"></i> View My Appointments</a>
                    <a href="/PamudiNew/patient/doctors.php" class="btn btn-secondary"><i class="fas fa-user-md"></i> Browse More Doctors</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
