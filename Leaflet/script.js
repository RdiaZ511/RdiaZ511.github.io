/**
 * DEMOSTRACIÓN DE LEAFLET JS - ESTADO CARABOBO, VENEZUELA
 */

// 1. L.map - Inicialización del Mapa
// Creamos el contenedor del mapa y definimos su centro (Valencia) y zoom inicial.
// También establecemos coordenadas máximas para circunscribir el mapa al estado Carabobo.
const caraboboBounds = [
  [9.7, -68.5], // Suroeste
  [10.6, -67.5] // Noreste
];

const map = L.map('map', {
  center: [10.162, -68.0],
  zoom: 10,
  maxBounds: caraboboBounds,
  maxBoundsViscosity: 1.0 // Evita que el usuario arrastre fuera de los límites
});

// 2. L.tileLayer - Capas Base (OpenStreetMap)
const osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
  maxZoom: 17,
  attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
});

// 3. L.marker - Marcadores de Puntos Geográficos
// Creamos marcadores para Valencia, Puerto Cabello y el histórico Campo de Carabobo.
const valencia = L.marker([10.162, -67.997]).addTo(map);
const puerto = L.marker([10.473, -68.012]).addTo(map);
const campo = L.marker([9.996, -68.163]).addTo(map);

// 4. L.popup & L.tooltip - Información Contextual
// El popup aparece al hacer clic; el tooltip al pasar el cursor.
valencia.bindPopup("<b>Valencia</b><br>Capital industrial de Venezuela.").openPopup();
valencia.bindTooltip("Haz click para saber más", { className: 'custom-tooltip' });

puerto.bindPopup("<b>Puerto Cabello</b><br>El puerto más importante del país.");
puerto.bindTooltip("Zona Costera");

campo.bindPopup("<b>Campo de Carabobo</b><br>Lugar histórico de la Batalla de Carabobo.");

// 5. L.circle - Representación de Áreas Circulares
// El Lago de Valencia representado por un círculo azul translúcido.
const lagoValencia = L.circle([10.18, -67.73], {
  color: 'blue',
  fillColor: '#30a',
  fillOpacity: 0.3,
  radius: 8000 // 8km de radio aproximado para el demo
}).addTo(map);
lagoValencia.bindPopup("Lago de Valencia (Tacarigua)");

// 6. L.polygon - Formas Geométricas Cerradas
// Simulamos el área del Valle de Valencia.
const valenciaValley = L.polygon([
  [10.22, -68.10],
  [10.25, -67.90],
  [10.10, -67.85],
  [10.05, -68.05]
], {
  color: 'orange',
  fillOpacity: 0.1
}).addTo(map);
valenciaValley.bindTooltip("Valle de Valencia");

// 7. L.polyline - Conexiones de Rutas
// Línea que representa la Autopista Valencia - Puerto Cabello.
const rutaPuerto = L.polyline([
  [10.170, -68.000],
  [10.250, -68.020],
  [10.350, -68.010],
  [10.460, -68.012]
], {
  color: 'red',
  weight: 4,
  dashArray: '5, 10'
}).addTo(map);
rutaPuerto.bindTooltip("Autopista Valencia - Puerto Cabello");

// 8. L.layerGroup - Agrupación de Capas
// Agrupamos los puntos históricos/de interés para facilitar su control.
const sitiosInteres = L.layerGroup([valencia, puerto, campo, rutaPuerto]);

// 9. L.control.layers - Panel de Control de Usuario
const baseMaps = {
  "Mapa Estándar": osm,
  "Mapa Topográfico": topo
};

const overlayMaps = {
  "Sitios de Interés": sitiosInteres,
  "Lago de Valencia": lagoValencia,
  "Valle de Valencia": valenciaValley
};

L.control.layers(baseMaps, overlayMaps).addTo(map);

// 10. L.control.scale - Escala Visual
L.control.scale({ imperial: false, position: 'bottomright' }).addTo(map);

// 11. Event Handling - Interacción con el Usuario
// Al hacer clic en el mapa, mostramos las coordenadas en la consola y un marcador temporal.
map.on('click', function(e) {
  const popup = L.popup()
    .setLatLng(e.latlng)
    .setContent("Muestreo en: " + e.latlng.toString())
    .openOn(map);
  console.log("Coordenadas clickeadas: ", e.latlng);
});

// Función educativa: Actualizar el resumen en la barra lateral al mover el mapa
map.on('moveend', function() {
  const center = map.getCenter();
  console.log("El mapa se movió al centro: ", center);
});
