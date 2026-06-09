Aquí tienes 5 indicadores negativos de productividad para detectar problemas, cuellos de botella o bajo rendimiento en tu equipo de EspoCRM:

---

### ❌ 1. Tareas Vencidas sin Cerrar

- **Fórmula**: Cantidad de Tareas con Fecha de Límite (dateDue) anterior a hoy y Estado ≠ Completada.
- **Objetivo**: Identificar incumplimientos crónicos y tareas abandonadas.
- **Implementación**: Crea un Informe de Lista sobre Tareas. Filtros: dateDue < fecha actual (usa el campo "Menor que hoy") y Estado no es igual a Completada. Agrupa por Asignado a. Un valor alto indica problemas de gestión del tiempo o sobrecarga.

---

### ❌ 2. Tareas Reabiertas (Ciclos de Reproceso)

- **Fórmula**: Cantidad de Tareas que hayan cambiado de Estado "Completada" a otro estado nuevamente.
- **Objetivo**: Detectar trabajos mal hechos o requisitos mal comprendidos que generan retrabajo.
- **Implementación**: No hay un campo nativo de "contador de reaperturas". Solución práctica: crea un campo personalizado tipo Integer llamado "Veces Reabierta". Mediante un Workflow o BPMN (si tienes Advanced Pack), incrementa ese campo cada vez que una tarea pase de Completada a otro estado. Luego, un informe que sume ese campo por usuario.

---

### ❌ 3. Tareas sin Asignar (Huérfanas)

- **Fórmula**: Cantidad de Tareas donde assigned_user_id es NULL.
- **Objetivo**: Medir desorganización en la distribución del trabajo. Las tareas sin dueño suelen paralizarse.
- **Implementación**: Informe de Lista sobre Tareas con filtro Asignado a = (vacío). Agrega columnas de Creado por y Fecha de Creación para responsabilizar al creador de la tarea huérfana. Un número creciente indica mala práctica de creación.

---

### ❌ 4. Tareas Estancadas (Sin Actividad Reciente)

- **Fórmula**: Cantidad de Tareas con Estado ≠ Completada y Última Modificación (modified_at) anterior a X días (ej. 7 o 15).
- **Objetivo**: Detectar tareas "olvidadas" que no avanzan pero tampoco se cierran.
- **Implementación**: Informe de Lista sobre Tareas. Filtros: Estado no es igual a Completada, modified_at es menor a (fecha actual - N días). Agrupar por Asignado a. Ideal para revisión semanal de estancamiento.

---

### ❌ 5. Baja Proporción de Cierre (Ratio de Abandono)

- **Fórmula**: (Tareas Creadas - Tareas Completadas) / Tareas Creadas en un período.
- **Objetivo**: Medir qué porcentaje del trabajo iniciado nunca se termina (brecha de productividad real).
- **Implementación**: Crea un Informe de Cuadrícula para Tareas con filtro de fecha (ej. creadas en el último mes). Agrega dos columnas: COUNT de ID (total creadas) y COUNT de ID con filtro Estado = Completada. El indicador negativo se calcula como 1 - (Completadas/Creadas). Valores cercanos a 0 son buenos; cercanos a 1 son críticos.

---

Estos indicadores te permitirán enfocar acciones correctivas donde realmente hay fuga de productividad. ¿Necesitas ayuda para construir alguno de estos reportes dentro de EspoCRM?