**EspoCRM utiliza una estructura de base de datos relacional y dinámica controlada por su propio framework ORM (Object-Relational Mapping).** En lugar de usar un esquema SQL estático, se basa en un framework de **Metadatos** para definir tablas, columnas y relaciones. Cuando creas campos o entidades desde el panel de administración, el sistema traduce automáticamente esas definiciones en cambios físicos en la base de datos.

---

### 1. Convenciones de Nomenclatura de Tablas
Las **tablas de la base de datos corresponden directamente a los nombres de las entidades**, pero escritas en formato `snake_case` (en lugar del `CamelCase` usado en el código PHP).

* **Entidades Estándar:** Una entidad como `TargetList` generará una tabla llamada `target_list`.
* **Tablas Core del CRM:** Incluyen por defecto tablas como `account`, `contact`, `lead`, `opportunity`, `case`, `user` y `team`.
* **Tablas del Sistema:** Datos de infraestructura y seguimiento se guardan en tablas como `attachment`, `activity`, `history` y `stream`.

---

### 2. Esquema de Columnas y Reglas de Campos
Cada tabla principal contiene columnas que coinciden con los campos definidos en el [Entity Manager](https://espocrm.com). Todas las entidades estándar inician con columnas de auditoría:
* `id`: VARCHAR(24) que contiene un identificador alfanumérico único.
* `name`: VARCHAR(255) para el nombre o identificador principal del registro.
* `deleted`: TINYINT(1) / BOOLEAN para borrado lógico (los registros no se eliminan físicamente de inmediato).
* `created_at` / `modified_at`: Campos DATETIME de control de tiempo.
* `created_by_id` / `modified_by_id`: Claves foráneas que apuntan a la tabla `user`.

Los tipos de campos complejos modifican la estructura de columnas:
* **Campos Simples:** Atributos como `varchar`, `int`, `float` y `text` mapean a una sola columna.
* **Campos de Moneda:** Generan dos columnas: `{nombre_campo}` (el valor numérico) y `{nombre_campo}_currency` (el código ISO de la moneda).
* **Campos de Dirección:** Se dividen en múltiples columnas como `{nombre_campo}_street`, `{nombre_campo}_city`, `{nombre_campo}_state` y `{nombre_campo}_postal_code`.

---

### 3. Representación de Relaciones
Las relaciones se definen en los [Metadatos de EspoCRM](https://espocrm.com) y se reflejan en la base de datos según su tipo:

* **Many-to-One / One-to-Many:** Se manejan añadiendo una columna de clave foránea directamente en la tabla del lado "Muchos". Por ejemplo, si un `Contact` pertenece a un `Account`, la tabla `contact` tendrá la columna `account_id`.
* **Many-to-Many (M2M):** Se manejan mediante una tabla intermedia de unión (join table). Se nombran combinando los nombres de ambas entidades en orden alfabético o contextual (ej. `account_contact` u `opportunity_lead`). Contienen las columnas `account_id`, `contact_id` y `deleted`.
* **Enlaces Polimórficos (Parent-to-Children):** Utilizados en tareas, llamadas o historial donde un registro puede vincularse a múltiples tipos de entidades. Requieren dos columnas: `parent_id` (el ID del registro destino) y `parent_type` (el nombre de la entidad en string, como "Account" o "Lead").

---

### 4. Generación del Esquema y Modificaciones
Al ser un sistema guiado por metadatos, nunca se deben ejecutar sentencias `ALTER TABLE` manuales para modificar la arquitectura básica.

* **Herramienta Rebuild:** Cuando un desarrollador cambia archivos de metadatos o un administrador añade campos, la base de datos se desincroniza. Ejecutar el comando `php rebuild.php` o `bin/command rebuild` fuerza a EspoCRM a aplicar los cambios SQL necesarios.
* **Motores Soportados:** El ORM abstrae la capa de datos para que la misma estructura funcione de forma idéntica en motores como [MySQL, MariaDB y PostgreSQL](https://espocrm.com).
