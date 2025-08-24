<?php

/**
 * Centralized secure image upload helper.
 * Usage:
 *   $path = secure_image_upload('field_name', __DIR__.'/uploads/', 'uploads/', 'prefix_', $error);
 * Returns relative path (for DB) or null on failure (sets $error message if not already set).
 */
function secure_image_upload(string $fieldName, string $destDirFs, string $destDirDb, string $prefix, string &$error, int $maxBytes = 5242880): ?string
{
    if ($error) { // existing error
        return null;
    }
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null; // Not mandatory by default; caller decides if required
    }
    $file = $_FILES[$fieldName];

    // Basic sanity checks
    if (!is_uploaded_file($file['tmp_name'])) {
        $error = 'Upload validation failed.';
        return null;
    }
    if ($file['size'] <= 0 || $file['size'] > $maxBytes) {
        $error = 'Image exceeds size limit (max 5MB).';
        return null;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        $error = 'Invalid image type.';
        return null;
    }

    // Detect real MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $allowedMimes, true)) {
        $error = 'File is not a valid image.';
        return null;
    }

    // Validate image structure
    if (!@getimagesize($file['tmp_name'])) {
        $error = 'Corrupted or invalid image file.';
        return null;
    }

    // Quick content scan (first 16KB)
    $snippet = file_get_contents($file['tmp_name'], false, null, 0, 16384);
    $badPatterns = ['<?php', '<script', 'base64_decode', 'eval(', 'system(', 'shell_exec', 'passthru', '<?=', 'phar://'];
    foreach ($badPatterns as $p) {
        if (stripos($snippet, $p) !== false) {
            $error = 'Image contains disallowed content pattern.';
            return null;
        }
    }

    // Ensure destination directory
    if (!is_dir($destDirFs)) {
        if (!mkdir($destDirFs, 0755, true)) {
            $error = 'Failed to create uploads directory.';
            return null;
        }
    }

    $filename = $prefix . uniqid('', true) . '.' . $ext;
    $targetFs = rtrim($destDirFs, '/\\') . DIRECTORY_SEPARATOR . $filename;
    $targetDb = rtrim($destDirDb, '/\\') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetFs)) {
        $error = 'Failed to move uploaded image.';
        return null;
    }

    // Extra safety: remove executable bits & set 0644
    @chmod($targetFs, 0644);

    return $targetDb;
}
