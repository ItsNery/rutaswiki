# 🗺️ RutasWiki

**RutasWiki** es una enciclopedia libre, abierta y colaborativa diseñada para mapear, documentar y catalogar el transporte público (rutas de autobús, combis, colectivos, líneas de metro y trenes) en comunidades, municipios y estados. Al igual que Wikipedia, **cualquiera puede editar** y colaborar para mantener la información del transporte público actualizada y transparente.

---

## 🚀 Características Clave

### 1. Mapeo Colaborativo Interactivo (Leaflet + Leaflet Draw)
- **Trazado de Calles**: Los editores pueden dibujar la polilínea exacta de la ruta directamente sobre el mapa utilizando herramientas de dibujo geográfico.
- **Registro de Paradas**: Colocación de marcadores interactivos para registrar paradas oficiales, estaciones y transferencias clave.
- **Personalización Visual**: Selector de color único por ruta para identificar y distinguir los trayectos claramente en el lienzo general.

### 2. Normalización de Horarios y Frecuencias (1 a N)
- **Gestión por Tipo de Día**: Configuración independiente para días hábiles (Lunes a Viernes), Sábados, Domingos y Días Festivos.
- **Previsualización en Tiempo Real (AJAX)**: Cálculo automático al instante de las corridas/salidas estimadas del día en base a la hora de inicio, fin y la frecuencia de paso (minutos).
- **Pestañas Interactivas (Tabs)**: Visualización estructurada de horarios y listados de salida en el artículo de la ruta utilizando AlpineJS.

### 3. Lógica de Proximidad Geográfica (Fórmula de Haversine)
- **Rutas Interurbanas**: Las rutas creadas en una ciudad que pasen cerca del centro de otra ciudad vecina (radio de 15 km) se listan e integran automáticamente en los mapas de ambas ciudades de forma fluida.
- **Rutas Cercanas a Mí (Geolocalización en Vivo)**: Búsqueda dinámica en la página de inicio que consulta la ubicación actual del usuario mediante el navegador y grafica en un mapa las líneas de transporte público en un radio configurable (2km, 5km, 10km, 15km).

### 4. Transparencia e Historial de Edición
- **Historial de Revisiones**: Registro cronológico estilo Wikipedia que guarda el estado geográfico, las paradas y el resumen de edición de cada cambio.
- **Puntuación Comunitaria**: Sistema de votos y comentarios para validar el estado o la calidad del servicio de la ruta en tiempo real.

### 5. Registro Seguro de Editores
- **Validación en Tiempo Real**: Comprobación interactiva asíncrona de disponibilidad de nombre de usuario en la pantalla de registro para evitar registros duplicados.

---

## 🛠️ Stack Tecnológico

- **Backend**: Laravel 12 (PHP 8.2+)
- **Base de Datos**: MySQL (con soporte para campos JSON)
- **Frontend**: TailwindCSS (Maquetación y Diseño Premium), AlpineJS (Interactividad del cliente)
- **Mapas**: Leaflet API (Map tiles cortesía de OpenStreetMap) y Leaflet Draw plugin
- **Autenticación**: Laravel Breeze

---

## 📦 Instalación y Configuración Local

Sigue estos pasos para levantar el entorno de desarrollo localmente:

1. **Clonar el Repositorio**
   ```bash
   git clone https://github.com/ItsNery/rutaswiki.git
   cd rutaswiki
   ```

2. **Instalar Dependencias**
   ```bash
   composer install
   npm install
   ```

3. **Configurar el Entorno**
   Copia el archivo de ejemplo y configura tu base de datos MySQL en el archivo `.env`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Correr las Migraciones y Seeders**
   ```bash
   php artisan migrate --seed
   ```

5. **Iniciar Servidores de Desarrollo**
   En terminales separadas, ejecuta:
   ```bash
   # Iniciar el servidor local de Laravel
   php artisan serve

   # Compilar los assets del frontend en tiempo real (Vite)
   npm run dev
   ```

6. **¡Listo!**
   Abre [http://127.0.0.1:8000](http://127.0.0.1:8500) (o el puerto configurado por artisan serve) en tu navegador preferido.

---

## 📄 Licencia
Este proyecto es software de código abierto bajo la licencia [MIT](https://opensource.org/licenses/MIT).
