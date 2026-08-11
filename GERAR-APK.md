# 🚀 Como gerar o APK do Rio Park

## Método 1: GitHub Actions (Recomendado - Nuvem) ⭐

1. Sobe o código pro GitHub
2. O APK é gerado automaticamente na nuvem
3. Baixa o APK pronto

### Passos:
```bash
git add .
git commit -m "Configuração Android"
git push
```

Depois vai em: **Actions → Build Android APK → Download artifact**

---

## Método 2: Instalar Android SDK (Local - Pesado)

### Requisitos:
- 3-5GB de download
- 8GB de espaço em disco
- 4-6GB de RAM durante build

### Instalar:
1. Baixa Android Studio: https://developer.android.com/studio
2. Instala só o SDK (sem abrir o Studio)
3. Configura ANDROID_HOME
4. Roda: `gradlew.bat assembleDebug`

---

## Método 3: AppCenter / Codemagic (Build Online)

Serviços que compilam APK na nuvem gratuitamente.

---

## 📱 Status do Projeto:

✅ Código Kotlin pronto  
✅ Gradle configurado  
✅ Java JDK instalado  
❌ Android SDK faltando (pesado pra sua máquina)

**Recomendo usar GitHub Actions!** 🎯
