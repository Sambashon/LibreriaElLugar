<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require_once __DIR__ . "/libreriaDb.php";
require_once '/php/vendor/autoload.php';
class Importador extends LibreriaDB
{
    /**
     * IMPORTA LIBROS DESDE EXCEL
     * - Inserta filas válidas
     * - Ignora filas inválidas
     * - Devuelve reporte
     */
    public function importarLibros(string $archivo): array
    {
            
        $spreadsheet = IOFactory::load($archivo);
        $rows = $spreadsheet->getActiveSheet()->toArray();
        
        $insertados = 0;
        $errores = [];

        $this->beginTransaction();

        try {
            foreach ($rows as $i => $row) {

                // saltar encabezados
                if ($i === 0) continue;

                $data = $this->validarFila($row);

                if ($data === false) {
                    $errores[] = "Fila $i inválida (datos faltantes o incorrectos)";
                    continue;
                }

                $this->execute(
                    "INSERT INTO libros 
                    (titulo, autor, editorial, genero, precio, stock)
                    VALUES 
                    (:titulo, :autor, :editorial, :genero, :precio, :stock)",
                    [
                        "titulo"     => $data['titulo'],
                        "autor"      => $data['autor'],
                        "editorial"  => $data['editorial'],
                        "genero"     => $data['genero'],
                        "precio"     => $data['precio'],
                        "stock"      => $data['stock']
                    ]
                );

                $insertados++;
            }

            $this->commit();

        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }

        return [
            "insertados" => $insertados,
            "errores" => $errores
        ];
    }

    /**
     * VALIDA UNA FILA DEL EXCEL
     * Devuelve array limpio o false si es inválido
     */
    private function validarFila(array $row): array|false
    {
        $titulo    = trim($row[0] ?? '');
        $autor     = trim($row[1] ?? '');
        $genero    = trim($row[2] ?? '');
        $stock     = $row[3] ?? null;
        $editorial = trim($row[4] ?? '');
        $precio = trim($row[5] ?? '');
        $precio = str_replace(['$', ' '], '', $precio);  // sacar $ y espacios
        $precio = str_replace('.', '', $precio);          // sacar puntos de miles
        $precio = str_replace(',', '.', $precio);         // coma decimal → punto

        // validaciones básicas obligatorias
        if ($titulo === '' || $autor === '') {
            return false;
        }

        if (!is_numeric($stock) || $stock < 0) {
            return false;
        }

        if (!is_numeric($precio) || $precio < 0) {
            return false;
        }

        return [
            "titulo"     => $titulo,
            "autor"      => $autor,
            "genero"     => $genero,
            "stock"      => (int)$stock,
            "editorial"  => $editorial,
            "precio"     => (float)$precio
        ];
    }

    /**
     * VALIDA ARCHIVO ANTES DE IMPORTAR
     */
    public function verificarArchivos(array $file): bool
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            throw new RuntimeException("Extensión inválida");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $validos = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv'
        ];

        if (!in_array($mime, $validos)) {
            throw new RuntimeException("Tipo MIME inválido");
        }

        // validación real del archivo
        IOFactory::load($file['tmp_name']);

        return true;
    }
}