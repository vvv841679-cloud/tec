# Documentación del Sistema de Gestión Hotelera

Esta carpeta contiene todos los diagramas y documentación del sistema.

## 📁 Archivos Disponibles

### 1. Diagrama de Clases UML 2.5
**Archivo:** `diagrama-clases-uml.puml`

Diagrama completo del modelo de datos del sistema, incluyendo:
- Todas las entidades (tablas)
- Atributos con tipos de datos
- Relaciones (1:N, N:M, polimórficas)
- Métodos principales de los modelos
- Notas explicativas

**Entidades principales:**
- Administración: Users, Roles, Permissions
- Clientes: Customers, Countries
- Habitaciones: RoomTypes, Rooms, BedTypes, Facilities
- Reservas: Bookings, BookingStatus, BookingCharges, Payments
- Sistema: Media, Menus, Sessions, PageViews

---

### 2. Mapa de Sitio Completo
**Archivo:** `sitemap-completo.puml`

Estructura jerárquica completa del sistema que muestra:
- **Sitio Público** (clientes)
  - Página principal
  - Habitaciones
  - Búsqueda y reservas
  - Autenticación de clientes
  - Área personal del cliente
  - Páginas informativas

- **Panel Administrativo** (Filament)
  - Dashboard
  - Gestión de habitaciones
  - Gestión de reservas
  - Gestión de pagos
  - Gestión de clientes
  - Configuración
  - Sistema (usuarios, roles, permisos)

Incluye códigos de colores para diferenciar:
- 🔵 Sitio público
- 🟠 Panel administrativo
- 🟣 Autenticación

---

### 3. Flujos de Usuario
**Archivo:** `flujos-usuario.puml`

Diagramas de actividad UML que muestran los procesos principales:

**Flujo 1: Proceso de Reserva (Cliente)**
- Búsqueda de disponibilidad
- Selección de habitación
- Autenticación/Registro
- Checkout y pago
- Confirmación

**Flujo 2: Cancelación de Reserva**
- Verificación de políticas
- Cálculo de reembolso
- Confirmación de cancelación

**Flujo 3: Gestión de Reserva (Administrador)**
- Crear reserva manual
- Editar reserva existente
- Cambiar estados
- Registrar pagos
- Agregar cargos extra

**Flujo 4: Proceso de Check-in**
- Búsqueda de reserva
- Verificación de pago
- Asignación de habitaciones
- Cambio a estado "checked_in"

---

### 4. Navegación del Sitio Público
**Archivo:** `navegacion-sitio-publico.puml`

Diagrama de navegación detallado del sitio web público que muestra:
- Estructura de páginas
- Componentes de cada página
- Flujo de navegación entre páginas
- Enlaces del header y footer
- Leyenda con códigos de colores por tipo de página

**Secciones principales:**
- 🏠 Página Principal
- 📋 Habitaciones
- 🔍 Búsqueda y Reserva
- 🔐 Autenticación
- 👤 Área de Cliente
- ℹ️ Información

---

### 5. Navegación del Panel Administrativo
**Archivo:** `navegacion-panel-admin.puml`

Diagrama completo de navegación del panel Filament que incluye:
- Estructura de módulos
- Recursos CRUD de cada módulo
- Acciones disponibles
- Permisos requeridos
- Navegación entre módulos relacionados

**Módulos:**
- 📊 Dashboard
- 🏨 Gestión de Habitaciones
- 📅 Gestión de Reservas
- 💳 Gestión de Pagos
- 👥 Gestión de Clientes
- ⚙️ Configuración
- 🔐 Sistema

---

## 🔧 Cómo Visualizar los Diagramas

### Opción 1: PlantUML Web Server (Más Rápido)
1. Ve a: http://www.plantuml.com/plantuml/uml/
2. Copia y pega el contenido de cualquier archivo `.puml`
3. Click en "Submit"

### Opción 2: VS Code (Recomendado para Desarrollo)
1. Instala la extensión "PlantUML" en VS Code
2. Abre cualquier archivo `.puml`
3. Presiona `Alt+D` para ver el preview
4. O click derecho → "Preview Current Diagram"

**Extensión recomendada:** `jebbs.plantuml`

### Opción 3: Generar Imágenes Localmente

#### Instalar PlantUML:

**Linux:**
```bash
sudo apt install plantuml
# o
sudo apt install default-jre
wget https://github.com/plantuml/plantuml/releases/download/v1.2023.13/plantuml-1.2023.13.jar
```

**macOS:**
```bash
brew install plantuml
```

**Windows:**
```bash
choco install plantuml
```

#### Generar imágenes:

**Generar PNG:**
```bash
plantuml docs/*.puml
```

**Generar SVG (mejor calidad):**
```bash
plantuml -tsvg docs/*.puml
```

**Generar un archivo específico:**
```bash
plantuml docs/diagrama-clases-uml.puml
```

Las imágenes se generarán en la misma carpeta que los archivos `.puml`.

---

## 📊 Exportar para Documentación

### Para Word/PDF:
1. Generar SVG: `plantuml -tsvg docs/*.puml`
2. Importar los archivos SVG en tu documento

### Para LaTeX:
```latex
\begin{figure}[h]
  \centering
  \includegraphics[width=\textwidth]{docs/diagrama-clases-uml.png}
  \caption{Diagrama de Clases UML del Sistema}
\end{figure}
```

### Para Markdown/GitHub:
```markdown
![Diagrama de Clases](diagrama-clases-uml.png)
```

---

## 📝 Notas sobre UML 2.5

Todos los diagramas siguen el estándar **UML 2.5** con las siguientes convenciones:

### Diagrama de Clases:
- `+` = público
- `-` = privado
- `#` = protegido
- `<<PK>>` = Primary Key
- `<<FK>>` = Foreign Key
- `1` -- `*` = relación uno a muchos
- `*` -- `*` = relación muchos a muchos

### Diagramas de Actividad:
- Rectángulos redondeados = actividades
- Diamantes = decisiones
- Círculo negro = inicio
- Círculo negro con borde = fin
- Swimlanes (|Actor|) = separación por actores

### Diagramas de Estado:
- Rectángulos = estados/páginas
- Flechas = transiciones
- Notas = información adicional

---

## 🎨 Códigos de Color

### Diagrama de Clases:
- Estructura estándar UML sin colores específicos

### Mapas de Navegación:
- 🔵 Azul (`#E3F2FD`) = Sitio Público
- 🟠 Naranja (`#FFF3E0`) = Panel Admin
- 🟣 Púrpura (`#F3E5F5`) = Autenticación

### Navegación Sitio Público:
- 🟢 Verde = Página Principal
- 🔵 Azul = Páginas Principales
- 🟠 Naranja = Autenticación
- 🟣 Púrpura = Proceso de Reserva
- 🔷 Cyan = Área de Cliente

### Navegación Panel Admin:
- 🟣 Púrpura = Dashboard
- 🔵 Azul = Habitaciones
- 🟢 Verde Azulado = Reservas
- 🟢 Verde = Pagos
- 🟠 Naranja = Clientes
- ⚪ Gris = Configuración
- 🔘 Gris Azulado = Sistema

---

## 🔄 Actualización de Diagramas

Si modificas la estructura de la base de datos o navegación:

1. **Diagrama de Clases:** Actualizar cuando agregues/modifiques tablas o relaciones
2. **Mapa de Sitio:** Actualizar cuando agregues nuevas páginas o módulos
3. **Flujos:** Actualizar cuando cambien procesos de negocio
4. **Navegación:** Actualizar cuando cambies rutas o estructura de menús

Los archivos `.puml` son texto plano y fáciles de versionar con Git.

---

## 📚 Referencias

- **PlantUML:** https://plantuml.com/
- **UML 2.5 Spec:** https://www.omg.org/spec/UML/2.5/
- **Filament PHP:** https://filamentphp.com/docs
- **Laravel:** https://laravel.com/docs

---

## ✅ Checklist de Documentación

- [x] Diagrama de Clases UML
- [x] Mapa de Sitio Completo
- [x] Flujos de Usuario Principales
- [x] Navegación Sitio Público
- [x] Navegación Panel Admin
- [ ] Manual de Usuario (pendiente)
- [ ] Manual de Instalación (pendiente)
- [ ] Documentación de API (pendiente)
- [ ] Guía de Despliegue (pendiente)

---

**Generado:** 2025-11-27
**Sistema:** Gestión Hotelera - Laravel + Filament
**Versión:** 1.0
