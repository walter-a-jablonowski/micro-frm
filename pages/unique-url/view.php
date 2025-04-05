<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= $csrfToken ?>">
  <title>Unique URL Login - MicroFrm</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="lib/micro-frm/js/errorHandler.js"></script>
  <script src="lib/micro-frm/js/ajax.js"></script>
</head>
<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow">
          <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Unique URL Login</h3>
          </div>
          <div class="card-body">
            <?php if( $token ): ?>
              <div class="alert alert-danger">
                <h4 class="alert-heading">Invalid or Expired Link</h4>
                <p>The login link you used is invalid or has expired.</p>
              </div>
              
              <div class="mt-4">
                <a href="index.php?page=login" class="btn btn-primary">Return to Login</a>
                <button id="generate-new-link" class="btn btn-outline-success ms-2">Generate New Link</button>
              </div>
            <?php else: ?>
              <div class="text-center">
                <h4>Generate a Unique Login URL</h4>
                <p>You can create a unique URL that allows you to login without a password.</p>
                
                <form id="unique-url-form" class="mt-4">
                  <div class="mb-3">
                    <label for="email" class="form-label">Email (Optional)</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email (optional)">
                    <div class="form-text">If provided, your account will be linked to this email.</div>
                  </div>
                  
                  <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Generate Login URL</button>
                  </div>
                </form>
                
                <div class="mt-3">
                  <a href="index.php?page=login">Return to Login</a>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Generate unique URL form
      const uniqueUrlForm = document.getElementById('unique-url-form');
      const generateNewLinkBtn = document.getElementById('generate-new-link');
      
      const generateUniqueUrl = async (email = null) => {
        // Send request to generate unique URL
        const response = await Ajax.post('unique-url', {
          email: email
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
            
          // Redirect to login page
          window.location.href = 'index.php?page=login';
        }
      };
      
      if (uniqueUrlForm) {
        uniqueUrlForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          
          const email = document.getElementById('email').value || null;
          await generateUniqueUrl(email);
        });
      }
      
      if (generateNewLinkBtn) {
        generateNewLinkBtn.addEventListener('click', async () => {
          const email = prompt('Enter your email (optional):');
          await generateUniqueUrl(email);
        });
      }
    });
  </script>
</body>
</html>
