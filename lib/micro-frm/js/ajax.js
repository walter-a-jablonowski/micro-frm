/**
 * ajax.js
 * Helper functions for sending AJAX requests
 */

class Ajax {
  /**
   * Send a JSON request to the server
   * 
   * @param {string} identifier - Request identifier
   * @param {object} data - Data to send
   * @param {function} successCallback - Success callback
   * @param {function} errorCallback - Error callback
   */
  static async sendRequest(identifier, data = {}, successCallback = null, errorCallback = null) {
    try {
      // Add identifier to data
      const requestData = {
        identifier: identifier,
        ...data
      };
      
      // Add CSRF token if available
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      if (csrfToken) {
        requestData.csrf_token = csrfToken;
      }
      
      // Send request
      const response = await fetch('ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(requestData)
      });
      
      // Parse response
      const result = await response.json();
      
      if (result.success) {
        // Call success callback
        if (successCallback) {
          successCallback(result);
        }
        return result;
      } else {
        // Handle error
        const error = result.error || { message: 'Unknown error' };
        
        // Call error callback
        if (errorCallback) {
          errorCallback(error);
        } else {
          // Use default error handler
          ErrorHandler.handleError(error);
        }
        return null;
      }
    } catch (error) {
      // Handle fetch error
      if (errorCallback) {
        errorCallback({ message: error.message });
      } else {
        // Use default error handler
        ErrorHandler.handleError({ 
          message: 'Network error: ' + error.message,
          type: 'Network Error'
        });
      }
      return null;
    }
  }
  
  /**
   * Send a GET request (shorthand)
   */
  static async get(identifier, data = {}, successCallback = null, errorCallback = null) {
    data.method = 'GET';
    return Ajax.sendRequest(identifier, data, successCallback, errorCallback);
  }
  
  /**
   * Send a POST request (shorthand)
   */
  static async post(identifier, data = {}, successCallback = null, errorCallback = null) {
    data.method = 'POST';
    return Ajax.sendRequest(identifier, data, successCallback, errorCallback);
  }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { Ajax };
}
