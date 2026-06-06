SIGADEA listo para Render.

En Render crea un Web Service conectado a GitHub.
Build Command:
composer install --no-dev --optimize-autoloader

Start Command:
php -S 0.0.0.0:$PORT -t .

Variables de entorno recomendadas:
DB_HOST=zephyr.proxy.rlwy.net
DB_PORT=11902
DB_NAME=railway
DB_USER=root
DB_PASS=TU_PASSWORD_DE_RAILWAY
