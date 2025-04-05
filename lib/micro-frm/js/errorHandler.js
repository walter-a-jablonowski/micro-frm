/**
 * errorHandler.js
 * Handles JavaScript errors and AJAX errors
 */

class ErrorHandler {
  // Store original console methods
  static originalConsole = {
    error: console.error,
    warn: console.warn
  };
  
  // Flag to prevent recursive error reporting
  static isReporting = false;
  
  /**
   * Initialize error handler
   */
  static init() {
    // Override window.onerror
    window.onerror = (message, source, lineno, colno, error) => {
      ErrorHandler.handleError({
        message: message,
        type: error ? error.name : 'JavaScript Error',
        file: source,
        line: lineno,
        column: colno,
        stack: error ? error.stack : null
      });
      
      // Return true to prevent default browser error handling
      return true;
    };
    
    // Override unhandled promise rejection
    window.addEventListener('unhandledrejection', (event) => {
      const error = event.reason;
      
      ErrorHandler.handleError({
        message: error.message || 'Unhandled Promise Rejection',
        type: 'Promise Error',
        stack: error.stack
      });
      
      // Prevent default handling
      event.preventDefault();
    });
    
    // Override console.error
    console.error = (...args) => {
      // Call original console.error
      ErrorHandler.originalConsole.error.apply(console, args);
      
      // Handle as error
      const message = args.map(arg => 
        typeof arg === 'object' ? JSON.stringify(arg) : String(arg)
      ).join(' ');
      
      ErrorHandler.handleError({
        message: message,
        type: 'Console Error'
      });
    };
    
    // Override console.warn
    console.warn = (...args) => {
      // Call original console.warn
      ErrorHandler.originalConsole.warn.apply(console, args);
      
      // Handle as warning
      const message = args.map(arg => 
        typeof arg === 'object' ? JSON.stringify(arg) : String(arg)
      ).join(' ');
      
      ErrorHandler.handleWarning({
        message: message,
        type: 'Console Warning'
      });
    };
    
    console.log('ErrorHandler initialized');
  }
  
  /**
   * Handle an error
   * 
   * @param {object} error - Error object
   */
  static handleError(error) {
    // Display error to user
    ErrorHandler.displayError(error);
    
    // Report error to server
    ErrorHandler.reportError(error);
  }
  
  /**
   * Handle a warning
   * 
   * @param {object} warning - Warning object
   */
  static handleWarning(warning) {
    // Display warning to user
    ErrorHandler.displayWarning(warning);
    
    // Report warning to server
    ErrorHandler.reportWarning(warning);
  }
  
  /**
   * Display error to user
   * 
   * @param {object} error - Error object
   */
  static displayError(error) {
    // Create error container if it doesn't exist
    let errorContainer = document.getElementById('error-container');
    
    if (!errorContainer) {
      errorContainer = document.createElement('div');
      errorContainer.id = 'error-container';
      errorContainer.style.position = 'fixed';
      errorContainer.style.top = '0';
      errorContainer.style.left = '0';
      errorContainer.style.right = '0';
      errorContainer.style.zIndex = '9999';
      errorContainer.style.padding = '1rem';
      document.body.appendChild(errorContainer);
    }
    
    // Create error message
    const errorElement = document.createElement('div');
    errorElement.className = 'alert alert-danger alert-dismissible fade show';
    errorElement.role = 'alert';
    
    // Error title
    const errorTitle = document.createElement('h4');
    errorTitle.className = 'alert-heading';
    errorTitle.textContent = error.type || 'Error';
    errorElement.appendChild(errorTitle);
    
    // Error message
    const errorMessage = document.createElement('p');
    errorMessage.textContent = error.message || 'An unknown error occurred';
    errorElement.appendChild(errorMessage);
    
    // Error details
    if (error.file || error.line || error.stack) {
      const errorDetails = document.createElement('details');
      const errorSummary = document.createElement('summary');
      errorSummary.textContent = 'Details';
      errorDetails.appendChild(errorSummary);
      
      const errorInfo = document.createElement('pre');
      errorInfo.style.whiteSpace = 'pre-wrap';
      errorInfo.style.marginTop = '0.5rem';
      
      let detailsText = '';
      
      if (error.file) {
        detailsText += `File: ${error.file}\n`;
      }
      
      if (error.line) {
        detailsText += `Line: ${error.line}`;
        if (error.column) {
          detailsText += `, Column: ${error.column}`;
        }
        detailsText += '\n';
      }
      
      if (error.stack) {
        detailsText += `\nStack Trace:\n${error.stack}`;
      }
      
      errorInfo.textContent = detailsText;
      errorDetails.appendChild(errorInfo);
      errorElement.appendChild(errorDetails);
    }
    
    // Close button
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close';
    closeButton.setAttribute('data-bs-dismiss', 'alert');
    closeButton.setAttribute('aria-label', 'Close');
    errorElement.appendChild(closeButton);
    
    // Add to container
    errorContainer.appendChild(errorElement);
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
      errorElement.remove();
    }, 10000);
  }
  
  /**
   * Display warning to user
   * 
   * @param {object} warning - Warning object
   */
  static displayWarning(warning) {
    // Create warning container if it doesn't exist
    let warningContainer = document.getElementById('warning-container');
    
    if (!warningContainer) {
      warningContainer = document.createElement('div');
      warningContainer.id = 'warning-container';
      warningContainer.style.position = 'fixed';
      warningContainer.style.top = '0';
      warningContainer.style.left = '0';
      warningContainer.style.right = '0';
      warningContainer.style.zIndex = '9998';
      warningContainer.style.padding = '1rem';
      document.body.appendChild(warningContainer);
    }
    
    // Create warning message
    const warningElement = document.createElement('div');
    warningElement.className = 'alert alert-warning alert-dismissible fade show';
    warningElement.role = 'alert';
    
    // Warning title
    const warningTitle = document.createElement('h4');
    warningTitle.className = 'alert-heading';
    warningTitle.textContent = warning.type || 'Warning';
    warningElement.appendChild(warningTitle);
    
    // Warning message
    const warningMessage = document.createElement('p');
    warningMessage.textContent = warning.message || 'An unknown warning occurred';
    warningElement.appendChild(warningMessage);
    
    // Close button
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close';
    closeButton.setAttribute('data-bs-dismiss', 'alert');
    closeButton.setAttribute('aria-label', 'Close');
    warningElement.appendChild(closeButton);
    
    // Add to container
    warningContainer.appendChild(warningElement);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
      warningElement.remove();
    }, 5000);
  }
  
  /**
   * Report error to server
   * 
   * @param {object} error - Error object
   */
  static reportError(error) {
    // Prevent recursive error reporting
    if (ErrorHandler.isReporting) {
      return;
    }
    
    ErrorHandler.isReporting = true;
    
    // Send error to server
    try {
      fetch('/ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          identifier: 'error.report',
          error: {
            message: error.message,
            type: error.type,
            file: error.file,
            line: error.line,
            column: error.column,
            stack: error.stack,
            url: window.location.href,
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString()
          }
        })
      }).catch(() => {
        // Ignore fetch errors to prevent recursive reporting
      }).finally(() => {
        ErrorHandler.isReporting = false;
      });
    } catch (e) {
      // Ignore any errors in the error reporter
      ErrorHandler.isReporting = false;
    }
  }
  
  /**
   * Report warning to server
   * 
   * @param {object} warning - Warning object
   */
  static reportWarning(warning) {
    // Prevent recursive error reporting
    if (ErrorHandler.isReporting) {
      return;
    }
    
    ErrorHandler.isReporting = true;
    
    // Send warning to server
    try {
      fetch('/ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          identifier: 'error.report',
          warning: {
            message: warning.message,
            type: warning.type,
            url: window.location.href,
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString()
          }
        })
      }).catch(() => {
        // Ignore fetch errors to prevent recursive reporting
      }).finally(() => {
        ErrorHandler.isReporting = false;
      });
    } catch (e) {
      // Ignore any errors in the error reporter
      ErrorHandler.isReporting = false;
    }
  }
  
  /**
   * Replace the entire page with an error page
   * 
   * @param {object} error - Error object
   */
  static replacePage(error) {
    const html = `
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error: ${error.type || 'JavaScript Error'}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      padding: 2rem;
      background-color: #f8f9fa;
    }
    .error-container {
      max-width: 800px;
      margin: 0 auto;
      background-color: #fff;
      border-radius: 5px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      padding: 2rem;
    }
    .error-title {
      color: #dc3545;
      margin-bottom: 1rem;
    }
    .error-message {
      font-size: 1.2rem;
      margin-bottom: 1.5rem;
    }
    .error-details {
      background-color: #f8f9fa;
      padding: 1rem;
      border-radius: 5px;
      font-family: monospace;
    }
  </style>
</head>
<body>
  <div class="error-container">
    <h1 class="error-title">${error.type || 'JavaScript Error'}</h1>
    <div class="error-message">${error.message || 'An unknown error occurred'}</div>
    <div class="error-details">
      ${error.file ? `<p>File: ${error.file}</p>` : ''}
      ${error.line ? `<p>Line: ${error.line}${error.column ? `, Column: ${error.column}` : ''}</p>` : ''}
      ${error.stack ? `<h3>Stack Trace</h3><pre>${error.stack}</pre>` : ''}
      <p>URL: ${window.location.href}</p>
      <p>Time: ${new Date().toLocaleString()}</p>
    </div>
    <div class="mt-4">
      <button class="btn btn-primary" onclick="window.location.reload()">Reload Page</button>
      <button class="btn btn-secondary" onclick="window.history.back()">Go Back</button>
    </div>
  </div>
</body>
</html>
    `;
    
    document.open();
    document.write(html);
    document.close();
  }
}

// Initialize error handler
document.addEventListener('DOMContentLoaded', () => {
  ErrorHandler.init();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { ErrorHandler };
}
