<?php
// Script to generate basic .po files for the 40-horas-oracion plugin
$languages = [
    'de_DE' => ['lang' => 'de', 'name' => 'Deutsch'],
    'en_US' => ['lang' => 'en', 'name' => 'English'],
    'es_ES' => ['lang' => 'es', 'name' => 'Español'],
    'fr_FR' => ['lang' => 'fr', 'name' => 'Français'],
    'it_IT' => ['lang' => 'it', 'name' => 'Italiano'],
    'nl_NL' => ['lang' => 'nl', 'name' => 'Nederlands'],
    'pt_BR' => ['lang' => 'pt', 'name' => 'Português'],
    'sq'    => ['lang' => 'sq', 'name' => 'Shqip'],
    'sk_SK' => ['lang' => 'sk', 'name' => 'Slovenčina'],
    'el'    => ['lang' => 'el', 'name' => 'Ελληνικά'],
    'ru_RU' => ['lang' => 'ru', 'name' => 'Русский'],
    'uk'    => ['lang' => 'uk', 'name' => 'Українська'],
    'ar'    => ['lang' => 'ar', 'name' => 'العربية']
];

$strings = [
    "Error de seguridad. Por favor recargue la página.",
    "Ha excedido el límite de intentos. Por favor espere una hora.",
    "Error en la verificación CAPTCHA.",
    "Horario inválido.",
    "Ese horario ya está completo.",
    "Error al guardar la inscripción. Por favor intente nuevamente.",
    "Inscripción realizada correctamente.",
    "Error de seguridad.",
    "Por favor complete todos los campos requeridos.",
    "Enviando...",
    "Error desconocido",
    "Error al procesar la solicitud. Por favor intente nuevamente.",
    "Inscribirme",
    "Aún no hay personas anotadas en esta hora. ¡Animate a ser la primera!",
    "Hora",
    "Día",
    "¿Está seguro de que desea eliminar esta inscripción?",
    "Eliminando...",
    "Exportando...",
    "Error al eliminar el registro.",
    "CSV exportado correctamente.",
    "Error al exportar CSV.",
    "Registro eliminado correctamente.",
    "Configuración guardada correctamente.",
    "Error al procesar la solicitud.",
    "No tiene permisos suficientes para realizar esta acción.",
    "ID de registro inválido.",
    "No se pudo eliminar el registro.",
    "Mes no especificado.",
    "No se pudo exportar el archivo CSV.",
    "Archivo exportado correctamente."
];

// Simplified basic translations for EN as an example, other languages will have empty strings for Loco Translate
$en_translations = [
    "Error de seguridad. Por favor recargue la página." => "Security error. Please reload the page.",
    "Ha excedido el límite de intentos. Por favor espere una hora." => "Rate limit exceeded. Please wait an hour.",
    "Error en la verificación CAPTCHA." => "CAPTCHA verification error.",
    "Horario inválido." => "Invalid schedule.",
    "Ese horario ya está completo." => "That schedule is already full.",
    "Error al guardar la inscripción. Por favor intente nuevamente." => "Error saving registration. Please try again.",
    "Inscripción realizada correctamente." => "Registration completed successfully.",
    "Error de seguridad." => "Security error.",
    "Por favor complete todos los campos requeridos." => "Please fill all required fields.",
    "Enviando..." => "Sending...",
    "Error desconocido" => "Unknown error",
    "Error al procesar la solicitud. Por favor intente nuevamente." => "Error processing request. Please try again.",
    "Inscribirme" => "Register",
    "Aún no hay personas anotadas en esta hora. ¡Animate a ser la primera!" => "There are no people registered for this hour yet. Be the first!",
    "Hora" => "Hour",
    "Día" => "Day",
    "¿Está seguro de que desea eliminar esta inscripción?" => "Are you sure you want to delete this registration?",
    "Eliminando..." => "Deleting...",
    "Exportando..." => "Exporting...",
    "Error al eliminar el registro." => "Error deleting record.",
    "CSV exportado correctamente." => "CSV exported successfully.",
    "Error al exportar CSV." => "Error exporting CSV.",
    "Registro eliminado correctamente." => "Record deleted successfully.",
    "Configuración guardada correctamente." => "Settings saved successfully.",
    "Error al procesar la solicitud." => "Error processing request.",
    "No tiene permisos suficientes para realizar esta acción." => "You do not have sufficient permissions to perform this action.",
    "ID de registro inválido." => "Invalid record ID.",
    "No se pudo eliminar el registro." => "Could not delete record.",
    "Mes no especificado." => "Month not specified.",
    "No se pudo exportar el archivo CSV." => "Could not export CSV file.",
    "Archivo exportado correctamente." => "File exported successfully."
];

$output_dir = __DIR__;

// Create POT file
$pot_content = 'msgid ""' . "\n";
$pot_content .= 'msgstr ""' . "\n";
$pot_content .= '"Project-Id-Version: 40 Horas de Oracion\n"' . "\n";
$pot_content .= '"MIME-Version: 1.0\n"' . "\n";
$pot_content .= '"Content-Type: text/plain; charset=UTF-8\n"' . "\n";
$pot_content .= '"Content-Transfer-Encoding: 8bit\n"' . "\n";
$pot_content .= '"Language: en_US\n"' . "\n\n";

foreach ($strings as $string) {
    $pot_content .= 'msgid "' . str_replace('"', '\"', $string) . '"' . "\n";
    $pot_content .= 'msgstr ""' . "\n\n";
}

file_put_contents($output_dir . '/40-horas-oracion.pot', $pot_content);

// Create PO files for each language
foreach ($languages as $locale => $lang_info) {
    $po_content = 'msgid ""' . "\n";
    $po_content .= 'msgstr ""' . "\n";
    $po_content .= '"Project-Id-Version: 40 Horas de Oracion\n"' . "\n";
    $po_content .= '"MIME-Version: 1.0\n"' . "\n";
    $po_content .= '"Content-Type: text/plain; charset=UTF-8\n"' . "\n";
    $po_content .= '"Content-Transfer-Encoding: 8bit\n"' . "\n";
    $po_content .= '"Language: ' . $locale . '\n"' . "\n\n";

    foreach ($strings as $string) {
        $translation = "";
        if ($locale === 'en_US' && isset($en_translations[$string])) {
            $translation = $en_translations[$string];
        } elseif ($locale === 'es_ES') {
            $translation = $string; // Base language
        }
        
        $po_content .= 'msgid "' . str_replace('"', '\"', $string) . '"' . "\n";
        $po_content .= 'msgstr "' . str_replace('"', '\"', $translation) . '"' . "\n\n";
    }
    
    file_put_contents($output_dir . '/40-horas-oracion-' . $locale . '.po', $po_content);
}

echo "Archivos POT y PO generados correctamente para los 13 idiomas.";
