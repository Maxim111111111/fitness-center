<?php
/**
 * Функции для работы с настройками сайта
 */

/**
 * Получает значение настройки по ключу
 * 
 * @param string $key Ключ настройки
 * @param mixed $default Значение по умолчанию, если настройка не найдена
 * @return mixed Значение настройки или значение по умолчанию
 */
function get_setting($key, $default = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        error_log("Error getting setting {$key}: " . $e->getMessage());
        return $default;
    }
}

/**
 * Обновляет или создает настройку
 * 
 * @param string $key Ключ настройки
 * @param mixed $value Значение настройки
 * @return bool Успешно ли обновлена настройка
 */
function update_setting($key, $value) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value, updated_at) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value), 
            updated_at = VALUES(updated_at)
        ");
        return $stmt->execute([$key, $value]);
    } catch (PDOException $e) {
        error_log("Error updating setting {$key}: " . $e->getMessage());
        return false;
    }
}

/**
 * Получает все настройки в виде ассоциативного массива
 * 
 * @return array Ассоциативный массив всех настроек
 */
function get_all_settings() {
    global $pdo;
    
    $settings = [];
    
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (PDOException $e) {
        error_log("Error getting all settings: " . $e->getMessage());
    }
    
    return $settings;
}

/**
 * Удаляет настройку по ключу
 * 
 * @param string $key Ключ настройки для удаления
 * @return bool Успешно ли удалена настройка
 */
function delete_setting($key) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM settings WHERE setting_key = ?");
        return $stmt->execute([$key]);
    } catch (PDOException $e) {
        error_log("Error deleting setting {$key}: " . $e->getMessage());
        return false;
    }
}

/**
 * Проверяет, находится ли сайт в режиме обслуживания
 * 
 * @return bool Находится ли сайт в режиме обслуживания
 */
function is_maintenance_mode() {
    return get_setting('maintenance_mode', '0') == '1';
}

/**
 * Проверяет, включено ли онлайн-бронирование
 * 
 * @return bool Включено ли онлайн-бронирование
 */
function is_online_booking_enabled() {
    return get_setting('enable_online_booking', '1') == '1';
}

/**
 * Получает данные для подвала сайта (контакты, соцсети)
 * 
 * @return array Массив с данными для подвала
 */
function get_footer_data() {
    return [
        'site_name' => get_setting('site_name', 'Moreon Fitness'),
        'contact_email' => get_setting('contact_email', ''),
        'contact_phone' => get_setting('contact_phone', ''),
        'address' => get_setting('address', ''),
        'working_hours' => get_setting('working_hours', ''),
        'facebook_url' => get_setting('facebook_url', ''),
        'instagram_url' => get_setting('instagram_url', ''),
        'vk_url' => get_setting('vk_url', ''),
        'youtube_url' => get_setting('youtube_url', '')
    ];
} 