# Como gerar o APK do Rio Park

## Método 1: GitHub Actions (recomendado)

1. Faça commit e push para `main` no GitHub
2. Acesse **Actions → Build Android APK**
3. Baixe o artifact **`rio-park-apk`** (ZIP com `app-release.apk`)

```bash
git add .
git commit -m "Sua mensagem"
git push origin main
```

### Instalação na maquininha

1. Desinstale o APK antigo (se houver)
2. Instale o `app-release.apk` do artifact (não use APK debug — `testOnly` bloqueia instalação)
3. Na configuração do app, informe apenas a URL base: `https://app.mlvideos.com.br`
4. O APK abre automaticamente **`/operador-lite`** (compatível com Android 6 / WebView antigo)
5. Login com operador cadastrado no painel; super admin deve marcar **pago** em Licenças

**Versão atual do APK:** `1.1.0` (versionCode 3) — Operador Lite

---

## Operador Lite vs Operador Vue

| Rota | Uso |
|------|-----|
| `/operador-lite` | Maquininhas Android 6, APK, WebView antigo |
| `/operador` | PC / navegadores modernos (Vue + Vite) |

O Operador Lite **não precisa** de `npm run build` — CSS/JS estáticos em `public/css` e `public/js/operator-lite/`.

---

## Deploy no servidor

### Opção A — Git (se o servidor tiver repositório)

```bash
cd /var/www/html/rio
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache public/css public/js
```

### Opção B — FTP / FileZilla (deploy manual)

Enviar pastas e arquivos alterados:

- `app/Http/Controllers/Operator/`
- `app/Http/Middleware/EnsureOperatorLicense.php`
- `resources/views/operator-lite/`
- `public/css/operator-lite.css`
- `public/js/operator-lite/`
- `routes/web.php`
- `bootstrap/app.php`

Depois no servidor:

```bash
cd /var/www/html/rio
php artisan migrate --force
php artisan route:cache
php artisan view:cache
php artisan config:cache
```

### Conferir

```bash
curl -I https://app.mlvideos.com.br/operador-lite/login
```

Deve retornar **200** (ou **302** para login se não autenticado).

### Variáveis `.env` (produção)

```env
APP_URL=https://app.mlvideos.com.br
APP_ENV=production
LICENSE_GRACE_DAYS=3
```

---

## Método 2: Build local (Android SDK)

Requisitos: Android Studio / SDK, ~3–5 GB de download.

```bash
cd android
./gradlew assembleRelease
```

APK em `android/app/build/outputs/apk/release/app-release.apk`

---

## Teste na maquininha Android 6

1. No Chrome da maquininha (se disponível): `https://app.mlvideos.com.br/operador-lite/login`
2. Depois teste pelo APK
3. Teste offline: registrar entrada/saída sem internet → reconectar → fila sincroniza
