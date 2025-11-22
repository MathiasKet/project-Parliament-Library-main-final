<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Digital Assets</h1>
                <?php if ($this->isAdmin()): ?>
                    <a href="digital-assets.php?action=upload" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Upload New
                    </a>
                <?php endif; ?>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Search and Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="" method="get" class="row g-3">
                        <input type="hidden" name="page" value="1">
                        
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search assets..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <select name="category_id" class="form-select" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category->id ?>" <?= (isset($_GET['category_id']) && $_GET['category_id'] == $category->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <a href="digital-assets.php" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Assets Grid -->
            <?php if (empty($assets)): ?>
                <div class="alert alert-info">No digital assets found.</div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($assets as $asset): ?>
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?= $asset->is_public ? 'success' : 'warning' ?> me-2">
                                            <?= $asset->is_public ? 'Public' : 'Private' ?>
                                        </span>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="digital-assets.php?action=download&id=<?= $asset->id ?>">
                                                        <i class="fas fa-download me-2"></i>Download
                                                    </a>
                                                </li>
                                                <?php if ($this->isAdmin() || $asset->uploaded_by == $_SESSION['user_id']): ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $asset->id ?>">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <?php
                                    $iconClass = 'fa-file';
                                    $fileType = strtolower($asset->file_type);
                                    
                                    if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        $iconClass = 'fa-image';
                                    } elseif (in_array($fileType, ['pdf'])) {
                                        $iconClass = 'fa-file-pdf';
                                    } elseif (in_array($fileType, ['doc', 'docx'])) {
                                        $iconClass = 'fa-file-word';
                                    } elseif (in_array($fileType, ['xls', 'xlsx'])) {
                                        $iconClass = 'fa-file-excel';
                                    } elseif (in_array($fileType, ['ppt', 'pptx'])) {
                                        $iconClass = 'fa-file-powerpoint';
                                    }
                                    ?>
                                    <div class="display-1 text-muted mb-3">
                                        <i class="fas <?= $iconClass ?>"></i>
                                    </div>
                                    <h5 class="card-title"><?= htmlspecialchars($asset->title) ?></h5>
                                    <p class="card-text text-muted small">
                                        <?= !empty($asset->description) ? htmlspecialchars($asset->description) : 'No description' ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="badge bg-light text-dark">
                                            <?= strtoupper($asset->file_type) ?>
                                        </span>
                                        <small class="text-muted">
                                            <?= Utils::formatBytes($asset->file_size) ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="far fa-user me-1"></i>
                                            <?= htmlspecialchars($asset->uploaded_by_name) ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?= Utils::formatDate($asset->created_at) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Delete Confirmation Modal -->
                            <div class="modal fade" id="deleteModal<?= $asset->id ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirm Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete "<?= htmlspecialchars($asset->title) ?>"?</p>
                                            <p class="text-danger">This action cannot be undone.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="digital-assets.php?action=delete&id=<?= $asset->id ?>" class="btn btn-danger">
                                                <i class="fas fa-trash me-1"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($paginator->getTotalPages() > 1): ?>
                    <nav class="mt-4">
                        <?= $paginator->render('pagination justify-content-center', 'page-item', 'page-link') ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
