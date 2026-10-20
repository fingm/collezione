#!/usr/bin/env bash

# Ir al directorio del proyecto
cd /srv/http/collezione || exit

# 1. Iniciar servidor de Laravel expuesto a la red local
php artisan serve --host=0.0.0.0 --port=8000 &

# 2. Iniciar Vite expuesto a la red local
npm run dev -- --host 0.0.0.0 &

# 3. Esperar a que levanten los servicios
sleep 2
xdg-open http://localhost:8000

wait