# Deploy Operador Lite (FTP + SSH)

## Problema comum: 404 em `/operador-lite`

O domínio `https://app.mlvideos.com.br` redireciona para `/login` (painel admin) — isso é normal.

A maquininha / APK usa **`/operador-lite`**. Se essa rota der **404**, os arquivos do Lite não estão no servidor ou o cache de rotas está antigo.

## 1. Conferir no servidor (SSH)

```bash
cd /var/www/html/rio

echo "=== Controllers ==="
ls -la app/Http/Controllers/Operator/Lite/

echo "=== Middleware ==="
ls -la app/Http/Middleware/EnsureOperatorLicense.php

echo "=== Views ==="
ls -la resources/views/operator-lite/

echo "=== CSS/JS ==="
ls -la public/css/operator-lite.css
ls -la public/js/operator-lite/

echo "=== Rotas no arquivo ==="
grep -n "operador-lite" routes/web.php | head

echo "=== Middleware no bootstrap ==="
grep -n "operator.license" bootstrap/app.php
```

Se algum `ls` falhar → **suba de novo via FileZilla** o arquivo/pasta que falta.

## 2. Arquivos obrigatórios (FileZilla → `/var/www/html/rio/`)

```
app/Http/Controllers/Operator/Lite/AuthController.php
app/Http/Controllers/Operator/Lite/DashboardController.php
app/Http/Controllers/Operator/Lite/LicenseController.php
app/Http/Controllers/Operator/Lite/SessionController.php
app/Http/Controllers/Operator/Lite/ShiftController.php
app/Http/Controllers/Operator/Lite/SyncController.php
app/Http/Controllers/Operator/Lite/Concerns/ActivatesOperatorDevice.php
app/Http/Middleware/EnsureOperatorLicense.php
bootstrap/app.php
routes/web.php
resources/views/operator-lite/   (pasta inteira: 9 blades)
public/css/operator-lite.css
public/js/operator-lite/         (store.js, sync.js, plate-keyboard.js, offline-forms.js)
```

**Não** enviar: `vendor/`, `storage/`, `tests/`, `node_modules/`.

## 3. Limpar cache e registrar rotas (SSH)

```bash
cd /var/www/html/rio

rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
rm -f bootstrap/cache/routes.php

php artisan route:clear
php artisan view:clear
php artisan config:clear

php artisan route:cache
php artisan view:cache
php artisan config:cache

php artisan route:list --path=operador-lite
```

A lista **deve** mostrar `operador-lite/login`, `operador-lite/entrada`, etc.

## 4. Testar

```bash
curl -I https://app.mlvideos.com.br/operador-lite/login
```

Esperado: **HTTP 200** (não 404).

No navegador da maquininha:

- Admin/painel: `https://app.mlvideos.com.br/login`
- Operador POS: `https://app.mlvideos.com.br/operador-lite/login`

No APK: URL base `https://app.mlvideos.com.br` (o app abre `/operador-lite` sozinho).

## 5. Permissões (se FileZilla falhar de novo)

```bash
sudo chown -R andre:www-data /var/www/html/rio
sudo find /var/www/html/rio -type d -exec chmod 775 {} \;
sudo find /var/www/html/rio -type f -exec chmod 664 {} \;
sudo chmod -R ug+rwx storage bootstrap/cache
```
