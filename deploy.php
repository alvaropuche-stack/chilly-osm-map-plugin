<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST method is allowed']);
    exit;
}

$plugin_name = $_POST['plugin_name'] ?? '';
$new_version = $_POST['new_version'] ?? '';
$uploaded_file = $_FILES['plugin_file'] ?? null;

if (empty($plugin_name) || empty($new_version) || !$uploaded_file) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$uploads_dir = __DIR__ . '/downloads';
$plugin_file_path = $uploads_dir . '/' . basename($uploaded_file['name']);

// Move the uploaded file to the downloads directory
if (!move_uploaded_file($uploaded_file['tmp_name'], $plugin_file_path)) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    exit;
}

// Update plugins.json
$json_file_path = __DIR__ . '/plugins.json';
$json_data = file_get_contents($json_file_path);
$plugins = json_decode($json_data, true);

if (!isset($plugins['plugins'][$plugin_name])) {
    http_response_code(404); // Not Found
    echo json_encode(['success' => false, 'message' => 'Plugin not found']);
    exit;
}

$plugins['plugins'][$plugin_name]['current_version'] = $new_version;
$plugins['plugins'][$plugin_name]['download_url'] = 'https://plugins-control.chillypills.com/downloads/' . basename($uploaded_file['name']);

if (file_put_contents($json_file_path, json_encode($plugins, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Failed to update plugins.json']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Plugin deployed successfully']);
?>
