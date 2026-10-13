#!/bin/bash

# Ruta a la carpeta de tu proyecto Laravel
PROJECT_DIR="/srv/http/collezione/"

cd "$PROJECT_DIR" || exit

# Iniciar php artisan serve en segundo plano
php artisan serve &
PID_PHP=$!

# Iniciar npm run dev en segundo plano
npm run dev &
PID_NPM=$!

# Esperar 2 segundos a que los servidores levanten
sleep 2

# Abrir el sitio en el navegador predeterminado
xdg-open http://127.0.0.1:8000

# Mantener el script corriendo para que los procesos de fondo sigan vivos
wait $PID_PHP $PID_NPM