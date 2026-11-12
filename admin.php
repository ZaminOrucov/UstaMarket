<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';

// Giriş yoxlaması
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['admin_position'] ?? 'Direktor';

// Sistemdən çıxış
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// 1. MƏHSUL ƏLAVƏ ETMƏK (Direktor və Anbardar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product']) && in_array($role, ['Direktor', 'Anbardar'])) {
    $title = $_POST['title'] ?? '';
    $category_id = $_POST['category_id'] ?? 1;
    $purchase_price = $_POST['purchase_price'] ?? 0;
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $description = $_POST['description'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO products (category_id, title, purchase_price, price, stock, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$category_id, $title, $purchase_price, $price, $stock, $description]);
    header("Location: admin.php?tab=warehouse");
    exit;
}

// 2. MƏHSULU REDAKTƏ ETMƏK / DƏYİŞDİRMƏK (Direktor və Anbardar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product']) && in_array($role, ['Direktor', 'Anbardar'])) {
    $id = $_POST['product_id'];
    $title = $_POST['title'];
    $purchase_price = $_POST['purchase_price'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $stmt = $pdo->prepare("UPDATE products SET title = ?, purchase_price = ?, price = ?, stock = ? WHERE id = ?");
    $stmt->execute([$title, $purchase_price, $price, $stock, $id]);
    header("Location: admin.php?tab=warehouse");
    exit;
}

// 3. MƏHSUL SİLMƏK (Direktor və Anbardar)
if (isset($_GET['delete_product']) && in_array($role, ['Direktor', 'Anbardar'])) {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$_GET['delete_product']]);
    header("Location: admin.php?tab=warehouse");
    exit;
}

// 4. İŞÇİ ƏLAVƏ ETMƏK (Direktor və Mühasib)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee']) && in_array($role, ['Direktor', 'Mühasib'])) {
    $stmt = $pdo->prepare("INSERT INTO employees (first_name, last_name, patronymic, fin_code, id_card_number, birth_date, position, monthly_salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['first_name'], $_POST['last_name'], $_POST['patronymic'], 
        $_POST['fin_code'], $_POST['id_card_number'], $_POST['birth_date'], 
        $_POST['position'], $_POST['monthly_salary']
    ]);
    header("Location: admin.php?tab=employees");
    exit;
}

// 5. İŞÇİ SİLMƏK (Direktor və Mühasib)
if (isset($_GET['delete_employee']) && in_array($role, ['Direktor', 'Mühasib'])) {
    $pdo->prepare("DELETE FROM employees WHERE id = ?")->execute([$_GET['delete_employee']]);
    header("Location: admin.php?tab=employees");
    exit;
}

// 6. SAYT İSTİFADƏÇİSİNİN ŞİFRƏSİNİ DƏYİŞDİRMƏK (Yalnız Direktor - Texniki dəstək)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_password']) && $role === 'Direktor') {
    $user_id = $_POST['user_id'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$new_password, $user_id]);
    header("Location: admin.php?tab=users");
    exit;
}

// 7. YENİ ADMİN VƏ YA İŞÇİ HESABI YARATMAQ (Yalnız Direktor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin']) && $role === 'Direktor') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $position = $_POST['position'];

    if (!empty($fullname) && !empty($email) && !empty($password) && !empty($position)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO admins (fullname, email, password_hash, position) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fullname, $email, $password_hash, $position]);
            header("Location: admin.php?tab=admins");
            exit;
        } catch (PDOException $e) {
            $admin_error = "Bu e-poçt artıq istifadə olunur!";
        }
    }
}

// 8. ADMİNİ SİLMƏK (Yalnız Direktor)
if (isset($_GET['delete_admin']) && $role === 'Direktor') {
    $admin_id_to_delete = $_GET['delete_admin'];
    if ($admin_id_to_delete != $_SESSION['admin_id']) {
        $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$admin_id_to_delete]);
    }
    header("Location: admin.php?tab=admins");
    exit;
}

$tab = $_GET['tab'] ?? ($role === 'Anbardar' ? 'warehouse' : 'finance');

// Məlumatların bazadan çəkilməsi
$products = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$employees = $pdo->query("SELECT * FROM employees ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Statistika
$total_stock_count = $pdo->query("SELECT SUM(stock) FROM products")->fetchColumn() ?: 0;
$total_inventory_value = $pdo->query("SELECT SUM(stock * purchase_price) FROM products")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UstaMarket - İdarəetmə Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#ecfdf5', 600: '#059669', 700: '#047857', 900: '#064e3b' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden font-sans">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between shadow-xl">
        <div>
            <div class="p-5 border-b border-slate-800 flex items-center gap-3">
                <i class="fa-solid fa-screwdriver-wrench text-amber-400 text-xl"></i>
                <div>
                    <h2 class="font-black text-base tracking-wider">USTAMARKET</h2>
                    <span class="text-[10px] bg-emerald-600 px-2 py-0.5 rounded text-white font-bold uppercase"><?= $role ?></span>
                </div>
            </div>
            <nav class="p-4 space-y-2 text-sm font-medium">
                <?php if ($role !== 'Anbardar'): ?>
                    <a href="?tab=finance" class="flex items-center gap-3 px-4 py-3 rounded-xl transition <?= $tab=='finance'?'bg-emerald-700 text-white':'text-slate-300 hover:bg-slate-800' ?>"><i class="fa-solid fa-wallet"></i> Maliyyə & Hesabatlar</a>
                <?php endif; ?>
                
                <a href="?tab=warehouse" class="flex items-center gap-3 px-4 py-3 rounded-xl transition <?= $tab=='warehouse'?'bg-emerald-700 text-white':'text-slate-300 hover:bg-slate-800' ?>"><i class="fa-solid fa-boxes-stacked"></i> Anbar & Məhsullar</a>
                
                <?php if ($role !== 'Anbardar'): ?>
                    <a href="?tab=employees" class="flex items-center gap-3 px-4 py-3 rounded-xl transition <?= $tab=='employees'?'bg-emerald-700 text-white':'text-slate-300 hover:bg-slate-800' ?>"><i class="fa-solid fa-users-gear"></i> İşçilər & Maaşlar</a>
                <?php endif; ?>

                <?php if ($role === 'Direktor'): ?>
                    <a href="?tab=users" class="flex items-center gap-3 px-4 py-3 rounded-xl transition <?= $tab=='users'?'bg-emerald-700 text-white':'text-slate-300 hover:bg-slate-800' ?>"><i class="fa-solid fa-user-shield"></i> İstifadəçi Şifrələri</a>
                    <a href="?tab=admins" class="flex items-center gap-3 px-4 py-3 rounded-xl transition <?= $tab=='admins'?'bg-emerald-700 text-white':'text-slate-300 hover:bg-slate-800' ?>"><i class="fa-solid fa-user-gear"></i> İdarəçilər (Adminlər)</a>
                <?php endif; ?>
            </nav>
        </div>
        <div class="p-4 border-t border-slate-800">
            <a href="?logout=1" class="flex items-center gap-2 text-xs text-red-400 hover:text-red-300 px-3 py-2"><i class="fa-solid fa-right-from-bracket"></i> Hesabdan Çıxış</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col overflow-y-auto">
        <header class="bg-white shadow-sm px-6 py-4 flex justify-between items-center z-10">
            <h1 class="text-xl font-extrabold text-gray-800">
                <?php 
                    if($tab=='finance') echo 'Maliyyə Hesabatları və Analitika';
                    elseif($tab=='warehouse') echo 'Anbar və Məhsul İdarəetməsi';
                    elseif($tab=='employees') echo 'İşçilər və Əməkhaqqı Reyestri';
                    elseif($tab=='users') echo 'İstifadəçi Şifrələrinin Sıfırlanması';
                    elseif($tab=='admins') echo 'Admin və İdarəçi İdarəetməsi';
                ?>
            </h1>
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-600"><?= $_SESSION['admin_name'] ?></span>
                <div class="w-10 h-10 bg-emerald-600 text-white rounded-full flex items-center justify-center font-bold">
                    <?= mb_substr($_SESSION['admin_name'], 0, 1) ?>
                </div>
            </div>
        </header>

        <div class="p-6 space-y-6 flex-1">

            <!-- 1. MALIYYƏ TABI -->
            <?php if ($tab === 'finance' && $role !== 'Anbardar'): ?>
            <div class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Ümumi Anbar Dəyəri (Alış ilə)</p>
                            <h2 class="text-2xl font-black text-emerald-700 mt-1"><?= number_format($total_inventory_value, 2) ?> ₼</h2>
                        </div>
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl"><i class="fa-solid fa-coins"></i></div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Anbarda Ümumi Mal Sayı</p>
                            <h2 class="text-2xl font-black text-gray-900 mt-1"><?= $total_stock_count ?> ədəd</h2>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl"><i class="fa-solid fa-boxes-stacked"></i></div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wide">Məhsul Qazanc və Marja Hesabatı</h3>
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs text-gray-500 uppercase border-b">
                                <th class="p-3">Məhsul</th>
                                <th class="p-3">Alış Qiyməti</th>
                                <th class="p-3">Satış Qiyməti</th>
                                <th class="p-3">Vahidə Görə Mənfəət</th>
                                <th class="p-3">Stok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($products as $p): 
                                $profit = $p['price'] - $p['purchase_price'];
                            ?>
                            <tr>
                                <td class="p-3 font-bold text-gray-900"><?= htmlspecialchars($p['title']) ?></td>
                                <td class="p-3 text-gray-600"><?= number_format($p['purchase_price'], 2) ?> ₼</td>
                                <td class="p-3 text-emerald-700 font-bold"><?= number_format($p['price'], 2) ?> ₼</td>
                                <td class="p-3 font-extrabold text-blue-600">+<?= number_format($profit, 2) ?> ₼</td>
                                <td class="p-3"><?= $p['stock'] ?> ədəd</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- 2. ANBAR TABI -->
            <?php if ($tab === 'warehouse'): ?>
            <div class="space-y-6">
                <?php if ($role !== 'Mühasib'): ?>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wide">Yeni Məhsul Əlavə Et</h3>
                    <form method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <input type="hidden" name="add_product" value="1">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Məhsul Adı</label>
                            <input type="text" name="title" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Kateqoriya</label>
                            <select name="category_id" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600 bg-white">
                                <option value="">Seçin</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Alış Qiyməti (AZN)</label>
                            <input type="number" step="0.01" name="purchase_price" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Satış Qiyməti (AZN)</label>
                            <input type="number" step="0.01" name="price" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Stok Miqdarı</label>
                            <input type="number" name="stock" value="10" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Açıqlama</label>
                            <input type="text" name="description" class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div class="col-span-full">
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm shadow transition">Məhsulu Bazaya Yaz</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 border-b font-bold text-gray-800 text-sm">Anbarda Olan Məhsullar Siyahısı (Qiymət və Stok Redaktəsi)</div>
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs text-gray-500 uppercase border-b">
                                <th class="p-3">ID</th>
                                <th class="p-3">Məhsul Adı</th>
                                <th class="p-3">Alış Qiyməti</th>
                                <th class="p-3">Satış Qiyməti</th>
                                <th class="p-3">Stok</th>
                                <?php if ($role !== 'Mühasib'): ?>
                                    <th class="p-3 text-right">Əməliyyat</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($products as $p): ?>
                            <tr>
                                <form method="POST">
                                    <input type="hidden" name="update_product" value="1">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <td class="p-3 font-semibold">#<?= $p['id'] ?></td>
                                    <td class="p-3 font-bold text-gray-900">
                                        <?php if ($role !== 'Mühasib'): ?>
                                            <input type="text" name="title" value="<?= htmlspecialchars($p['title']) ?>" class="border px-2 py-1 rounded text-sm w-full bg-gray-50">
                                        <?php else: ?>
                                            <?= htmlspecialchars($p['title']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3">
                                        <?php if ($role !== 'Mühasib'): ?>
                                            <input type="number" step="0.01" name="purchase_price" value="<?= $p['purchase_price'] ?>" class="border px-2 py-1 rounded text-sm w-24 bg-gray-50">
                                        <?php else: ?>
                                            <?= number_format($p['purchase_price'], 2) ?> ₼
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3">
                                        <?php if ($role !== 'Mühasib'): ?>
                                            <input type="number" step="0.01" name="price" value="<?= $p['price'] ?>" class="border px-2 py-1 rounded text-sm w-24 bg-gray-50 font-bold text-emerald-700">
                                        <?php else: ?>
                                            <span class="font-bold text-emerald-700"><?= number_format($p['price'], 2) ?> ₼</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3">
                                        <?php if ($role !== 'Mühasib'): ?>
                                            <input type="number" name="stock" value="<?= $p['stock'] ?>" class="border px-2 py-1 rounded text-sm w-20 bg-gray-50 font-bold">
                                        <?php else: ?>
                                            <span class="font-bold"><?= $p['stock'] ?> ədəd</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($role !== 'Mühasib'): ?>
                                        <td class="p-3 text-right flex items-center justify-end gap-2">
                                            <button type="submit" title="Yadda saxla" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded text-xs font-bold shadow"><i class="fa-solid fa-check"></i></button>
                                            <a href="?tab=warehouse&delete_product=<?= $p['id'] ?>" onclick="return confirm('Silməyə əminsiniz?')" class="text-red-500 hover:text-red-700 p-1"><i class="fa-solid fa-trash-can"></i></a>
                                        </td>
                                    <?php endif; ?>
                                </form>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- 3. İŞÇİLƏR TABI -->
            <?php if ($tab === 'employees' && $role !== 'Anbardar'): ?>
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wide">Yeni İşçi Qeydiyyatı</h3>
                    <form method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <input type="hidden" name="add_employee" value="1">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Ad</label>
                            <input type="text" name="first_name" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Soyad</label>
                            <input type="text" name="last_name" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Ata Adı</label>
                            <input type="text" name="patronymic" class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">FİN Kod</label>
                            <input type="text" name="fin_code" required maxlength="7" class="w-full px-3.5 py-2 border rounded-xl text-sm uppercase outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Vəsiqə Nömrəsi</label>
                            <input type="text" name="id_card_number" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Doğum Tarixi</label>
                            <input type="date" name="birth_date" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Vəzifəsi</label>
                            <input type="text" name="position" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Aylıq Əməkhaqqı (AZN)</label>
                            <input type="number" step="0.01" name="monthly_salary" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div class="col-span-full">
                            <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-sm shadow transition">İşçini Qeydiyyata Al</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 border-b font-bold text-gray-800 text-sm">Müəssisə İşçilərinin Siyahısı</div>
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs text-gray-500 uppercase border-b">
                                <th class="p-4">ID</th>
                                <th class="p-4">Ad Soyad Ata adı</th>
                                <th class="p-4">FİN / Vəsiqə</th>
                                <th class="p-4">Vəzifə</th>
                                <th class="p-4">Aylıq Maaş</th>
                                <th class="p-4 text-right">Əməliyyat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($employees as $e): ?>
                            <tr>
                                <td class="p-4 font-semibold">#<?= $e['id'] ?></td>
                                <td class="p-4 font-bold text-gray-900"><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name'] . ' ' . $e['patronymic']) ?></td>
                                <td class="p-4 text-xs text-gray-500"><?= $e['fin_code'] ?> / <?= $e['id_card_number'] ?></td>
                                <td class="p-4"><?= htmlspecialchars($e['position']) ?></td>
                                <td class="p-4 font-extrabold text-emerald-700"><?= number_format($e['monthly_salary'], 2) ?> ₼</td>
                                <td class="p-4 text-right">
                                    <a href="?tab=employees&delete_employee=<?= $e['id'] ?>" onclick="return confirm('Silməyə əminsiniz?')" class="text-red-500 hover:text-red-700"><i class="fa-solid fa-trash-can"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- 4. İSTİFADƏÇİ ŞİFRƏLƏRİNİN DƏYİŞDİRİLMƏSİ (Yalnız Direktor) -->
            <?php if ($tab === 'users' && $role === 'Direktor'): ?>
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 border-b font-bold text-gray-800 text-sm">Sayt İstifadəçiləri (Şifrə Sıfırlama)</div>
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs text-gray-500 uppercase border-b">
                                <th class="p-4">ID</th>
                                <th class="p-4">Ad Soyad</th>
                                <th class="p-4">E-poçt</th>
                                <th class="p-4">Telefon</th>
                                <th class="p-4">Yeni Şifrə Təyin Et</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td class="p-4 font-semibold">#<?= $u['id'] ?></td>
                                <td class="p-4 font-bold text-gray-900"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                                <td class="p-4 text-gray-600"><?= htmlspecialchars($u['email']) ?></td>
                                <td class="p-4"><?= htmlspecialchars($u['phone']) ?></td>
                                <td class="p-4">
                                    <form method="POST" class="flex items-center gap-2">
                                        <input type="hidden" name="update_user_password" value="1">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="text" name="new_password" placeholder="Yeni şifrə" required class="border px-3 py-1 rounded-lg text-xs w-36 outline-none focus:ring-1 focus:ring-emerald-600">
                                        <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow">Yenilə</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- 5. ADMİNLƏRİN İDARƏ OLUNMASI (Yalnız Direktor) -->
            <?php if ($tab === 'admins' && $role === 'Direktor'): ?>
            <div class="space-y-6">
                <?php if (isset($admin_error)): ?>
                    <div class="p-4 bg-red-100 text-red-700 rounded-xl text-sm font-semibold"><?= $admin_error ?></div>
                <?php endif; ?>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                    <h3 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wide">Yeni Admin / İdarəçi Hesabı Yarat</h3>
                    <form method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <input type="hidden" name="add_admin" value="1">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Ad Soyad</label>
                            <input type="text" name="fullname" placeholder="Məs: Elmir Məmmədov" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">E-poçt (Giriş üçün)</label>
                            <input type="email" name="email" placeholder="ornek@ustamarket.az" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Şifrə</label>
                            <input type="password" name="password" placeholder="Şifrə təyin edin" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Vəzifəsi / Rolu</label>
                            <select name="position" required class="w-full px-3.5 py-2 border rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-600 bg-white">
                                <option value="">Vəzifə seçin</option>
                                <option value="Direktor">Direktor</option>
                                <option value="Mühasib">Mühasib</option>
                                <option value="Anbardar">Anbardar</option>
                            </select>
                        </div>
                        <div class="col-span-full">
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm shadow transition">Hesab Yarat və İcazə Ver</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 border-b font-bold text-gray-800 text-sm">Sistemdə Qeydiyyatda Olan İdarəçilər</div>
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs text-gray-500 uppercase border-b">
                                <th class="p-4">ID</th>
                                <th class="p-4">Ad Soyad</th>
                                <th class="p-4">E-poçt</th>
                                <th class="p-4">Vəzifə</th>
                                <th class="p-4">Qeydiyyat Tarixi</th>
                                <th class="p-4 text-right">Əməliyyat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php 
                            $all_admins = $pdo->query("SELECT * FROM admins ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
                            foreach($all_admins as $adm): 
                            ?>
                            <tr>
                                <td class="p-4 font-semibold">#<?= $adm['id'] ?></td>
                                <td class="p-4 font-bold text-gray-900"><?= htmlspecialchars($adm['fullname']) ?></td>
                                <td class="p-4 text-gray-600"><?= htmlspecialchars($adm['email']) ?></td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold 
                                        <?= $adm['position']=='Direktor'?'bg-purple-100 text-purple-700':($adm['position']=='Mühasib'?'bg-blue-100 text-blue-700':'bg-amber-100 text-amber-700') ?>">
                                        <?= $adm['position'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-xs text-gray-500"><?= $adm['created_at'] ?></td>
                                <td class="p-4 text-right">
                                    <?php if ($adm['id'] != $_SESSION['admin_id']): ?>
                                        <a href="?tab=admins&delete_admin=<?= $adm['id'] ?>" onclick="return confirm('Bu admini silmək istədiyinizə əminsiniz?')" class="text-red-500 hover:text-red-700"><i class="fa-solid fa-trash-can"></i></a>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 font-semibold">(Sizsiniz)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </main>
</body>
</html>