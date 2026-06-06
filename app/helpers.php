<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/backup.php';

session_start();
check_scheduled_backup();

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Token CSRF invalido.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        redirect('index.php');
    }
}

function require_admin(): void
{
    require_login();
    if (strtolower(current_user()['rol']) !== 'administrador') {
        http_response_code(403);
        exit('Acceso denegado.');
    }
}

function is_admin(): bool
{
    return current_user() && strtolower(current_user()['rol']) === 'administrador';
}

function sanitize_name(string $value): string
{
    return preg_replace('/[^A-Za-zÁÉÍÓÚáéíóúÑñÜü ]/u', '', trim($value)) ?? '';
}

function safe_folder(string $value): string
{
    $value = preg_replace('/[^A-Za-z0-9_\- ]/u', '', $value) ?? 'archivo';
    return str_replace(' ', '_', trim($value));
}

function document_uploaded(?string $path): bool
{
    return $path !== null && $path !== '' && is_file(BASE_PATH . DIRECTORY_SEPARATOR . $path);
}

function upload_pdf(string $field, string $teacher, string $grade, string $student, string $type, ?string $previous = ''): string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return (string)$previous;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo cargar el documento ' . $type . '.');
    }

    $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($_FILES[$field]['tmp_name']);
    if ($extension !== 'pdf' || $mime !== 'application/pdf') {
        throw new RuntimeException('Solo se permiten archivos PDF.');
    }

    $folder = UPLOAD_PATH . DIRECTORY_SEPARATOR . safe_folder($teacher . '_' . $grade) . DIRECTORY_SEPARATOR . safe_folder($student);
    if (!is_dir($folder)) {
        mkdir($folder, 0775, true);
    }

    $filename = strtoupper($type) . '.pdf';
    $target = $folder . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
        throw new RuntimeException('No se pudo guardar el documento ' . $type . '.');
    }

    return 'uploads/documents/' . safe_folder($teacher . '_' . $grade) . '/' . safe_folder($student) . '/' . $filename;
}

function report_filter_label(string $filter): string
{
    return match ($filter) {
        'faltantes' => 'Por documentos faltantes',
        'entregados' => 'Por documentos entregados',
        default => 'Reporte general',
    };
}

function student_is_complete(array $student): bool
{
    return document_uploaded($student['acta'] ?? '') &&
        document_uploaded($student['certificado'] ?? '') &&
        document_uploaded($student['comp_domicilio'] ?? '') &&
        document_uploaded($student['curp'] ?? '');
}

