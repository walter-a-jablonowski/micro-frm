<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= $csrfToken ?>">
  <title>Login - MicroFrm</title>
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
            <h3 class="mb-0">Login</h3>
          </div>
          <div class="card-body">
            <?php if( in_array('email', $loginMethods) ): ?>
              <form id="login-form">
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                  <button type="submit" class="btn btn-primary">Login</button>
                </div>
              </form>
              
              <div class="mt-3 text-center">
                <a href="index.php?page=register">Don't have an account? Register</a>
              </div>
              
              <?php if( $googleEnabled || $auth0Enabled || $uniqueUrlEnabled ): ?>
                <hr class="my-4">
                <div class="text-center mb-3">
                  <p>Or login with:</p>
                </div>
              <?php endif; ?>
            <?php endif; ?>
            
            <div class="d-grid gap-2">
              <?php if( $googleEnabled ): ?>
                <button id="google-login" class="btn btn-outline-danger">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-google me-2" viewBox="0 0 16 16">
                    <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z"/>
                  </svg>
                  Login with Google
                </button>
              <?php endif; ?>
              
              <?php if( $auth0Enabled ): ?>
                <button id="auth0-login" class="btn btn-outline-primary">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-lock me-2" viewBox="0 0 16 16">
                    <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                    <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415z"/>
                  </svg>
                  Login with Auth0
                </button>
              <?php endif; ?>
              
              <?php if( $uniqueUrlEnabled ): ?>
                <button id="unique-url-login" class="btn btn-outline-success">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link-45deg me-2" viewBox="0 0 16 16">
                    <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1.002 1.002 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4.018 4.018 0 0 1-.128-1.287z"/>
                    <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243L6.586 4.672z"/>
                  </svg>
                  Login with Unique URL
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Email login form
      const loginForm = document.getElementById('login-form');
      
      if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          
          const email = document.getElementById('email').value;
          const password = document.getElementById('password').value;
          
          // Send login request
          const response = await Ajax.post('login', {
            email: email,
            password: password
          });
          
          if (response && response.success) {
            // Redirect to home page
            window.location.href = 'index.php?page=home';
          }
        });
      }
      
      // Google login
      const googleLoginBtn = document.getElementById('google-login');
      
      if (googleLoginBtn) {
        googleLoginBtn.addEventListener('click', () => {
          // Redirect to Google login
          window.location.href = 'index.php?page=login&provider=google';
        });
      }
      
      // Auth0 login
      const auth0LoginBtn = document.getElementById('auth0-login');
      
      if (auth0LoginBtn) {
        auth0LoginBtn.addEventListener('click', () => {
          // Redirect to Auth0 login
          window.location.href = 'index.php?page=login&provider=auth0';
        });
      }
      
      // Unique URL login
      const uniqueUrlLoginBtn = document.getElementById('unique-url-login');
      
      if (uniqueUrlLoginBtn) {
        uniqueUrlLoginBtn.addEventListener('click', async () => {
          // Show modal for email input
          const email = prompt('Enter your email (optional):');
          
          // Send request to generate unique URL
          const response = await Ajax.post('unique-url', {
            email: email || null
          });
          
          if (response && response.success && response.url) {
            // Show URL to user
            alert('Your unique login URL is:\n\n' + response.url + '\n\nSave this URL to login in the future.');
            
            // Copy to clipboard
            navigator.clipboard.writeText(response.url)
              .then(() => {
                alert('URL copied to clipboard!');
              })
              .catch(() => {
                // Clipboard copy failed, already showed the URL
              });
          }
        });
      }
    });
  </script>
</body>
</html>
