<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= $csrfToken ?>">
  <title>Home - MicroFrm</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="/lib/micro-frm/js/errorHandler.js"></script>
  <script src="/lib/micro-frm/js/ajax.js"></script>
</head>
<body>
  <!-- Header -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">MicroFrm</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link active" href="index.php">Home</a>
          </li>
        </ul>
        <div class="d-flex">
          <span class="navbar-text me-3">
            <?php if( $isAnonymous ): ?>
              Anonymous User
            <?php else: ?>
              <?= htmlspecialchars($name) ?>
              <?php if( $email ): ?>
                (<?= htmlspecialchars($email) ?>)
              <?php endif; ?>
            <?php endif; ?>
          </span>
          <a href="index.php?page=logout" class="btn btn-outline-light">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Content -->
  <div class="container mt-4">
    <div class="card">
      <div class="card-header">
        <h3>Welcome to MicroFrm</h3>
      </div>
      <div class="card-body">
        <h4>Hello, <?= htmlspecialchars($name) ?>!</h4>
        <p>You are logged in using: <strong><?= ucfirst($loginMethod) ?></strong></p>
        
        <div class="alert alert-info mt-4">
          <h5>About MicroFrm</h5>
          <p>This is a PHP micro framework with minimal functionality for web applications.</p>
          <p>Features include:</p>
          <ul>
            <li>Multiple login methods (Email, Google, Auth0, Unique URL)</li>
            <li>Session management</li>
            <li>User management</li>
            <li>Error handling</li>
            <li>Logging</li>
            <li>Configuration via YAML</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
