<?php
$pageTitle = 'Manage Doctors';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
requireRole('admin');

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE id = ?");
    $stmt->execute([$deleteId]);
    $doctor = $stmt->fetch();
    if ($doctor) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$doctor['user_id']]);
        header('Location: /PamudiNew/admin/manage_doctors.php?success=Doctor removed successfully');
        exit;
    }
}

$doctors = $pdo->query("
    SELECT d.*, u.full_name, u.email, u.phone
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    ORDER BY d.created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1><i class="fas fa-users-cog" style="color: var(--primary-light);"></i> Manage Doctors</h1>
            <p>View and manage all registered doctors</p>
        </div>
        <a href="/PamudiNew/admin/add_doctor.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Doctor</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if (count($doctors) > 0): ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Specialty</th>
                    <th>Qualifications</th>
                    <th>Rating</th>
                    <th>Fee (Rs.)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctors as $i => $doc): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($doc['full_name']) ?></strong></td>
                    <td><?= htmlspecialchars($doc['email']) ?></td>
                    <td><span class="badge badge-doctor"><?= htmlspecialchars($doc['specialty']) ?></span></td>
                    <td><?= htmlspecialchars($doc['qualifications']) ?></td>
                    <td><span style="color: var(--warning);"><i class="fas fa-star"></i> <?= number_format($doc['rating'], 1) ?></span></td>
                    <td><?= number_format($doc['consultation_fee'], 2) ?></td>
                    <td>
                        <div class="actions">
                            <a href="/PamudiNew/admin/manage_doctors.php?delete=<?= $doc['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this doctor?')"><i class="fas fa-trash"></i></a>
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
            <i class="fas fa-user-md"></i>
            <h3>No doctors registered yet</h3>
            <p>Add your first doctor to get started.</p>
            <a href="/PamudiNew/admin/add_doctor.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Doctor</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
