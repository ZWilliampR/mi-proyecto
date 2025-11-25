// server.js (versión ampliada: persistencia simulada + cifrado + prueba SQL simulada)
const express = require("express");
const fs = require("fs");
const crypto = require("crypto");
const app = express();

app.use(express.json());
app.use((req, res, next) => {
  console.log(`[${new Date().toISOString()}] ${req.method} ${req.url}`);
  next();
});

// Configuración simple de cifrado simulado (AES-256-GCM)
const ALGO = "aes-256-gcm";
const SECRET_KEY = crypto.scryptSync("mi_clave_secreta_demo_2025", "salt", 32); // usar KMS en producción
const IV_LENGTH = 12;

function encrypt(text) {
  const iv = crypto.randomBytes(IV_LENGTH);
  const cipher = crypto.createCipheriv(ALGO, SECRET_KEY, iv);
  const encrypted = Buffer.concat([cipher.update(text, "utf8"), cipher.final()]);
  const tag = cipher.getAuthTag();
  // retornamos iv + tag + data en base64 para almacenar
  return `${iv.toString("base64")}:${tag.toString("base64")}:${encrypted.toString("base64")}`;
}

function decrypt(enc) {
  const [ivB64, tagB64, dataB64] = enc.split(":");
  const iv = Buffer.from(ivB64, "base64");
  const tag = Buffer.from(tagB64, "base64");
  const data = Buffer.from(dataB64, "base64");
  const decipher = crypto.createDecipheriv(ALGO, SECRET_KEY, iv);
  decipher.setAuthTag(tag);
  const decrypted = Buffer.concat([decipher.update(data), decipher.final()]);
  return decrypted.toString("utf8");
}

// helper para "persistir" en archivo JSON
function saveRecord(record) {
  const file = "data.json";
  let db = [];
  if (fs.existsSync(file)) {
    try {
      db = JSON.parse(fs.readFileSync(file, "utf8"));
    } catch (e) {
      db = [];
    }
  }
  db.push(record);
  fs.writeFileSync(file, JSON.stringify(db, null, 2));
}

// endpoint principal: recibe pasos y persiste (con cifrado parcial)
app.post("/api/steps", (req, res) => {
  const data = req.body;

  // Validación básica
  if (!data || Object.keys(data).length === 0 || Object.values(data).every(v => v === null || v === "")) {
    return res.status(400).json({ error: "Solicitud inválida. Se requieren datos en formato JSON." });
  }
  if (!data.nombre || !data.descripcion) {
    return res.status(400).json({ error: "Datos incompletos. Se requieren 'nombre' y 'descripcion'." });
  }

  // Cifrar campo sensible (descripcion) antes de almacenar
  const descripcionCifrada = encrypt(data.descripcion.toString());

  // Simular id generado por BD
  const generatedId = Date.now(); // simple id basado en timestamp

  const record = {
    id: generatedId,
    nombre: data.nombre,
    descripcion: descripcionCifrada, // guardamos la versión cifrada
    reciboEn: new Date().toISOString()
  };

  // Persistir en archivo local (simula base de datos cifrada)
  saveRecord(record);

  console.log("Registro persistido (cifrado):", { id: record.id, nombre: record.nombre });

  // Responder con id (sin exponer datos cifrados)
  res.json({ message: "Datos recibidos y almacenados (simulado)", id: generatedId });
});

// Endpoint de prueba para mostrar cómo se evitaría inyección SQL (simulado)
app.post("/api/login-sim", (req, res) => {
  const { username, password } = req.body || {};

  if (!username || !password) {
    return res.status(400).json({ error: "Campos 'username' y 'password' requeridos." });
  }

  // Simulación de verificación segura: NUNCA concatenar strings; usar parámetros.
  // Aquí simplemente mostramos que el input fue recibido y que la rutina "filtra" inputs peligrosos.
  const suspiciousPattern = /('|--|;|\bOR\b|\bAND\b)/i;
  if (suspiciousPattern.test(username) || suspiciousPattern.test(password)) {
    // Respuesta segura: no exponemos datos, solo indicamos fallo de autenticación
    return res.status(401).json({ error: "Credenciales inválidas o formato no permitido." });
  }

  // Simular usuario válido
  return res.json({ message: "Usuario validado (simulado)", user: username });
});

// GET para verificar servidor
app.get("/", (req, res) => {
  res.json({ message: "API simulada FamilyIntegral (en línea)" });
});

app.use((err, req, res, next) => {
  console.error("Error interno:", err.message);
  res.status(500).json({ error: "Error interno del servidor" });
});

const PORT = 3000;
app.listen(PORT, () => console.log(`Servidor corriendo en http://localhost:${PORT}`));