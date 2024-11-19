<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Writer;

class ProcessController extends Controller
{
    public function index() {
        return view('convert');
    }

    public function index_compatibilities() {
        return view('compatibilities');
    }

    public function format_csv() {
        // Read the CSV file
        $csvData = Storage::disk('public')->get('original/compatibility.csv');
        $csvReader = Reader::createFromString($csvData);
        $csvReader->setHeaderOffset(0);

        // Create a new CSV writer
        $csvWriter = Writer::createFromPath(storage_path('app/public/formatted/updated_compatibility.csv'), 'w+');
        $csvWriter->insertOne($csvReader->getHeader()); // Write headers to the new CSV

        // Process each row
        foreach ($csvReader as $row) {
            // Extract the list from the "years" column to an array, escaping the line breaks
            $yearsList = explode("\n", $row['years']);
            // Format the PHP array to a JSON array with each element enclosed in double quotes, duplicates removed, and minified
            $formattedYears = json_encode(array_values(array_unique($yearsList)));
            // Update the "years" column in the row
            $row['years'] = $formattedYears;

            // Repeat the same process for other columns
            $brandsList = explode("\n", $row['brands']);
            $formattedBrands = json_encode(array_values(array_unique($brandsList)));
            $row['brands'] = $formattedBrands;

            $modelsList = explode("\n", $row['models']);
            $formattedModels = json_encode(array_values(array_unique($modelsList)));
            $row['models'] = $formattedModels;

            $motorsList = explode("\n", $row['motors']);
            $formattedMotors = json_encode(array_values(array_unique($motorsList)));
            $row['motors'] = $formattedMotors;

            // Write the updated row to the new CSV file
            $csvWriter->insertOne($row);
        }

        // Close the CSV writer
        $csvWriter->output();

        return redirect('/')->with('message', 'Procesado correctamente');
    }

    public function merge_csv() {
        // Leer el primer archivo CSV
        $csvData1 = Storage::disk('public')->get('original/export_con_id.csv');
        $csvReader1 = Reader::createFromString($csvData1);
        $csvReader1->setHeaderOffset(0);

        // Leer el segundo archivo CSV
        $csvData2 = Storage::disk('public')->get('original/compatibilidades_sin_id.csv');
        $csvReader2 = Reader::createFromString($csvData2);
        $csvReader2->setHeaderOffset(0);

        // Crear un diccionario para el primer archivo con SKU como clave y ID como valor
        $idMap = [];
        foreach ($csvReader1 as $row) {
            $idMap[$row['SKU']] = $row['ID'];
        }

        // Crear un nuevo CSV writer para el archivo combinado
        $csvWriter = Writer::createFromPath(storage_path('app/public/formatted/merged_file.csv'), 'w+');

        // Obtener el encabezado del segundo archivo y agregar la columna ID
        $header = $csvReader2->getHeader();
        $header[] = 'ID';
        $csvWriter->insertOne($header); // Escribir el nuevo encabezado al nuevo archivo CSV

        // Procesar cada fila del segundo archivo
        foreach ($csvReader2 as $row) {
            $sku = $row['SKU'];

            // Agregar la columna ID si el SKU está en el primer archivo
            if (isset($idMap[$sku])) {
                $row['ID'] = $idMap[$sku];
            } else {
                $row['ID'] = ''; // Dejar vacío si no hay coincidencia
            }

            // Escribir la fila actualizada al nuevo archivo CSV
            $csvWriter->insertOne($row);
        }

        // Cerrar el CSV writer
        $csvWriter->output();

        return redirect('/')->with('message', 'CSV files merged successfully');
    }
}
