# Family Integral

![CI](https://img.shields.io/badge/CI-GitHub%20Actions-blue)
![License](https://img.shields.io/badge/license-Academic-green)

Sistema web desarrollado como parte del **Proyecto Integrador** de la asignatura *Gestión del Proceso de Desarrollo de Software*.

El objetivo del proyecto es implementar una aplicación web utilizando buenas prácticas de ingeniería de software como control de versiones, integración continua y despliegue automatizado.

---

# Tabla de Contenidos

* [Descripción](#descripción)
* [Stack Tecnológico](#stack-tecnológico)
* [Requisitos](#requisitos)
* [Instalación](#instalación)
* [Ejecución](#ejecución)
* [Tests](#tests)
* [Estructura del Proyecto](#estructura-del-proyecto)
* [Variables de Entorno](#variables-de-entorno)
* [Flujo de Trabajo Git](#flujo-de-trabajo-git)
* [Contribución](#contribución)
* [Autores](#autores)
* [Licencia](#licencia)

---

# Descripción

**Family Integral** es una aplicación web que busca facilitar la gestión de información familiar mediante una interfaz accesible y moderna.

El proyecto se desarrolla aplicando prácticas profesionales de desarrollo de software como:

* Control de versiones con Git
* Estrategia de ramas basada en Git Flow
* Integración continua con GitHub Actions
* Contenedores con Docker

---

# Stack Tecnológico

Tecnologías utilizadas en el proyecto:

Frontend:

* Vite
* JavaScript
* TailwindCSS

Herramientas de desarrollo:

* Node.js
* Git y GitHub
* Docker
* GitHub Actions

---

# Requisitos

Para ejecutar el proyecto necesitas:

* Node.js 20+
* npm
* Git
* Docker (opcional)

---

# Instalación

Clonar repositorio:

```bash
git clone https://github.com/TU-USUARIO/TU-REPO.git
```

Entrar al proyecto:

```bash
cd mi-proyecto
```

Instalar dependencias:

```bash
npm install
```

---

# Ejecución

Ejecutar en modo desarrollo:

```bash
npm run dev
```

Construir versión de producción:

```bash
npm run build
```

---

# Tests

Ejecutar pruebas:

```bash
npm test
```

Las pruebas también se ejecutan automáticamente mediante **GitHub Actions** en cada Pull Request.

---

# Estructura del Proyecto

```
project/
│
├── .github/
│   ├── workflows/
│   │   └── ci.yml
│   └── PULL_REQUEST_TEMPLATE.md
│
├── src/
│
├── tests/
│
├── .env.example
├── .gitignore
├── package.json
├── README.md
```

---

# Variables de Entorno

Ejemplo de archivo `.env`:

```
PORT=3000
NODE_ENV=development
API_KEY=example_key
```

Usa `.env.example` como plantilla.

---

# Flujo de Trabajo Git

El proyecto utiliza un flujo basado en **Git Flow simplificado**.

Ramas principales:

* `main` → producción
* `develop` → integración
* `feature/*` → desarrollo de nuevas funcionalidades

Flujo de trabajo:

```
feature → develop → main
```

Los cambios se integran mediante **Pull Requests** con validación automática de CI.

---

# Contribución

Proceso para contribuir:

1. Crear rama desde `develop`

```
git checkout develop
git checkout -b feature/nueva-feature
```

2. Hacer commits usando **Conventional Commits**

Ejemplo:

```
feat(auth): agregar login de usuario
```

3. Subir cambios

```
git push origin feature/nueva-feature
```

4. Crear Pull Request en GitHub.

---

# Autores

Proyecto desarrollado para fines académicos por estudiantes de **Ingeniería en Gestión y Desarrollo de Software**.

---

# Licencia

Este proyecto se desarrolla únicamente con fines educativos.
