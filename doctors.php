<?php
$pageTitle = 'Browse Doctors';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('patient');

// Get filter
$specialty_filter = $_GET['specialty'] ?? '';
$search = $_GET['search'] ?? '';

$query = "
    SELECT d.*, u.full_name, u.email
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    WHERE 1=1
";
$params = [];

if ($specialty_filter) {
    $query .= " AND d.specialty = ?";
    $params[] = $specialty_filter;
}
if ($search) {
    $query .= " AND (u.full_name LIKE ? OR d.specialty LIKE ? OR d.qualifications LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY d.rating DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$doctors = $stmt->fetchAll();

// Get all specialties for filter
$specialties = $pdo->query("SELECT DISTINCT specialty FROM doctors ORDER BY specialty")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-user-md" style="color: var(--primary-light);"></i> Our Doctors</h1>
        <p>Browse our team of expert healthcare professionals</p>
    </div>

    <div class="filter-bar">
        <form method="GET" action="" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%;">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Search doctors by name, specialty..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <select name="specialty" class="form-control" style="max-width: 220px;">
                <option value="">All Specialties</option>
                <?php foreach ($specialties as $spec): ?>
                    <option value="<?= htmlspecialchars($spec) ?>" <?= $specialty_filter === $spec ? 'selected' : '' ?>><?= htmlspecialchars($spec) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <?php if ($search || $specialty_filter): ?>
                <a href="/PamudiNew/patient/doctors.php" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (count($doctors) > 0): ?>
    <div class="doctors-grid">
        <?php foreach ($doctors as $doc): ?>
        <div class="doctor-card">
            <div class="doctor-card-image">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="doctor-card-body">
                <h3><?= htmlspecialchars($doc['full_name']) ?></h3>
                <div class="doctor-specialty">
                    <i class="fas fa-stethoscope"></i> <?= htmlspecialchars($doc['specialty']) ?>
                </div>
                <div class="doctor-qualifications">
                    <i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($doc['qualifications']) ?>
                </div>
                <div class="doctor-rating">
                    <div class="stars">
                        <?php
                        $rating = floatval($doc['rating']);
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
                <div class="doctor-fee">
                    <div>
                        <span class="fee-amount">Rs. <?= number_format($doc['consultation_fee'], 2) ?></span>
                        <br><span class="fee-label">Consultation Fee</span>
                    </div>
                    <a href="/PamudiNew/patient/doctor_details.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>No doctors found</h3>
            <p>Try adjusting your search or filter criteria.</p>
            <a href="/PamudiNew/patient/doctors.php" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset Filters</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
