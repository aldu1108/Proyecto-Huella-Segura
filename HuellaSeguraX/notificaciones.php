<?php
// api/notificaciones.php - API para gestionar notificaciones
header('Content-Type: application/json');
include_once('config/conexion.php');
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] === 'demo') {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$accion = $_GET['accion'] ?? '';

try {
    switch ($accion) {
        case 'obtener':
            // Obtener notificaciones del usuario
            $consulta = "SELECT n.*, 
                        u.nombre_usuario as usuario_origen_nombre,
                        u.foto_usuario as usuario_origen_foto
                        FROM notificaciones n
                        LEFT JOIN usuarios u ON n.id_usuario_origen = u.id_usuario
                        WHERE n.id_usuario_destino = ?
                        ORDER BY n.fecha_creacion DESC
                        LIMIT 20";

            $stmt = $conexion->prepare($consulta);
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $resultado = $stmt->get_result();

            $notificaciones = [];
            while ($row = $resultado->fetch_assoc()) {
                $row['tiempo_transcurrido'] = calcularTiempoTranscurrido($row['fecha_creacion']);
                $notificaciones[] = $row;
            }

            echo json_encode([
                'success' => true,
                'notificaciones' => $notificaciones
            ]);
            break;

        case 'marcar_leida':
            // Marcar una notificación como leída
            $id_notificacion = (int) ($_POST['id_notificacion'] ?? 0);

            if ($id_notificacion > 0) {
                $stmt = $conexion->prepare("UPDATE notificaciones SET leida = 1 
                                           WHERE id_notificacion = ? AND id_usuario_destino = ?");
                $stmt->bind_param("ii", $id_notificacion, $usuario_id);
                $stmt->execute();

                echo json_encode(['success' => true]);
            } else {
                throw new Exception('ID de notificación inválido');
            }
            break;

        case 'marcar_todas_leidas':
            // Marcar todas como leídas
            $stmt = $conexion->prepare("UPDATE notificaciones SET leida = 1 
                                       WHERE id_usuario_destino = ? AND leida = 0");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();

            echo json_encode(['success' => true]);
            break;

        case 'contador':
            // Obtener solo el contador de no leídas
            $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM notificaciones 
                                       WHERE id_usuario_destino = ? AND leida = 0");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $total = $resultado->fetch_assoc()['total'];

            echo json_encode([
                'success' => true,
                'contador' => $total
            ]);
            break;

        default:
            throw new Exception('Acción no válida');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function calcularTiempoTranscurrido($fecha)
{
    $ahora = new DateTime();
    $fecha_notif = new DateTime($fecha);
    $diferencia = $ahora->diff($fecha_notif);

    if ($diferencia->y > 0) {
        return 'hace ' . $diferencia->y . ' año' . ($diferencia->y > 1 ? 's' : '');
    } elseif ($diferencia->m > 0) {
        return 'hace ' . $diferencia->m . ' mes' . ($diferencia->m > 1 ? 'es' : '');
    } elseif ($diferencia->d > 0) {
        return 'hace ' . $diferencia->d . ' día' . ($diferencia->d > 1 ? 's' : '');
    } elseif ($diferencia->h > 0) {
        return 'hace ' . $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '');
    } elseif ($diferencia->i > 0) {
        return 'hace ' . $diferencia->i . ' minuto' . ($diferencia->i > 1 ? 's' : '');
    } else {
        return 'Ahora mismo';
    }
}

$conexion->close();
?>