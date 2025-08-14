# Guide Instruction
##  Setup with Docker
### In root project
``cd docker``  
``docker compose up --build -d``
``docker ps`` get "backend_container_name" and "frontend_container_name"

#### setup for backend
``docker exec -it backend_container_name bash``  
``cp .env.example .env``  
``composer install``  
``php artisan migrate``

#### setup for frontend

#### mount node_modules on local
``docker run --rm -v "$(pwd)/../fe-s-clinic:/app" -w /app node:22-alpine npm install``
