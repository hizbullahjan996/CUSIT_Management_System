<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect_to(dashboard_for_role($_SESSION['user']['role']));
}

$error = '';
$selectedRole = (string) ($_POST['portal_role'] ?? 'student');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $selectedRole = (string) ($_POST['portal_role'] ?? 'student');

    if (!in_array($selectedRole, ['student', 'faculty', 'admin'], true)) {
        $error = 'Please choose a valid portal role.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid university email and password.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'student_id' => $user['student_id'],
                'department' => $user['department'],
                'semester' => $user['semester'],
            ];
            redirect_to(dashboard_for_role($user['role']));
        }
        if ($error === '') {
            $error = 'Invalid login credentials.';
        }
    }
}

$template = file_get_contents(template_path('cusit_portal_login/code.html'));
$errorHtml = $error !== ''
    ? '<div class="rounded-lg border border-error/30 bg-error/10 text-error px-md py-sm">' . h($error) . '</div>'
    : '';

$selectedRoleEscaped = h($selectedRole);
$selectorScript = <<<HTML
<script>
document.addEventListener('DOMContentLoaded', function () {
  var roleInput = document.getElementById('portal_role');
  var buttons = document.querySelectorAll('[data-portal-role]');
  var passwordInput = document.getElementById('password');
  var passwordToggle = passwordInput ? passwordInput.parentElement.querySelector('.material-symbols-outlined.cursor-pointer') : null;
  var activeClasses = ['bg-secondary-container', 'text-on-secondary-container', 'shadow-sm'];
  var inactiveClasses = ['text-on-surface-variant', 'hover:text-on-surface'];

  function applyRole(role) {
    roleInput.value = role;
    buttons.forEach(function (button) {
      var isActive = button.getAttribute('data-portal-role') === role;
      activeClasses.forEach(function (className) {
        button.classList.toggle(className, isActive);
      });
      inactiveClasses.forEach(function (className) {
        button.classList.toggle(className, !isActive);
      });
    });
  }

  buttons.forEach(function (button) {
    button.setAttribute('type', 'button');
    button.addEventListener('click', function () {
      applyRole(button.getAttribute('data-portal-role'));
    });
  });

  if (passwordInput && passwordToggle) {
    passwordToggle.addEventListener('click', function () {
      var isHidden = passwordInput.getAttribute('type') === 'password';
      passwordInput.setAttribute('type', isHidden ? 'text' : 'password');
      passwordToggle.textContent = isHidden ? 'visibility_off' : 'visibility';
    });
  }

  applyRole(roleInput.value || 'student');
});
</script>
</body>
HTML;

$template = strtr($template, [
    '<form class="space-y-lg">' => '<form class="space-y-lg" method="post" action="' . h(base_url('login.php')) . '">' . csrf_field() . $errorHtml,
    '<div class="flex p-xs bg-surface-container-highest rounded-lg mb-xl">' => '<div class="flex p-xs bg-surface-container-highest rounded-lg mb-xl"><input type="hidden" id="portal_role" name="portal_role" value="' . $selectedRoleEscaped . '">',
    '<button class="flex-1 py-sm px-md rounded-md font-label-md text-label-md bg-secondary-container text-on-secondary-container shadow-sm transition-all">Student</button>' => '<button class="flex-1 py-sm px-md rounded-md font-label-md text-label-md bg-secondary-container text-on-secondary-container shadow-sm transition-all" data-portal-role="student">Student</button>',
    '<button class="flex-1 py-sm px-md rounded-md font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-all">Faculty</button>' => '<button class="flex-1 py-sm px-md rounded-md font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-all" data-portal-role="faculty">Faculty</button>',
    '<button class="flex-1 py-sm px-md rounded-md font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-all">Admin</button>' => '<button class="flex-1 py-sm px-md rounded-md font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-all" data-portal-role="admin">Admin</button>',
    '<input class="w-full pl-xl pr-md py-md bg-surface-container-low border border-outline-variant rounded-lg text-on-surface focus:outline-none focus:border-secondary transition-all placeholder-transparent" id="email" placeholder="Email" type="email"/>' => '<input class="w-full pl-14 pr-md py-md bg-surface-container-low border border-outline-variant rounded-lg text-on-surface focus:outline-none focus:border-secondary transition-all placeholder-transparent" id="email" name="email" placeholder="Email" type="email" value="' . h($_POST['email'] ?? '') . '" required/>',
    '<input class="w-full pl-xl pr-xl py-md bg-surface-container-low border border-outline-variant rounded-lg text-on-surface focus:outline-none focus:border-secondary transition-all placeholder-transparent" id="password" placeholder="Password" type="password"/>' => '<input class="w-full pl-14 pr-xl py-md bg-surface-container-low border border-outline-variant rounded-lg text-on-surface focus:outline-none focus:border-secondary transition-all placeholder-transparent" id="password" name="password" placeholder="Password" type="password" required/>',
    '<a class="text-secondary-fixed font-bold hover:underline" href="#">Register Smart Identity</a>' => '<a class="text-secondary-fixed font-bold hover:underline" href="' . h(base_url('register.php')) . '">Register Smart Identity</a>',
    '</body>' => $selectorScript,
]);

echo $template;
