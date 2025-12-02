# ERP de Estoque

Sistema de estoque com:
- **Produtos** (cadastro, preço de venda, custo médio, estoque)
- **Compras** (entrada de estoque + recalculo de custo médio)
- **Vendas** (baixa de estoque + cálculo de lucro e cancelamento de venda)

---

## Como rodar o projeto

### 1. Configurar .env do backend

```bash
cd backend
cp .env.example .env
```

No .env (exemplo mínimo):

```bash
APP_ENV=local
APP_KEY=base64:GERADA_PELO_ARTISAN
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=db 
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=root
```

Gerar a APP_KEY:
```bash
docker-compose run --rm backend php artisan key:generate
```

### 2. Subir os containers 

Na raiz do projeto (onde está o docker-compose.yml):

```bash
docker-compose up -d --build
```

### 3. Rodar migrações
```bash
docker-compose exec backend php artisan migrate
```

---

### Acessos

Frontend: http://localhost:5173

Backend: http://localhost:8000
