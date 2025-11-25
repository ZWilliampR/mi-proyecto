# Imagen base de Node
FROM node:18

# Carpeta de trabajo dentro del contenedor
WORKDIR /app

# Copia el package.json y package-lock.json
COPY package*.json ./

# Instala dependencias
RUN npm install

# Copia el resto del proyecto
COPY . .

# Expone el puerto (cambia 3000 si usas otro)
EXPOSE 3000

# Comando para arrancar tu API
CMD ["npm", "start"]