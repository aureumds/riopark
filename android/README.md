# Rio Park Operator APK

WebView genérico para maquininhas Android.

## Build

Abra o projeto `android/` no Android Studio ou:

```bash
cd android
./gradlew assembleDebug
```

## Primeira execução

Configure a URL base do servidor (ex: `http://192.168.0.10` ou IP da máquina com Laravel).

## Bridge JavaScript

O WebView expõe `window.RioParkBridge.printTicket(text)` para impressão de tickets.
