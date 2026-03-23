<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

<h1 align="center">FamilyIntegral API</h1>

<p align="center">
  API REST para gestión de salud familiar desarrollada con Laravel 11 + PHP 8.2 + MySQL 8.0
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-red?style=flat-square&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-orange?style=flat-square&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/Docker-ready-blue?style=flat-square&logo=docker" alt="Docker">
  <img src="https://img.shields.io/badge/PHPStan-level%203-brightgreen?style=flat-square" alt="PHPStan">
  <img src="https://img.shields.io/badge/tests-5%20passed-brightgreen?style=flat-square" alt="Tests">
</p>

---

## 📋 Descripción

FamilyIntegral es una API REST para la gestión de salud familiar que permite:

- Gestión de medicamentos con recordatorios automáticos
- Cálculo y seguimiento del IMC
- Tests de salud (estrés, depresión, apnea del sueño, podómetro)
- Gestión de miembros familiares
- Chats con profesionales de salud
- Visitas domiciliarias
- Calendario del ciclo menstrual
- Integración con Google Calendar
- Consultas a OpenFDA

---

## 🛠️ Stack Tecnológico

| Tecnología | Versión | Uso |
|---|---|---|
| Laravel | 11.x | Framework backend |
| PHP | 8.2 | Lenguaje del servidor |
| MySQL | 8.0 | Base de datos |
| Docker | Latest | Contenedores |
| Laravel Sanctum | 4.x | Autenticación por tokens |
| Laravel Pint | 1.27.0 | Formatter de código |
| PHPStan | 2.1.x | Análisis estático |
| PHP_CodeSniffer | 4.0.x | Linter PSR-12 |
| Apache JMeter | 5.6.3 | Pruebas de rendimiento |

---

## 🚀 Instalación con Docker

### Requisitos
- Docker Desktop instalado y corriendo
- Git

### Pasos

**1. Clonar el repositorio:**
```bash
git clone https://github.com/tu-usuario/family-integral-back.git
cd family-integral-back
```

**2. Configurar el ambiente Docker:**
```bash
Copy-Item .env.docker .env
```

**3. Levantar los contenedores:**
```bash
docker compose up -d
```

**4. Correr migraciones y seeders:**
```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed --class=RoleSeeder
```

**5. Verificar que todo funciona:**
```bash
curl http://localhost:8000/api/test
```

### Servicios disponibles

| Servicio | URL |
|---|---|
| API REST | http://localhost:8000/api |
| phpMyAdmin | http://localhost:8080 |

> **Usuario MySQL:** root | **Contraseña:** (vacía)

---

## 🌐 Endpoints Principales

| Método | Endpoint | Descripción |
|---|---|---|
| POST | `/api/auth/register` | Registro de usuario |
| POST | `/api/auth/login` | Inicio de sesión |
| GET | `/api/auth/me` | Perfil del usuario |
| POST | `/api/auth/logout` | Cerrar sesión |
| GET | `/api/medicamentos` | Listar medicamentos |
| POST | `/api/medicamentos` | Crear medicamento |
| GET | `/api/recordatorios/hoy` | Recordatorios del día |
| POST | `/api/imc` | Registrar IMC |
| POST | `/api/tests-salud` | Realizar test de salud |
| GET | `/api/visitas-domiciliarias` | Listar visitas |
| GET | `/api/openfda/medicamento?nombre=` | Buscar medicamento |

Ver todas las rutas:
```bash
docker compose exec app php artisan route:list
```

---

## 🧪 Pruebas

### Pruebas Unitarias (PHPUnit)
```bash
docker compose exec -e DB_DATABASE=family_integral_test app php artisan test --filter AuthTest
```

Resultado esperado:
```
✓ registro exitoso de usuario
✓ login con credenciales validas
✓ login con password incorrecto
✓ registro con email duplicado
✓ login con membresia inactiva

Tests: 5 passed (14 assertions)
```

### Pruebas de Rendimiento (JMeter)
```bash
# Limpiar resultados anteriores
Remove-Item -Recurse -Force jmeter\results\report
Remove-Item -Force jmeter\results\results.jtl

# Correr pruebas
docker run --rm --network family-integral-back_family_network -v ${PWD}/jmeter:/jmeter justb4/jmeter -n -t /jmeter/plan.jmx -l /jmeter/results/results.jtl -e -o /jmeter/results/report
```

Resultados obtenidos:
```
Peticiones: 5 | Promedio: 65ms | Mínimo: 49ms | Máximo: 124ms | Errores: 0%
```

El reporte HTML se genera en `jmeter/results/report/index.html`.

---

## ✅ Calidad de Código

### Laravel Pint (Formatter)
```bash
docker compose exec app ./vendor/bin/pint
```

### PHP_CodeSniffer (Linter PSR-12)
```bash
docker compose exec app ./vendor/bin/phpcs
```

### PHPStan (Análisis estático nivel 3)
```bash
docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=512M
```

---

## 🔄 Cambiar entre ambientes

### Docker → XAMPP
```bash
docker compose down
Copy-Item .env.xampp .env
# Iniciar XAMPP Apache + MySQL
```

### XAMPP → Docker
```bash
Copy-Item .env.docker .env
docker compose up -d
```

> ⚠️ Nunca correr XAMPP y Docker simultáneamente (conflicto puerto 3306)

---

## 📁 Estructura del Proyecto
```
family-integral-back/
├── app/Http/Controllers/Api/   # 11 controladores
├── app/Models/                 # 13 modelos
├── database/migrations/        # 15 migraciones
├── database/seeders/           # RoleSeeder
├── jmeter/                     # Plan de pruebas JMeter
│   ├── plan.jmx
│   └── results/
├── tests/Feature/              # Pruebas unitarias
│   └── AuthTest.php
├── .env.docker                 # Config Docker
├── .env.xampp                  # Config XAMPP
├── Dockerfile
├── docker-compose.yml
├── docker-entrypoint.sh
├── pint.json                   # Config Laravel Pint
├── phpstan.neon                # Config PHPStan
└── phpcs.xml                   # Config CodeSniffer
```

---

## 📄 Licencia

Este proyecto es desarrollado como parte del proyecto integrador de la carrera de Ingeniería en Desarrollo y Gestión de Software — UTRM / BIS Universities.