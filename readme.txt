=== 40 Horas de Oración ===
Contributors: Your Name
Tags: prayer, hours, registration, women, religious, vocations
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Un plugin profesional para WordPress que permite gestionar un sistema de inscripción para las 40 horas continuas de oración.

== Descripción ==

El plugin "40 Horas de Oración" es un sistema completo de inscripción diseñado para comunidades religiosas que se unen en oración durante 40 horas continuas (días 14, 15 y 16 de cada mes).

=== Características principales ===

* **Formulario de inscripción elegante**: Permite a las participantes inscribirse en los horarios disponibles
* **Sistema de contadores**: Muestra participantes del mes actual e histórico total
* **Tabla pública de horarios**: Visualiza todas las inscripciones organizadas por hora
* **Reinicio automático mensual**: El día 18, exporta CSV y reinicia para el nuevo mes
* **Panel de administración completo**: Gestiona inscripciones, configuración y exportaciones
* **CAPTCHA anti-spam**: Integración con Google reCAPTCHA v2 o Cloudflare Turnstile
* **Exportación CSV automática**: Genera reportes mensuales automáticamente
* **Diseño responsive y moderno**: Interfaz optimizada para móviles, tablets y escritorio
* **Internacionalización preparada**: Listo para traducciones
* **Seguridad de alto nivel**: Nonces, sanitización, escaping, prepared statements

== Instalación ==

=== Método 1: Desde el panel de WordPress ===

1. Ve a Plugins > Agregar nuevo
2. Busca "40 Horas de Oración"
3. Haz clic en "Instalar ahora"
4. Activa el plugin

=== Método 2: Instalación manual ===

1. Descarga el archivo del plugin (.zip)
2. Sube la carpeta `40-horas-oracion` a `/wp-content/plugins/`
3. Activa el plugin desde el menú Plugins

== Configuración ==

=== 1. Configurar CAPTCHA ===

El plugin soporta Google reCAPTCHA v2 y Cloudflare Turnstile.

1. Ve a **40 Horas de Oración > Configuración**
2. Completa las claves de tu servicio CAPTCHA elegido:
   - **Google reCAPTCHA**: Obtén tus claves en https://www.google.com/recaptcha/admin
   - **Cloudflare Turnstile**: Obtén tus claves en https://dash.cloudflare.com/

=== 2. Personalizar texto introductorio ===

1. En **Configuración**, edita el campo "Texto introductorio"
2. Personaliza el color principal si lo deseas
3. Guarda los cambios

=== 3. Configurar contador histórico ===

El contador histórico comienza en 13965 por defecto. Puedes personalizarlo en **Configuración**.

=== 4. Opciones de múltiples participantes ===

En **Configuración**:
- **Permitir múltiples personas por hora**: Activado por defecto
- **Máximo por hora**: Deja en 0 para ilimitado, o establece un número máximo

== Uso ==

=== Mostrar el shortcode ===

Coloca este shortcode en cualquier página o entrada:

```
[horas_oracion]
```

Este shortcode muestra:
- Contadores de participantes (mensual e histórico)
- Formulario de inscripción
- Tabla de horarios y participantes
- Texto introductorio personalizable

=== Panel de administración ===

**40 Horas de Oración > Inscripciones**
- Ver todas las inscripciones
- Filtrar por mes y búsqueda
- Eliminar registros individuales
- Exportar CSV manualmente

**40 Horas de Oración > Configuración**
- Configurar claves CAPTCHA
- Personalizar texto y colores
- Ajustar opciones de inscripción
- Configurar contador histórico

**40 Horas de Oración > Exportaciones**
- Descargar archivos CSV históricos
- Ver lista de exportaciones anteriores

== WP-Cron ==

El plugin incluye un sistema automático que:

**Cada mes (día 18)**:
1. Exporta automáticamente un CSV con los registros del mes anterior
2. Limpia la tabla activa
3. Inicia automáticamente el nuevo ciclo

Los archivos CSV se guardan en: `/wp-content/uploads/40-horas-oracion/`

**Nombre del archivo**: `40-horas-oracion-YYYY-MM.csv`

== Estructura de 40 horas ==

Las 40 horas se distribuyen así:

- **Día 14**: Horas 1-16 (08:00 a 23:00)
- **Día 15**: Horas 17-32 (00:00 a 23:00)
- **Día 16**: Horas 33-40 (00:00 a 07:00)

Cada participante elige una hora específica para su oración.

== Base de datos ==

El plugin crea una tabla personalizada: `wp_40_horas_oracion`

Campos:
- id: ID único del registro
- nombre: Nombre de la participante
- apellido: Apellido de la participante
- ciudad: Ciudad de residencia
- pais: País de residencia
- numero_hora: Número de hora elegida (1-40)
- dia: Día del mes (14, 15 o 16)
- hora: Hora exacta (HH:MM)
- created_at: Fecha y hora de inscripción
- ip_address: IP de la participante (para seguridad)

== Seguridad ==

El plugin implementa múltiples capas de seguridad:

- **Nonces**: Protección CSRF en todos los formularios
- **Sanitización**: Limpieza completa de datos de entrada
- **Escaping**: Salida correctamente escapada
- **Prepared Statements**: Prevención de inyección SQL
- **Validaciones server-side**: Validación en el servidor
- **CAPTCHA anti-spam**: Protección contra bots
- **Rate limiting**: Control de envíos por IP
- **Protección XSS**: Prevención de ataques de scripts

== Internacionalización ==

El plugin está completamente preparado para traducciones.

- Text Domain: `40-horas-oracion`
- Domain Path: `/languages`

Para traducir el plugin:
1. Usa un editor de .pot/.po (como Poedit)
2. Extrae las cadenas de texto
3. Traduce a tu idioma
4. Coloca los archivos en `/languages/`

== Preguntas frecuentes ==

**P: ¿Puedo cambiar los horarios de oración?**
R: Los horarios están configurados para los días 14, 15 y 16. Si necesitas cambiarlos, contáctanos.

**P: ¿Qué pasa si la table o un registro se elimina accidentalmente?**
R: Los registros eliminados del mes anterior están guardados en archivos CSV en la carpeta de descargas.

**P: ¿Puedo limitar a una persona por hora?**
R: Sí, en Configuración activa "Máximo por hora" y establece el valor a 1.

**P: ¿Cómo exporto los datos manualmente?**
R: Ve a Inscripciones, selecciona el mes y haz clic en "Exportar CSV".

**P: ¿Los archivos CSV se guardan automáticamente?**
R: Sí, el día 18 se exportan automáticamente y se guardan en `/wp-content/uploads/40-horas-oracion/`

== Soporte ==

Para reportar errores o solicitar nuevas características, contáctanos a través de nuestra página de soporte.

== Cambios ==

= 1.0.0 =
* Lanzamiento inicial del plugin
* Sistema completo de inscripción
* Panel de administración
* Exportación CSV automática
* Reinicio mensual automático
* Integración CAPTCHA
* Diseño responsive

== Licencia ==

Este plugin es software libre: puedes redistribuirlo y/o modificarlo bajo los términos de la Licencia Pública General GNU publicada por la Free Software Foundation, versión 2 o posterior.

Este plugin se distribuye con la esperanza de que sea útil, pero SIN NINGUNA GARANTÍA; ni siquiera la garantía implícita de COMERCIABILIDAD o IDONEIDAD PARA UN PROPÓSITO PARTICULAR. Consulta la Licencia Pública General GNU para más detalles.

Deberías haber recibido una copia de la Licencia Pública General GNU junto con este programa. Si no es así, consulta https://www.gnu.org/licenses/gpl-2.0.html

== Autor ==

Desarrollado con ♥ para comunidades religiosas

© 2026 - 40 Horas de Oración
