<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require_once __DIR__ . "/libreriaDb.php";
require_once '/php/vendor/autoload.php';
require_once __DIR__ . "/helpers/BookUid.php";

use App\Helpers\BookUid;

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

                $uid = BookUid::ensureExists($this, $data);
                $metadata = BookUid::getMetadata($this, $uid);

                $this->execute(
                    "INSERT INTO libros 
                    (uid, titulo, autor, editorial, genero, precio, stock, descripcion, destacado, info_adicional)
                    VALUES 
                    (:uid, :titulo, :autor, :editorial, :genero, :precio, :stock, :descripcion, :destacado, :info_adicional)",
                    [
                        "uid"         => $uid,
                        "titulo"     => $data['titulo'],
                        "autor"      => $data['autor'],
                        "editorial"  => $data['editorial'],
                        "genero"     => $data['genero'],
                        "precio"     => $data['precio'],
                        "stock"      => $data['stock'],
                        "descripcion" => $metadata['descripcion'],
                        "destacado" => $metadata['destacado'] ? 1 : 0,
                        "info_adicional" => $data['info_adicional']
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
        $info_adicional = trim($row[6] ?? '');

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
            "precio"     => (float)$precio,
            "info_adicional" => $info_adicional
        ];
    }

    /**
     * VALIDA ARCHIVO ANTES DE IMPORTAR
     */
    public function verificarArchivos(array $file): bool
    {
        if (
            !isset($file['error'], $file['size'], $file['tmp_name'], $file['name'])
            || $file['error'] !== UPLOAD_ERR_OK
            || !is_uploaded_file($file['tmp_name'])
            || (int) $file['size'] < 1
            || (int) $file['size'] > 8 * 1024 * 1024
        ) {
            throw new \InvalidArgumentException('El archivo debe tener un tamaño máximo de 8 MB.');
        }
        $actualSize = filesize($file['tmp_name']);
        if ($actualSize === false || $actualSize < 1 || $actualSize > 8 * 1024 * 1024) {
            throw new \InvalidArgumentException('El archivo debe tener un tamaño máximo de 8 MB.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            throw new \InvalidArgumentException("Extensión inválida");
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $validos = [
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
            'xls' => ['application/vnd.ms-excel', 'application/x-ole-storage'],
            'csv' => ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'],
        ];

        if (!in_array($mime, $validos[$ext], true)) {
            throw new \InvalidArgumentException("Tipo MIME inválido");
        }

        // validación real del archivo
        try {
            IOFactory::load($file['tmp_name']);
        } catch (\Throwable $error) {
            throw new \InvalidArgumentException('El archivo no contiene una planilla válida.', 0, $error);
        }

        return true;
    }
}