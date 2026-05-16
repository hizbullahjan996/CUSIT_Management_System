<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect_to(dashboard_for_role($_SESSION['user']['role']));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $department = trim((string) ($_POST['department'] ?? ''));
    $studentId = trim((string) ($_POST['student_id'] ?? ''));
    $semester = trim((string) ($_POST['semester'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = 'Please provide a name, valid email, and a password of at least 6 characters.';
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO users (name, email, password, role, department, student_id, semester) VALUES (?, ?, ?, "student", ?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $department ?: null, $studentId ?: null, $semester ?: null]);
            flash('success', 'Student account created. Please log in.');
            redirect_to('login.php');
        } catch (PDOException $e) {
            $error = 'This email is already registered.';
        }
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
<meta charset="utf-8"><meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Register | CUSIT Portal</title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@700;800&family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
</head>
<body class="bg-[#131315] text-[#e4e2e4] min-h-screen flex items-center justify-center font-[Inter] p-6">
<form class="w-full max-w-lg bg-[#1f1f21]/80 border border-white/10 rounded-xl p-8 space-y-5" method="post">
  <?= csrf_field() ?>
  <div><p class="text-[#adc6ff] text-sm font-semibold">Student Portal</p><h1 class="font-[Outfit] text-3xl font-extrabold text-[#bec6e0]">Create Account</h1></div>
  <?php if ($error): ?><div class="rounded-lg border border-[#ffb4ab]/30 bg-[#ffb4ab]/10 text-[#ffb4ab] px-4 py-3"><?= h($error) ?></div><?php endif; ?>
  <input class="w-full bg-[#1b1b1d] border border-[#45464d] rounded-lg p-3" name="name" placeholder="Full name" required>
  <input class="w-full bg-[#1b1b1d] border border-[#45464d] rounded-lg p-3" name="email" placeholder="University email" type="email" required>
  <div class="grid sm:grid-cols-2 gap-4">
    <input class="w-full bg-[#1b1b1d] border border-[#45464d] rounded-lg p-3" name="department" placeholder="Department">
    <input class="w-full bg-[#1b1b1d] border border-[#45464d] rounded-lg p-3" name="student_id" placeholder="Student ID">
  </div>
  <input class="w-full bg-[#1b1b1d] border border-[#45464d] rounded-lg p-3" name="semester" placeholder="Semester">
  <input class="w-full bg-[#1b1b1d] border border-[#45464d] rounded-lg p-3" name="password" placeholder="Password" type="password" minlength="6" required>
  <button class="w-full py-3 rounded-lg bg-gradient-to-r from-[#0566d9] to-[#1e0052] text-white font-bold">Register</button>
  <a class="block text-center text-[#adc6ff]" href="<?= h(base_url('login.php')) ?>">Back to login</a>
</form>
</body>
</html>
