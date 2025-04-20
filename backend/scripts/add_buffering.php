<?php
/**
 * Скрипт для автоматического добавления буферизации вывода во все API файлы
 */
$api_directory = __DIR__ . '/../api/';
$backup_directory = __DIR__ . '/../backups/' . date('Y-m-d_H-i-s') . '/';
if (!file_exists($backup_directory)) {
    mkdir($backup_directory, 0755, true);
}
$api_files = glob($api_directory . '*.php');
$modified_count = 0;
foreach ($api_files as $file_path) {
    $filename = basename($file_path);
    
    if (preg_match('/_fixed\.php$|_bootstrap\.php$|_buffered\.php$|_debug\.php$/', $filename)) {
        echo "Пропускаем файл (уже модифицирован): $filename\n";
        continue;
    }
    
    $content = file_get_contents($file_path);
    
    file_put_contents($backup_directory . $filename, $content);
    
    if (strpos($content, 'ob_start()') !== false) {
        echo "Пропускаем файл (уже содержит буферизацию): $filename\n";
        continue;
    }
    
    $modified_content = preg_replace(
        '/^(<\?php)/i',
        "<?php\n// Включаем буферизацию вывода в начале файла\nob_start();\n",
        $content
    );
    
    $modified_content = preg_replace(
        '/\bexit;/i',
        "ob_end_flush();",
        $modified_content
    );
    
    if (strpos($modified_content, 'ob_end_flush()') === false) {
        $modified_content = preg_replace(
            '/\bexit;/i',
            "ob_end_flush(); // Сбрасываем буфер перед выходом\nexit;",
            $modified_content
        );
        
        if ($modified_content === $content) {
            $modified_content .= "\n\n// Сбрасываем буфер в конце файла\nob_end_flush();\n";
        }
    }
    
    file_put_contents($file_path, $modified_content);
    $modified_count++;
    
    echo "Модифицирован файл: $filename\n";
}
echo "\nВсего модифицировано файлов: $modified_count\n";
echo "Резервные копии сохранены в $backup_directory\n";
$readme_content = "# Резервная копия API файлов\n\n";
$readme_content .= "Копии созданы: " . date('Y-m-d H:i:s') . "\n\n";
$readme_content .= "## Как восстановить файлы\n\n";
$readme_content .= "Для восстановления оригинальных файлов, скопируйте их содержимое обратно в директорию `/api/`\n";
file_put_contents($backup_directory . 'README.md', $readme_content);
echo "\nГотово!\n"; 