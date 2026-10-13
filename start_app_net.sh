#!/usr/bin/env bash

# Ir al directorio del proyecto
cd /srv/http/collezione || exit

# 1. Iniciar servidor de Laravel (disponible para la red local)
php artisan serve --host=0.0.0.0 --port=8000 &

# 2. Iniciar servidor de Vite
npm run dev &

# 3. Esperar 2 segundos para que los servidores levanten y abrir el navegador
sleep 2
xdg-open http://localhost:8000

# Mantiene el script activo para que los procesos en segundo plano no mueran inmediatamente
wait