<?php
session_start();
include 'db.php'; 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userOrEmail = trim($_POST['user_or_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);

    if (empty($userOrEmail) || empty($password)) {
        
        header("Location: login.php?error=" . urlencode("Invalid username/email or password."));
        exit;
    }

    $tables = [
        'users' => 'user_dashboard.php',
        'admins' => 'admin.php',
        'agents' => 'agent_dashboard.php' // Changed redirect to the new dashboard
    ];

    $foundUser = null;
    $userRole = null;

    foreach ($tables as $table => $dashboard) {
        if (filter_var($userOrEmail, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("SELECT * FROM `$table` WHERE email = ?");
        } else {
            $stmt = $conn->prepare("SELECT * FROM `$table` WHERE name = ?");
        }
        $stmt->bind_param("s", $userOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            $passwordMatch = ($table === 'admins')
                ? $password === $user['password']
                : password_verify($password, $user['password']);

            if ($passwordMatch) {
                $foundUser = $user;
                $userRole = $table;
                break;
            }
        }
    }

    if ($foundUser) {
        $_SESSION['user_id'] = $foundUser['id'];
        $_SESSION['username'] = $foundUser['username'] ?? $foundUser['name'];
        $_SESSION['role'] = $userRole;

        if ($rememberMe) {
            setcookie("remember_user", $userOrEmail, time() + (30 * 24 * 60 * 60), "/"); 
        } else {
            setcookie("remember_user", "", time() - 3600, "/"); 
        }

        header("Location: " . $tables[$userRole]);
        exit;
    } else {
        header("Location: login.php?error=" . urlencode("Invalid username/email or password."));
        exit;
    }
}


$remembered = $_COOKIE['remember_user'] ?? '';
$errorMsg = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body
  class="min-h-screen bg-cover bg-center flex items-center justify-center relative"
  style="background-image: url('your-landing-background.jpg');"
>
  <div class="absolute inset-0 bg-black bg-opacity-60"></div>

  <div
    class="relative z-10 w-full max-w-md p-8 bg-white/80 rounded-xl shadow-lg backdrop-blur-md"
  >
    <h2
      class="text-3xl font-extrabold text-center text-red-600 mb-6 tracking-wide"
    >
      Welcome Back
    </h2>

    <?php if ($errorMsg): ?>
      <div
        id="errorMessage"
        class="text-red-600 text-sm mb-4 text-center font-medium"
      >
        <?php echo htmlspecialchars($errorMsg); ?>
      </div>
    <?php endif; ?>

    <form id="loginForm" action="login.php" method="POST" novalidate class="space-y-5">
      <div>
        <label
          for="user_or_email"
          class="block text-sm font-semibold text-gray-700 mb-1"
          >Email or Username</label
        >
        <input
          type="text"
          id="user_or_email"
          name="user_or_email"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500"
          placeholder="someone@gmil.com or username"
          value="<?php echo htmlspecialchars($remembered); ?>"
        />
        <p id="userError" class="text-red-600 text-sm mt-1 hidden">
          Username or Email is required.
        </p>
      </div>

      <div class="relative">
        <label
          for="password"
          class="block text-sm font-semibold text-gray-700 mb-1"
          >Password</label
        >
        <input
          type="password"
          id="password"
          name="password"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 pr-12"
          placeholder="••••••••"
        />
        <button
          type="button"
          id="togglePassword"
          class="absolute right-3 top-8 text-sm text-gray-500 hover:text-red-600"
        >
        </button>
        <p id="passError" class="text-red-600 text-sm mt-1 hidden">
          Password is required.
        </p>
      </div>

      <div class="flex items-center justify-between">
        <label class="flex items-center text-sm text-gray-700">
          <input
            type="checkbox"
            id="remember"
            name="remember_me"
            class="mr-2 rounded"
            <?php if (!empty($remembered)) echo 'checked'; ?>
          />
          Remember me
        </label>
        <a href="#" class="text-sm text-blue-600 hover:underline"
          >Forgot password?</a
        >
      </div>

      <button
        type="submit"
        class="w-full bg-red-600 text-white font-semibold py-2.5 rounded-lg hover:bg-white hover:text-red-600 border border-red-600 transition-all"
      >
        Sign In
      </button>

      <div class="text-center text-sm mt-4">
        <span class="text-gray-600">Don't have an account?</span>
        <a href="registration.html" class="text-blue-600 hover:underline"
          >Sign Up</a
        >
      </div>
      <div class="text-center text-sm mt-2">
        <a href="index.html" class="text-gray-500 hover:text-red-600"
          >← Back to Home</a
        >
      </div>
    </form>
  </div>

  <script>
    const loginForm = document.getElementById("loginForm");
    const userOrEmailInput = document.getElementById("user_or_email");
    const passwordInput = document.getElementById("password");
    const userError = document.getElementById("userError");
    const passError = document.getElementById("passError");
    const togglePasswordBtn = document.getElementById("togglePassword");

    togglePasswordBtn.addEventListener("click", () => {
      const type =
        passwordInput.getAttribute("type") === "password"
          ? "text"
          : "password";
      passwordInput.setAttribute("type", type);
      togglePasswordBtn.textContent = type === "password" ? "Show" : "Hide";
    });

    loginForm.addEventListener("submit", (e) => {
      let valid = true;

      if (!userOrEmailInput.value.trim()) {
        userError.classList.remove("hidden");
        userOrEmailInput.classList.add("border-red-600");
        valid = false;
      } else {
        userError.classList.add("hidden");
        userOrEmailInput.classList.remove("border-red-600");
      }

      if (!passwordInput.value.trim()) {
        passError.classList.remove("hidden");
        passwordInput.classList.add("border-red-600");
        valid = false;
      } else {
        passError.classList.add("hidden");
        passwordInput.classList.remove("border-red-600");
      }

      if (!valid) {
        e.preventDefault();
      }
    });
  </script>
</body>
</html>