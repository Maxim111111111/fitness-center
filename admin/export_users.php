<?php
session_start();
require_once('../database/config.php');

// Проверка доступа (только для администраторов и менеджеров)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header('Location: ../login.php');
    exit();
}

// Установка заголовков для скачивания CSV-файла
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.csv"');

// Создание файлового указателя для вывода
$output = fopen('php://output', 'w');

// Добавление BOM (Byte Order Mark) для корректного отображения кириллицы в Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Получение параметров фильтрации, если они переданы
$roleFilter = isset($_GET['user_role']) ? $_GET['user_role'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$searchFilter = isset($_GET['search']) ? $_GET['search'] : '';

// Построение запроса с учетом фильтров
$whereConditions = [];
$params = [];

if (!empty($roleFilter)) {
    $whereConditions[] = "role = :role";
    $params[':role'] = $roleFilter;
}

if ($statusFilter !== '') {
    $whereConditions[] = "is_active = :status";
    $params[':status'] = (int)$statusFilter;
}

if (!empty($searchFilter)) {
    $whereConditions[] = "(email LIKE :search OR first_name LIKE :search OR last_name LIKE :search OR phone LIKE :search)";
    $params[':search'] = "%{$searchFilter}%";
}

// Формирование условия WHERE
$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Заголовки CSV-файла
$headers = [
    'ID', 
    'Email', 
    'Имя', 
    'Фамилия', 
    'Телефон', 
    'Роль', 
    'Статус', 
    'Дата регистрации', 
    'Последний вход'
];
fputcsv($output, $headers);

// Получение данных пользователей
$sql = "SELECT id, email, first_name, last_name, phone, role, is_active, created_at, last_login 
        FROM users 
        {$whereClause}
        ORDER BY id";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();

// Вывод данных в CSV
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Преобразование значений для удобочитаемости
    $row['role'] = translate_role($row['role']);
    $row['is_active'] = $row['is_active'] ? 'Активен' : 'Неактивен';
    $row['created_at'] = date('d.m.Y H:i', strtotime($row['created_at']));
    $row['last_login'] = $row['last_login'] ? date('d.m.Y H:i', strtotime($row['last_login'])) : 'Никогда';
    
    fputcsv($output, $row);
}

// Закрытие файла
fclose($output);
exit();

// Функция для перевода роли на русский
function translate_role($role) {
    switch ($role) {
        case 'admin': return 'Администратор';
        case 'manager': return 'Менеджер';
        case 'user': return 'Пользователь';
        default: return $role;
    }
} 