<?php
$pageTitle = 'Add Doctor';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $specialty = trim($_POST['specialty'] ?? '');
    $qualifications = trim($_POST['qualifications'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $consultation_fee = floatval($_POST['consultation_fee'] ?? 0);
    $rating = floatval($_POST['rating'] ?? 0);

    if (empty($full_name) || empty($email) || empty($password) || empty($specialty) || empty($qualifications)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            try {
                $pdo->beginTransaction();

                // Create user account
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, 'doctor')");
                $stmt->execute([$full_name, $email, $hashed, $phone]);
                $userId = $pdo->lastInsertId();

                // Create doctor profile
                $stmt = $pdo->prepare("INSERT INTO doctors (user_id, specialty, qualifications, bio, consultation_fee, rating) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $specialty, $qualifications, $bio, $consultation_fee, $rating]);
                $newDoctorId = $pdo->lastInsertId();

                // Set default availability (Mon-Fri 5PM-9PM, Sat 9AM-1PM)
                $availStmt = $pdo->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, is_available) VALUES (?, ?, ?, ?, 1)");
                foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                    $availStmt->execute([$newDoctorId, $day, '17:00:00', '21:00:00']);
                }
                $availStmt->execute([$newDoctorId, 'Saturday', '09:00:00', '13:00:00']);

                $pdo->commit();
                $success = "Doctor '$full_name' has been registered successfully with default availability!";

                // Clear form
                $full_name = $email = $phone = $specialty = $qualifications = $bio = '';
                $consultation_fee = 0;
                $rating = 0;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Failed to register doctor. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-user-md" style="color: var(--primary-light);"></i> Register New Doctor</h1>
        <p>Add a new doctor to the MediCare team</p>
    </div>

    <div class="form-container wide">
        <div class="form-card">
            <?php if ($error): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <h3 style="margin-bottom: 1rem; color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fas fa-id-card" style="color: var(--primary-light);"></i> Account Information
                </h3>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name *</label>
                        <input type="text" name="full_name" class="form-control" placeholder="Dr. Full Name" value="<?= htmlspecialchars($full_name ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="doctor@medicare.com" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone</label>
                        <input type="tel" name="phone" class="form-control" placeholder="Phone number" value="<?= htmlspecialchars($phone ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="Set a password" required>
                    </div>
                </div>

                <h3 style="margin: 1.5rem 0 1rem; color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fas fa-stethoscope" style="color: var(--primary-light);"></i> Professional Details
                </h3>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-heartbeat"></i> Specialty *</label>
                        <select name="specialty" class="form-control" required>
                            <option value="">Select Specialty</option>
                            <option value="Cardiology" <?= ($specialty ?? '') === 'Cardiology' ? 'selected' : '' ?>>Cardiology</option>
                            <option value="Neurology" <?= ($specialty ?? '') === 'Neurology' ? 'selected' : '' ?>>Neurology</option>
                            <option value="Dermatology" <?= ($specialty ?? '') === 'Dermatology' ? 'selected' : '' ?>>Dermatology</option>
                            <option value="Orthopedics" <?= ($specialty ?? '') === 'Orthopedics' ? 'selected' : '' ?>>Orthopedics</option>
                            <option value="Pediatrics" <?= ($specialty ?? '') === 'Pediatrics' ? 'selected' : '' ?>>Pediatrics</option>
                            <option value="Gynecology" <?= ($specialty ?? '') === 'Gynecology' ? 'selected' : '' ?>>Gynecology</option>
                            <option value="Ophthalmology" <?= ($specialty ?? '') === 'Ophthalmology' ? 'selected' : '' ?>>Ophthalmology</option>
                            <option value="ENT" <?= ($specialty ?? '') === 'ENT' ? 'selected' : '' ?>>ENT</option>
                            <option value="General Medicine" <?= ($specialty ?? '') === 'General Medicine' ? 'selected' : '' ?>>General Medicine</option>
                            <option value="Psychiatry" <?= ($specialty ?? '') === 'Psychiatry' ? 'selected' : '' ?>>Psychiatry</option>
                            <option value="Urology" <?= ($specialty ?? '') === 'Urology' ? 'selected' : '' ?>>Urology</option>
                            <option value="Oncology" <?= ($specialty ?? '') === 'Oncology' ? 'selected' : '' ?>>Oncology</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-graduation-cap"></i> Qualifications *</label>
                        <input type="text" name="qualifications" class="form-control" placeholder="e.g. MBBS, MD, FACC" value="<?= htmlspecialchars($qualifications ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-money-bill-wave"></i> Consultation Fee (Rs.)</label>
                        <input type="number" name="consultation_fee" class="form-control" placeholder="e.g. 2500" value="<?= htmlspecialchars($consultation_fee ?? '') ?>" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-star"></i> Initial Rating (0-5)</label>
                        <input type="number" name="rating" class="form-control" placeholder="e.g. 4.5" value="<?= htmlspecialchars($rating ?? '') ?>" step="0.1" min="0" max="5">
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-file-medical-alt"></i> Bio / About</label>
                    <textarea name="bio" class="form-control" placeholder="Brief description about the doctor's experience and expertise..."><?= htmlspecialchars($bio ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 0.5rem;">
                    <i class="fas fa-user-plus"></i> Register Doctor
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
