-- ============================================================
--  SISTEMA DE INDICADORES DE GESTIÓN ECONÓMICA REGIONAL
--  Motor: PostgreSQL 15 | Cliente: DBeaver
--  Tabla principal: entidad
-- ============================================================
--
--  INDICADORES INCLUIDOS:
--  1. INOS  — Índice de Nivel Operativo Sectorial
--  2. IPDM  — Índice de Penetración Digital y Presencia de Mercado
--  3. IDCHS — Índice de Densidad de Capital Humano por Sector
--  4. IEET  — Índice de Estructura Económica Territorial
--  5. IASE  — Índice de Antigüedad y Supervivencia Empresarial
--  6. IDPDI — Índice de Diversificación Productiva y Dependencia de Insumos
--
--  NOTA: Los JOINs geográficos usan el campo entidad.municipio_parroquia
--        (entero de 4 dígitos, ej: 101 = '0101' = Bejuma/Bejuma)
--        y se resuelven contra la tabla ref_geo incluida en este script.
-- ============================================================


-- ============================================================
-- 0. TABLA DE REFERENCIA GEOGRÁFICA
--    Municipios y parroquias del estado Carabobo
-- ============================================================
CREATE TABLE IF NOT EXISTS ref_geo (
    codigo_parroquia    CHAR(4)     NOT NULL,
    cod_municipio       CHAR(2)     NOT NULL,
    municipio           VARCHAR(40) NOT NULL,
    parroquia           VARCHAR(40) NOT NULL,
    CONSTRAINT pk_ref_geo PRIMARY KEY (codigo_parroquia)
);

COMMENT ON TABLE  ref_geo                  IS 'Referencia geográfica municipios/parroquias Carabobo';
COMMENT ON COLUMN ref_geo.codigo_parroquia IS 'Código compuesto 4 dígitos MMPP';
COMMENT ON COLUMN ref_geo.cod_municipio    IS 'Primeros 2 dígitos = municipio';

INSERT INTO ref_geo (codigo_parroquia, cod_municipio, municipio, parroquia) VALUES
('0101','01','Bejuma',         'Bejuma'),
('0102','01','Bejuma',         'Canoabo'),
('0103','01','Bejuma',         'Simón Bolívar'),
('0201','02','Carlos Arvelo',  'Belén'),
('0202','02','Carlos Arvelo',  'Güigüe'),
('0203','02','Carlos Arvelo',  'Tacarigua'),
('0301','03','Diego Ibarra',   'Aguas Calientes'),
('0302','03','Diego Ibarra',   'Mariara'),
('0401','04','Guacara',        'Ciudad Alianza'),
('0402','04','Guacara',        'Guacara'),
('0403','04','Guacara',        'Yagua'),
('0501','05','Juan José Mora', 'Morón'),
('0502','05','Juan José Mora', 'Urama'),
('0601','06','Libertador',     'Independencia'),
('0602','06','Libertador',     'Tocuyito'),
('0701','07','Los Guayos',     'Los Guayos'),
('0801','08','Miranda',        'Miranda'),
('0901','09','Montalbán',      'Montalbán'),
('1001','10','Naguanagua',     'Naguanagua'),
('1101','11','Puerto Cabello', 'Bartolomé Salom'),
('1102','11','Puerto Cabello', 'Borburata'),
('1103','11','Puerto Cabello', 'Democracia'),
('1104','11','Puerto Cabello', 'Fraternidad'),
('1105','11','Puerto Cabello', 'Goaigoaza'),
('1106','11','Puerto Cabello', 'Juan José Flores'),
('1107','11','Puerto Cabello', 'Patanemo'),
('1108','11','Puerto Cabello', 'Unión'),
('1201','12','San Diego',      'San Diego'),
('1301','13','San Joaquín',    'San Joaquín'),
('1401','14','Valencia',       'Candelaria'),
('1402','14','Valencia',       'Catedral'),
('1403','14','Valencia',       'El Socorro'),
('1404','14','Valencia',       'Miguel Peña'),
('1405','14','Valencia',       'Negro Primero'),
('1406','14','Valencia',       'Rafael Urdaneta'),
('1407','14','Valencia',       'San Blas'),
('1408','14','Valencia',       'San José'),
('1409','14','Valencia',       'Santa Rosa')
ON CONFLICT (codigo_parroquia) DO NOTHING;


-- ============================================================
-- 1. INOS — Índice de Nivel Operativo Sectorial
--    Mide el % promedio de operatividad real por sector/municipio
--    Campo clave: operativa (entero 0-100)
-- ============================================================
CREATE OR REPLACE VIEW v_inos AS
SELECT
    s.nombre                                                AS sector,
    g.municipio,
    COUNT(e.codigo)                                         AS total_entidades,
    ROUND(AVG(e.operativa), 1)                              AS inos,
    MIN(e.operativa)                                        AS minimo,
    MAX(e.operativa)                                        AS maximo,
    COUNT(*) FILTER (WHERE e.operativa >= 80)               AS cnt_optimo,
    COUNT(*) FILTER (WHERE e.operativa >= 60
                       AND e.operativa <  80)               AS cnt_aceptable,
    COUNT(*) FILTER (WHERE e.operativa >= 40
                       AND e.operativa <  60)               AS cnt_bajo,
    COUNT(*) FILTER (WHERE e.operativa <  40)               AS cnt_critico,
    CASE
        WHEN ROUND(AVG(e.operativa), 1) >= 80 THEN 'Óptimo'
        WHEN ROUND(AVG(e.operativa), 1) >= 60 THEN 'Aceptable'
        WHEN ROUND(AVG(e.operativa), 1) >= 40 THEN 'Bajo'
        ELSE 'Crítico'
    END                                                     AS semaforo
FROM entidad e
JOIN ref_sectores s ON s.codigo = e.sector
JOIN ref_geo      g ON g.codigo_parroquia
                     = LPAD(e.municipio_parroquia::TEXT, 4, '0')
WHERE e.operativa IS NOT NULL
GROUP BY s.nombre, g.municipio
ORDER BY inos DESC;


-- ============================================================
-- 2. IPDM — Índice de Penetración Digital y Presencia de Mercado
--    Mide visibilidad digital + participación de mercado
--    Campos: web_entidad, instagram_entidad, twitter_entidad,
--            facebook_entidad, participacion_mercado
-- ============================================================
CREATE OR REPLACE VIEW v_ipdm AS
WITH digital AS (
    SELECT
        e.codigo,
        e.sector,
        e.municipio_parroquia,
        (CASE WHEN e.web_entidad       IS NOT NULL
               AND e.web_entidad       <> '' THEN 1 ELSE 0 END +
         CASE WHEN e.instagram_entidad IS NOT NULL
               AND e.instagram_entidad <> '' THEN 1 ELSE 0 END +
         CASE WHEN e.twitter_entidad   IS NOT NULL
               AND e.twitter_entidad   <> '' THEN 1 ELSE 0 END +
         CASE WHEN e.facebook_entidad  IS NOT NULL
               AND e.facebook_entidad  <> '' THEN 1 ELSE 0 END
        )                                                   AS canales,
        e.participacion_mercado
    FROM entidad e
)
SELECT
    s.nombre                                                AS sector,
    g.municipio,
    COUNT(d.codigo)                                         AS total_entidades,
    ROUND(AVG(d.canales), 2)                                AS promedio_canales_digitales,
    ROUND(AVG(d.participacion_mercado), 1)                  AS promedio_mercado,
    ROUND(AVG(d.canales / 4.0 * 40
            + d.participacion_mercado * 0.60), 1)           AS ipdm,
    CASE
        WHEN ROUND(AVG(d.canales / 4.0 * 40
                + d.participacion_mercado * 0.60), 1) >= 70 THEN 'Alto'
        WHEN ROUND(AVG(d.canales / 4.0 * 40
                + d.participacion_mercado * 0.60), 1) >= 40 THEN 'Medio'
        ELSE 'Bajo'
    END                                                     AS nivel
FROM digital d
JOIN ref_sectores s ON s.codigo = d.sector
JOIN ref_geo      g ON g.codigo_parroquia
                     = LPAD(d.municipio_parroquia::TEXT, 4, '0')
GROUP BY s.nombre, g.municipio
ORDER BY ipdm DESC;


-- ============================================================
-- 3. IDCHS — Índice de Densidad de Capital Humano por Sector
--    Mide concentración de empleos por sector y tamaño
--    Campos: capital_humano, sector, tamano_entidad
-- ============================================================
CREATE OR REPLACE VIEW v_idchs AS
SELECT
    s.nombre                                                AS sector,
    g.municipio,
    t.nombre                                                AS tamano,
    COUNT(e.codigo)                                         AS total_entidades,
    SUM(e.capital_humano)                                   AS total_empleos,
    ROUND(AVG(e.capital_humano), 1)                         AS promedio_empleos,
    MIN(e.capital_humano)                                   AS minimo_empleos,
    MAX(e.capital_humano)                                   AS maximo_empleos,
    ROUND(SUM(e.capital_humano) * 100.0 /
        NULLIF(SUM(SUM(e.capital_humano))
            OVER (PARTITION BY g.municipio), 0), 1)         AS peso_empleo_municipio_pct
FROM entidad e
JOIN ref_sectores s ON s.codigo = e.sector
JOIN ref_tamanos  t ON t.codigo = e.tamano_entidad
JOIN ref_geo      g ON g.codigo_parroquia
                     = LPAD(e.municipio_parroquia::TEXT, 4, '0')
WHERE e.capital_humano IS NOT NULL
GROUP BY s.nombre, g.municipio, t.nombre
ORDER BY g.municipio, total_empleos DESC;


-- ============================================================
-- 4. IEET — Índice de Estructura Económica Territorial
--    Mide diversificación sectorial por municipio
--    Campos: sector, municipio_parroquia
-- ============================================================
CREATE OR REPLACE VIEW v_ieet AS
WITH base AS (
    SELECT
        g.municipio,
        s.nombre                                            AS sector,
        COUNT(e.codigo)                                     AS entidades_sector
    FROM entidad e
    JOIN ref_sectores s ON s.codigo = e.sector
    JOIN ref_geo      g ON g.codigo_parroquia
                         = LPAD(e.municipio_parroquia::TEXT, 4, '0')
    GROUP BY g.municipio, s.nombre
),
totales AS (
    SELECT municipio, SUM(entidades_sector) AS total_municipio
    FROM base
    GROUP BY municipio
)
SELECT
    b.municipio,
    b.sector,
    b.entidades_sector,
    t.total_municipio,
    ROUND(b.entidades_sector * 100.0 /
        NULLIF(t.total_municipio, 0), 1)                    AS peso_pct,
    CASE
        WHEN ROUND(b.entidades_sector * 100.0 /
            NULLIF(t.total_municipio, 0), 1) >= 60
                                            THEN 'Sector dominante'
        WHEN ROUND(b.entidades_sector * 100.0 /
            NULLIF(t.total_municipio, 0), 1) >= 30
                                            THEN 'Sector relevante'
        ELSE                                     'Sector secundario'
    END                                                     AS rol_sector
FROM base b
JOIN totales t ON t.municipio = b.municipio
ORDER BY b.municipio, peso_pct DESC;


-- ============================================================
-- 5. IASE — Índice de Antigüedad y Supervivencia Empresarial
--    Mide estabilidad del tejido empresarial por tramos de edad
--    Campo clave: fecha_inicio
-- ============================================================
CREATE OR REPLACE VIEW v_iase AS
WITH anos AS (
    SELECT
        e.codigo,
        e.sector,
        e.tamano_entidad,
        e.municipio_parroquia,
        e.operativa,
        EXTRACT(YEAR FROM CURRENT_DATE)
        - EXTRACT(YEAR FROM e.fecha_inicio)                 AS anos_actividad
    FROM entidad e
    WHERE e.fecha_inicio IS NOT NULL
)
SELECT
    s.nombre                                                AS sector,
    g.municipio,
    t.nombre                                                AS tamano,
    COUNT(a.codigo)                                         AS total_entidades,
    ROUND(AVG(a.anos_actividad), 1)                         AS antiguedad_promedio,
    CASE
        WHEN ROUND(AVG(a.anos_actividad), 1) <= 2  THEN 'Emergente'
        WHEN ROUND(AVG(a.anos_actividad), 1) <= 5  THEN 'En consolidación'
        WHEN ROUND(AVG(a.anos_actividad), 1) <= 10 THEN 'Establecida'
        WHEN ROUND(AVG(a.anos_actividad), 1) <= 20 THEN 'Madura'
        ELSE 'Ancla territorial'
    END                                                     AS clasificacion,
    COUNT(*) FILTER (WHERE a.anos_actividad <= 2)           AS cnt_emergente,
    COUNT(*) FILTER (WHERE a.anos_actividad BETWEEN 3  AND 5)  AS cnt_consolidacion,
    COUNT(*) FILTER (WHERE a.anos_actividad BETWEEN 6  AND 10) AS cnt_establecida,
    COUNT(*) FILTER (WHERE a.anos_actividad BETWEEN 11 AND 20) AS cnt_madura,
    COUNT(*) FILTER (WHERE a.anos_actividad > 20)           AS cnt_ancla,
    ROUND(AVG(a.operativa), 1)                              AS operativa_promedio
FROM anos a
JOIN ref_sectores s ON s.codigo = a.sector
JOIN ref_tamanos  t ON t.codigo = a.tamano_entidad
JOIN ref_geo      g ON g.codigo_parroquia
                     = LPAD(a.municipio_parroquia::TEXT, 4, '0')
GROUP BY s.nombre, g.municipio, t.nombre
ORDER BY g.municipio, antiguedad_promedio DESC;


-- ============================================================
-- 6. IDPDI — Índice de Diversificación Productiva
--             y Dependencia de Insumos
--    Conteo de ítems en productos y materia_prima (separados por coma)
--    Campos: productos, materia_prima
-- ============================================================
CREATE OR REPLACE VIEW v_idpdi AS
WITH conteos AS (
    SELECT
        e.codigo,
        e.sector,
        e.tamano_entidad,
        e.municipio_parroquia,
        CASE WHEN e.productos IS NOT NULL AND e.productos <> ''
             THEN LENGTH(e.productos)
                  - LENGTH(REPLACE(e.productos, ',', '')) + 1
             ELSE 0 END                                     AS n_productos,
        CASE WHEN e.materia_prima IS NOT NULL AND e.materia_prima <> ''
             THEN LENGTH(e.materia_prima)
                  - LENGTH(REPLACE(e.materia_prima, ',', '')) + 1
             ELSE 0 END                                     AS n_insumos
    FROM entidad e
    WHERE e.productos IS NOT NULL AND e.materia_prima IS NOT NULL
)
SELECT
    s.nombre                                                AS sector,
    g.municipio,
    t.nombre                                                AS tamano,
    COUNT(c.codigo)                                         AS total_entidades,
    ROUND(AVG(c.n_productos), 1)                            AS promedio_productos,
    ROUND(AVG(c.n_insumos), 1)                              AS promedio_insumos,
    ROUND(AVG(
        c.n_productos::NUMERIC / NULLIF(c.n_insumos, 0)
    ), 2)                                                   AS ratio_idpdi,
    CASE
        WHEN ROUND(AVG(
            c.n_productos::NUMERIC / NULLIF(c.n_insumos, 0)
        ), 2) >= 2   THEN 'Alta diversificación'
        WHEN ROUND(AVG(
            c.n_productos::NUMERIC / NULLIF(c.n_insumos, 0)
        ), 2) >= 1   THEN 'Equilibrado'
        ELSE              'Alta dependencia de insumos'
    END                                                     AS clasificacion
FROM conteos c
JOIN ref_sectores s ON s.codigo = c.sector
JOIN ref_tamanos  t ON t.codigo = c.tamano_entidad
JOIN ref_geo      g ON g.codigo_parroquia
                     = LPAD(c.municipio_parroquia::TEXT, 4, '0')
GROUP BY s.nombre, g.municipio, t.nombre
ORDER BY ratio_idpdi DESC;


-- ============================================================
-- TABLA HISTÓRICA — MEDICIÓN CONTINUA MENSUAL
-- ============================================================
CREATE TABLE IF NOT EXISTS historico_indicadores (
    id          SERIAL          NOT NULL,
    fecha_corte DATE            NOT NULL,
    indicador   VARCHAR(10)     NOT NULL,
    sector      VARCHAR(60),
    municipio   VARCHAR(40),
    tamano      VARCHAR(20),
    valor       NUMERIC(8,2),
    valor_2     NUMERIC(8,2),
    semaforo    VARCHAR(30),
    observacion VARCHAR(200),
    CONSTRAINT pk_historico PRIMARY KEY (id)
);

CREATE INDEX IF NOT EXISTS idx_hist_fecha     ON historico_indicadores(fecha_corte);
CREATE INDEX IF NOT EXISTS idx_hist_indicador ON historico_indicadores(indicador);
CREATE INDEX IF NOT EXISTS idx_hist_municipio ON historico_indicadores(municipio);

COMMENT ON TABLE  historico_indicadores             IS 'Snapshots mensuales de los 6 indicadores';
COMMENT ON COLUMN historico_indicadores.fecha_corte IS 'Fecha del snapshot, usar siempre el 1ro del mes';
COMMENT ON COLUMN historico_indicadores.valor_2     IS 'Campo auxiliar: ratio, mínimo u otro valor secundario';


-- ============================================================
-- VERIFICACIÓN FINAL
-- ============================================================

-- Confirmar tabla geográfica
SELECT cod_municipio, municipio, COUNT(*) AS parroquias
FROM ref_geo
GROUP BY cod_municipio, municipio
ORDER BY cod_municipio;
-- Esperado: 14 municipios

-- Listar vistas creadas
SELECT viewname, definition
FROM pg_views
WHERE viewname IN ('v_inos','v_ipdm','v_idchs','v_ieet','v_iase','v_idpdi')
ORDER BY viewname;
-- Esperado: 6 vistas

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
