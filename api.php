<?php
// CORS və JSON başlıqları
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config/db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        registerUser($pdo);
        break;
    case 'login':
        loginUser($pdo);
        break;
    case 'get_products':
        getProducts($pdo);
        break;
    case 'get_product_detail':
        getProductDetail($pdo);
        break;
    case 'get_categories':
        getCategories($pdo);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Naməlum əməliyyat (action) təyin olundu']);
        break;
}

// ---------------------------------------------------------
// Köməkçi Funksiya: Həm JSON, həm də POST məlumatlarını oxumaq üçün
// ---------------------------------------------------------
function getRequestData() {
    $content = trim(file_get_contents("php://input"));
    $data = json_decode($content, true);
    
    // Əgər JSON gəlməyibsə, standart $_POST massivini yoxlayırıq
    if (empty($data)) {
        $data = $_POST;
    }
    return $data;
}

// ---------------------------------------------------------
// FUNKSİYALAR
// ---------------------------------------------------------

// 1. Qeydiyyat
function registerUser($pdo) {
    $data = getRequestData();

    if (empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name'])) {
        echo json_encode(['status' => 'error', 'message' => 'Zəruri xanaları doldurun (email, password, first_name, last_name)']);
        return;
    }

    // E-poçtun bazada olub-olmamasını yoxlamaq
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Bu e-poçt ünvanı artıq istifadədədir']);
        return;
    }

    // Şifrəni şifrələmək (Bcrypt)
    $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (first_name, last_name, patronymic, country_code, phone, email, birth_date, password_hash, profile_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['patronymic'] ?? null,
            $data['country_code'] ?? '+994',
            $data['phone'] ?? '',
            $data['email'],
            $data['birth_date'] ?? null,
            $password_hash,
            $data['profile_image'] ?? null
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Qeydiyyat uğurla tamamlandı']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Qeydiyyat zamanı xəta: ' . $e->getMessage()]);
    }
}

// 2. Giriş (Login)
function loginUser($pdo) {
    $data = getRequestData();

    if (empty($data['email']) || empty($data['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'E-poçt və şifrə daxil edilməlidir']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch();

    // İstifadəçi mövcuddursa və şifrə düzgündürsə
    if ($user && password_verify($data['password'], $user['password_hash'])) {
        unset($user['password_hash']); // Təhlükəsizlik üçün şifrəni cavabdan çıxarırıq
        echo json_encode(['status' => 'success', 'message' => 'Giriş uğurludur', 'user' => $user]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'E-poçt və ya şifrə yanlışdır']);
    }
}

// 3. Məhsulları Gətir
function getProducts($pdo) {
    $search = $_GET['search'] ?? '';
    $category_id = $_GET['category_id'] ?? null;

    $sql = "SELECT p.*, c.name as category_name, 
            (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id LIMIT 1) as primary_image 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND p.title LIKE ?";
        $params[] = "%$search%";
    }

    if (!empty($category_id)) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }

    $sql .= " ORDER BY p.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $products]);
}

// 4. Məhsul Detalları
function getProductDetail($pdo) {
    $id = $_GET['id'] ?? 0;
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Məhsul ID qeyd olunmayıb']);
        return;
    }

    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['status' => 'error', 'message' => 'Məhsul tapılmadı']);
        return;
    }

    $stmtImg = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
    $stmtImg->execute([$id]);
    $product['images'] = $stmtImg->fetchAll(PDO::FETCH_COLUMN);

    $stmtRev = $pdo->prepare("SELECT r.*, u.first_name, u.last_name, u.profile_image FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.id DESC");
    $stmtRev->execute([$id]);
    $product['reviews'] = $stmtRev->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $product]);
}

// 5. Kateqoriyaları Gətir
function getCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $categories]);
}