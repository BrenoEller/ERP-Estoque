FROM node:22-alpine

WORKDIR /app

# Copia apenas os manifests pra aproveitar cache
COPY package*.json ./

RUN npm install

# Copia o resto do código do frontend
COPY . .

EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173"]
