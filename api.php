<?php
header('Content-Type: application/json');
require 'db.php';

// --- PARSEO DE LA RUTA ---
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base       = '/todo_api';
$path       = substr($requestUri, strlen($base));

if (strpos($path, '/api.php') === 0) {
    $path = substr($path, strlen('/api.php'));
}

$path = rawurldecode($path);
$path = trim($path);
$path = trim($path, '/');
$segments = $path === '' ? [] : explode('/', $path);

if (!isset($segments[0]) || $segments[0] !== 'tareas') {
    http_response_code(404);
    echo json_encode(['error' => 'Recurso no encontrado']);
    exit;
}

$id     = isset($segments[1]) ? (int)$segments[1] : null;
$method = $_SERVER['REQUEST_METHOD'];

// --- GET /tareas o GET /tareas/{id} ---
if ($method === 'GET') {
    if ($id) {
        $stmt = $pdo->prepare('SELECT * FROM tareas WHERE id = ?');
        $stmt->execute([$id]);
        $tarea = $stmt->fetch();
        if ($tarea) {
            echo json_encode($tarea);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Tarea no encontrada']);
        }
    } else {
        $stmt = $pdo->query('SELECT * FROM tareas');
        echo json_encode($stmt->fetchAll());
    }
    exit;
}

// --- POST /tareas ---
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['titulo'])) {
        http_response_code(400);
        echo json_encode(['error' => 'El campo titulo es obligatorio']);
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO tareas (titulo) VALUES (?)');
    $stmt->execute([$data['titulo']]);
    http_response_code(201);
    echo json_encode(['id' => $pdo->lastInsertId()]);
    exit;
}

// --- PUT /tareas/{id} ---
if ($method === 'PUT' && $id) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['titulo'], $data['completada'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan datos para actualizar']);
        exit;
    }
    $stmt = $pdo->prepare('UPDATE tareas SET titulo = ?, completada = ? WHERE id = ?');
    $stmt->execute([$data['titulo'], $data['completada'], $id]);
    echo json_encode(['updated' => $stmt->rowCount()]);
    exit;
}

// --- DELETE /tareas/{id} ---
if ($method === 'DELETE' && $id) {
    $stmt = $pdo->prepare('DELETE FROM tareas WHERE id = ?');
    $stmt->execute([$id]);
    echo json_encode(['deleted' => $stmt->rowCount()]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
