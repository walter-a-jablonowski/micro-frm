<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= $csrfToken ?>">
  <title>Register - MicroFrm</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="/lib/micro-frm/js/errorHandler.js"></script>
  <script src="/lib/micro-frm/js/ajax.js"></script>
</head>
<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow">
          <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Register</h3>
          </div>
          <div class="card-body">
            <form id="register-form">
              <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <div class="mb-3">
                <label for="confirm-password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm-password" name="confirm-password" required>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Register</button>
              </div>
            </form>
            
            <div class="mt-3 text-center">
              <a href="index.php?page=login">Already have an account? Login</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const registerForm = document.getElementById('register-form');
      
      registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        
        // Validate passwords match
        if (password !== confirmPassword) {
          ErrorHandler.displayError({
            message: 'Passwords do not match',
            type: 'Validation Error'
          });
          return;
        }
        
        // Send registration request
        const response = await Ajax.post('register', {
          name: name,
          email: email,
          password: password
        });
        
        if (response && response.success) {
          // Redirect to home page
          window.location.href = 'index.php?page=home';
        }
      });
    });
  </script>
</body>
</html>
