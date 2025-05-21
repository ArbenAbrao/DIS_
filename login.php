<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance</title>

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"/>

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"/>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="main.css"/>
  <link rel="shortcut icon" href="img/logo.png" />

  <style>
/* Ensure modal stays on top */
.modal {
  z-index: 1050 !important; /* Ensure the modal has a higher z-index */
}

.modal-dialog {
  position: relative;
  z-index: 1060; /* Increase the z-index here to make sure it's above the modal backdrop */
}

/* Optional: Set the backdrop to ensure it is clickable */
.modal-backdrop {
  z-index: 1040 !important; /* Set a lower z-index for the backdrop */
}


    .password-container {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      cursor: pointer;
      color: rgb(0, 0, 0);
    }
  </style>
</head>
<body class="d-flex justify-content-center align-items-center" 
      style="height: 100vh; background-image: url('img/loginbg.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
  <div class="login-container">
    <img src="img/logo.png" alt="Logo" class="logo" />
    <h2>Login</h2>
    <form method="POST" action="login_process.php">
      <label for="username">Username:</label>
      <input type="text" name="username" class="form-control" required />

      <label for="password">Password:</label>
      <div class="password-container">
        <input type="password" name="password" id="password" class="form-control" required />
        <span class="toggle-password" onclick="togglePassword('password', 'eyeIcon')">
          <i id="eyeIcon" class="bi bi-eye"></i>
        </span>
      </div>

      <button type="submit" class="login-btn">Login</button>
    </form>
    <button type="button" class="reg-btn" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
  </div>

 <!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="registerModalLabel">Register</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="registerForm" method="POST" onsubmit="showRegisteredModal(event)">
          <label for="reg-username">Username:</label>
          <input type="text" name="username" class="form-control" required />

          <label for="reg-password">Password:</label>
          <div class="password-container">
            <input type="password" name="password" id="reg-password" class="form-control" required />
            <span class="toggle-password" onclick="togglePassword('reg-password', 'regEyeIcon')">
              <i id="regEyeIcon" class="bi bi-eye"></i>
            </span>
          </div>

          <label for="confirm-password">Confirm Password:</label>
          <div class="password-container">
            <input type="password" name="confirm_password" id="confirm-password" class="form-control" required />
            <span class="toggle-password" onclick="togglePassword('confirm-password', 'confirmEyeIcon')">
              <i id="confirmEyeIcon" class="bi bi-eye"></i>
            </span>
          </div>

          <button type="submit" class="reg-btn">Register</button>
        </form>
      </div>
    </div>
  </div>
</div>


  <!-- Registered Success Modal -->
<div class="modal fade" id="registeredModal" tabindex="-1" aria-labelledby="registeredModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="registeredModalLabel">Registration Successful</h5>
        </div>
        <div class="modal-body">
          <p>You have been successfully registered!</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Toggle Password Script -->
  <script>
    function togglePassword(inputId, iconId) {
      const passwordField = document.getElementById(inputId);
      const eyeIcon = document.getElementById(iconId);

      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.classList.remove('bi-eye');
        eyeIcon.classList.add('bi-eye-slash');
      } else {
        passwordField.type = 'password';
        eyeIcon.classList.remove('bi-eye-slash');
        eyeIcon.classList.add('bi-eye');
      }
    }

    // Submit form via AJAX and show success modal
    async function showRegisteredModal(event) {
      event.preventDefault();  // Prevent normal form submission

      const form = document.getElementById('registerForm');
      const formData = new FormData(form);

      try {
        const response = await fetch('register.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          // Show success modal after successful registration
          var successModal = new bootstrap.Modal(document.getElementById('registeredModal'), {
            keyboard: false
          });
          successModal.show();

          // Close the register modal after submission
          var registerModal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
          registerModal.hide();

          // Automatically close the success modal after 2 seconds
          setTimeout(function() {
            successModal.hide();
          }, 1000);
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred during registration. Please try again.');
      }
    }
</script>


  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
