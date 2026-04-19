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
- Tests de salud
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
| Laravel | 11.x | Backend |
| PHP | 8.2 | Lenguaje |
| MySQL | 8.0 | Base de datos |
| Docker | Latest | Contenedores |
| GitHub Actions | Latest | CI/CD |
| AWS EC2 | - | Despliegue |
| Postman | - | Pruebas |

---

## 🌍 Ambientes

| Ambiente | Propósito | URL |
|---|---|---|
| Desarrollo | Local | http://localhost:8000 |
| QA | Docker | Local |
| Producción | AWS EC2 | http://IP_PUBLICA/api |

---

## 🚀 Instalación con Docker

### Requisitos
- Docker Desktop
- Git

### Pasos

- git clone https://github.com/tu-usuario/family-integral-back.git
- cd family-integral-back
- cp .env.docker .env
- docker compose up -d
- docker compose exec app php artisan migrate


##    🔄 Pipeline CI/CD

Se implementó integración continua con GitHub Actions.

Etapas:
Checkout del código
Instalación de dependencias
Configuración del entorno
Migraciones
Ejecución de pruebas
Triggers:
push a main y develop
pull requests

Archivo:

.github/workflows/ci.yml

##    🚀 Despliegue en AWS

El proyecto está desplegado en una instancia EC2 con Ubuntu.

Proceso:
ssh ubuntu@IP_PUBLICA
cd family-integral-back
docker compose up -d
docker ps

##    🌐 Verificación

Abrir en navegador:

http://IP_PUBLICA/api/test

##    🧪 Pruebas
Pruebas unitarias
php artisan test
Pruebas manuales

Se validaron endpoints con Postman:

/api/auth/register
/api/auth/login
/api/test
⚠️ Nota sobre pruebas

Algunas pruebas pueden fallar en CI debido a diferencias entre MySQL (local) y SQLite (pipeline).

##    📊 Monitoreo
Logs en storage/logs
Endpoint de salud:
GET /api/test

##    🔄 Estrategia de Despliegue

Recreate Deployment

Se detiene la versión actual
Se levanta una nueva con Docker

##    📁 Estructura del Proyecto
- .github/workflows/
- app/
- tests/
- docker-compose.yml
- .env.example
- README.md

##    🧠 Conclusión

El proyecto implementa:

- CI/CD con GitHub Actions
- Despliegue en AWS
- Contenedores Docker
- Pruebas automatizadas y manuales
- Backend funcional


##    📄 Licencia

Proyecto académico — Ingeniería en Desarrollo y Gestión de Software.
