<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Spanish language strings for local_coursehead.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowcss'] = 'Permitir CSS personalizado';
$string['allowcss_desc'] = 'Permitir que se edite y se sirva CSS personalizado por curso.';
$string['allowjs'] = 'Permitir JavaScript personalizado';
$string['allowjs_desc'] = 'Permitir que se edite y se sirva JavaScript personalizado por curso. El JavaScript personalizado se ejecuta en el navegador de todos los usuarios que visualizan el curso; actívelo solo si la capacidad local/coursehead:managejs está restringida a roles de total confianza. Desactivado por defecto.';
$string['assetsdeleted'] = 'Recursos personalizados del curso eliminados.';
$string['assetssaved'] = 'Recursos personalizados del curso guardados.';
$string['cachedef_assetmeta'] = 'Metadatos de los recursos personalizados por curso';
$string['coursehead:manage'] = 'Gestionar los recursos personalizados del curso';
$string['coursehead:managecss'] = 'Editar el CSS personalizado del curso';
$string['coursehead:managejs'] = 'Editar el JavaScript personalizado del curso (solo usuarios de confianza)';
$string['coursesettings'] = 'Configuración de recursos del curso';
$string['csseditingunavailable'] = 'La edición de CSS no está disponible. El CSS personalizado está desactivado a nivel de sitio o no tiene permiso para editarlo.';
$string['cssurl'] = 'URL del recurso CSS';
$string['currentstatus'] = 'Estado actual';
$string['customcss'] = 'CSS personalizado';
$string['customcss_help'] = 'Pegue únicamente reglas CSS. No incluya etiquetas &lt;style&gt;.';
$string['customjs'] = 'JavaScript personalizado';
$string['customjs_help'] = 'Pegue únicamente JavaScript. No incluya etiquetas &lt;script&gt;. JavaScript es una herramienta potente y solo debe habilitarse para usuarios de confianza.';
$string['defaultjsloadstrategy'] = 'Estrategia de carga de JavaScript por defecto';
$string['defaultjsloadstrategy_desc'] = 'Cómo se carga por defecto la etiqueta de script en las nuevas configuraciones de curso. Se recomienda encarecidamente "Diferida" (defer): no bloquea el renderizado de la página.';
$string['deleteassets'] = 'Eliminar los recursos personalizados del curso';
$string['deleteconfirm'] = '¿Está seguro de que desea eliminar todos los recursos personalizados del curso «{$a}»? El CSS y el JavaScript almacenados se eliminarán de forma permanente.';
$string['enablecourseassets'] = 'Habilitar recursos personalizados para este curso';
$string['enablecss'] = 'Habilitar CSS personalizado';
$string['enabled'] = 'Habilitar el plugin';
$string['enabled_desc'] = 'Interruptor general. Cuando está desactivado, no se inyecta ni se sirve ningún recurso personalizado en ningún curso.';
$string['enablejs'] = 'Habilitar JavaScript personalizado';
$string['errorforbiddentag'] = 'El código no debe contener "{$a}". Pegue únicamente reglas CSS o JavaScript sin etiquetas HTML ni PHP.';
$string['errortoolong'] = 'El código supera la longitud máxima permitida de {$a} caracteres.';
$string['errorvalidationfailed'] = 'El código enviado no superó la validación y no se ha guardado.';
$string['eventcourseassetscreated'] = 'Recursos personalizados del curso creados';
$string['eventcourseassetsdeleted'] = 'Recursos personalizados del curso eliminados';
$string['eventcourseassetsupdated'] = 'Recursos personalizados del curso actualizados';
$string['excludedpaths'] = 'Rutas excluidas';
$string['excludedpaths_desc'] = 'Un prefijo de ruta por línea (relativo a la raíz de Moodle). Las páginas cuya ruta comience por un prefijo listado nunca reciben recursos personalizados. Las líneas que comienzan con # son comentarios. Ejemplo: /mod/quiz/attempt.php';
$string['injectonactivitypages'] = 'Inyectar en páginas de actividades';
$string['injectonactivitypages_desc'] = 'Inyectar los recursos personalizados en las páginas de actividades (módulos) del curso.';
$string['injectoncoursehome'] = 'Inyectar en la página principal del curso';
$string['injectoncoursehome_desc'] = 'Inyectar los recursos personalizados en la página principal del curso.';
$string['injectongradepages'] = 'Inyectar en páginas de calificaciones';
$string['injectongradepages_desc'] = 'Inyectar los recursos personalizados en las páginas del libro de calificaciones del curso. Desactivado por defecto porque las páginas de calificaciones son sensibles y rara vez necesitan personalización.';
$string['jseditingunavailable'] = 'La edición de JavaScript no está disponible. El JavaScript personalizado está desactivado a nivel de sitio o no tiene permiso para editarlo.';
$string['jsloadstrategy'] = 'Estrategia de carga de JavaScript';
$string['jsloadstrategy_help'] = '«Diferida» (recomendada) carga el script sin bloquear el renderizado de la página y lo ejecuta después de analizar el documento. «Bloqueante» carga y ejecuta el script inmediatamente, pausando el renderizado de la página.';
$string['jssecuritywarning'] = 'El JavaScript personalizado se ejecuta para los usuarios que visualizan este curso. Conceda este permiso solo a usuarios de confianza.';
$string['jsurl'] = 'URL del recurso JavaScript';
$string['managecourseassets'] = 'Recursos personalizados del curso';
$string['maxcsslength'] = 'Longitud máxima del CSS';
$string['maxcsslength_desc'] = 'Número máximo de caracteres permitidos en el CSS personalizado de un curso. Use 0 para no establecer límite.';
$string['maxjslength'] = 'Longitud máxima del JavaScript';
$string['maxjslength_desc'] = 'Número máximo de caracteres permitidos en el JavaScript personalizado de un curso. Use 0 para no establecer límite.';
$string['noassets'] = 'Todavía no hay recursos personalizados configurados para este curso.';
$string['pluginname'] = 'Recursos personalizados en el head del curso';
$string['privacy:metadata'] = 'El plugin almacena únicamente configuración CSS y JavaScript a nivel de curso y no almacena datos personales.';
$string['restoredroppedcss'] = 'El CSS personalizado no se ha restaurado: el usuario que restaura no tiene la capacidad local/coursehead:managecss en el curso de destino o el contenido no superó la validación.';
$string['restoredroppedjs'] = 'El JavaScript personalizado no se ha restaurado: el usuario que restaura no tiene la capacidad local/coursehead:managejs en el curso de destino o el contenido no superó la validación.';
$string['restorekeptexisting'] = 'El curso de destino ya tiene recursos personalizados; se ha conservado la configuración existente.';
$string['revision'] = 'Revisión';
$string['showwarnings'] = 'Mostrar advertencias de seguridad';
$string['showwarnings_desc'] = 'Mostrar una advertencia de seguridad en la página de gestión del curso cuando la edición de JavaScript esté disponible.';
$string['statusdisabled'] = 'Desactivado';
$string['statusenabled'] = 'Activado';
$string['strategyblocking'] = 'Bloqueante';
$string['strategydefer'] = 'Diferida (recomendada)';
