# 🚀 Como gerar o APK do Rio Park

## Passos rápidos:

### 1. Instalar JDK 17
1. Baixe: https://adoptium.net/temurin/releases/?version=17
2. Escolha: Windows x64 JDK (.msi)
3. Instale (deixe as opções padrão marcadas)
4. Reinicie o terminal/Git Bash

### 2. Gerar o APK
```bash
cd C:\xampp\htdocs\rio-park\android
gradlew.bat assembleDebug
```

### 3. Pegar o APK
O APK estará em:
```
C:\xampp\htdocs\rio-park\android\app\build\outputs\apk\debug\app-debug.apk
```

## ✅ Gradle Wrapper já está configurado!
Você já tem:
- ✅ gradlew.bat
- ✅ gradle-wrapper.jar
- ✅ gradle-wrapper.properties

Só falta o Java! 🎯
