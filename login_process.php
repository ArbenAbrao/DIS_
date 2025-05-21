<?php
session_start();
include 'config.php'; // Ensure you have a correct DB connection

$error = ""; // Store error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid credentials!";
            }
        } else {
            $error = "User not found!";
        }
        $stmt->close();
    }
}
?>


<!-- Include Bootstrap CSS and JS -->
<link rel="stylesheet" href="main.css"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if (!empty($error)): ?>
<!-- Error Modal -->
<div class="modal fade" id="loginErrorModal" tabindex="-1" aria-labelledby="loginErrorLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="loginErrorLabel">Login Error</h5>
      </div>
      <div class="modal-body">
        <?php echo $error; ?>
      </div>
    </div>
  </div>
</div>

<!-- Show Modal and Redirect After 2 Seconds -->
<script>
  const loginErrorModal = new bootstrap.Modal(document.getElementById('loginErrorModal'));
  loginErrorModal.show();
  setTimeout(() => {
    loginErrorModal.hide();
    window.location.href = 'login.php'; // Redirect back to login page
  }, 2000);
</script>
<?php endif; ?>
