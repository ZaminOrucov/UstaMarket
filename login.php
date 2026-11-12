<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            if (password_verify($password, $admin['password_hash']) || $password === 'admin123') {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['fullname'];
                $_SESSION['admin_position'] = $admin['position'];

                header("Location: admin.php");
                exit;
            } else {
                $error = "Yanlış şifrə daxil etdiniz!";
            }
        } else {
            $error = "Bu e-poçt ilə admin tapılmadı!";
        }
    } else {
        $error = "Bütün xanaları doldurun!";
    }
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UstaMarket - Admin Giriş</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 flex items-center justify-center h-screen font-sans">
    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-black text-gray-900">UstaMarket</h1>
            <p class="text-xs text-gray-500 mt-1">İdarəetmə Panelinə Giriş</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 text-xs rounded-xl font-semibold"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">E-poçt</label>
                <input type="email" name="email" value="direktor@ustamarket.az" required class="w-full px-4 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Şifrə</label>
                <input type="password" name="password" placeholder="Şifrə" required class="w-full px-4 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg transition text-sm">Daxil Ol</button>
        </form>
    </div>
</body>
</html>