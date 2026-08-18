<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'profac_app');
if ($conn->connect_error) {
    die('ERROR: ' . $conn->connect_error);
}

$sql = file_get_contents('SCRIPT_COMPLETO_PROYECCION_MAYO_2026.sql');

// Ejecuta todas las queries juntas
if ($conn->multi_query($sql)) {
    $queryNum = 1;
    do {
        if ($result = $conn->store_result()) {
            $rows = $result->num_rows;
            echo "RESULTADO $queryNum: $rows filas\n";
            
            // Si hay datos, muestra primeras 5 filas
            if ($rows > 0) {
                $fields = $result->fetch_fields();
                echo "COLUMNAS: " . implode(', ', array_map(fn($f) => $f->name, $fields)) . "\n";
                
                $count = 0;
                while ($row = $result->fetch_assoc()) {
                    if ($count++ < 3) {
                        echo json_encode($row) . "\n";
                    }
                }
                if ($rows > 3) echo "... y " . ($rows - 3) . " más\n";
            }
            echo "\n";
            $result->free();
        }
        $queryNum++;
    } while ($conn->next_result());
    echo "✓ SCRIPT COMPLETADO EXITOSAMENTE\n";
} else {
    echo "ERROR: " . $conn->error . "\n";
}

$conn->close();
?>
