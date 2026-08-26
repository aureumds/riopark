package com.riopark.operator

import android.app.Activity
import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.webkit.JavascriptInterface
import android.webkit.WebChromeClient
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity

class MainActivity : AppCompatActivity() {

    private lateinit var webView: WebView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        webView = WebView(this)
        setContentView(webView)

        val prefs = getSharedPreferences("riopark", Context.MODE_PRIVATE)
        var baseUrl = prefs.getString("base_url", null)

        if (baseUrl.isNullOrBlank()) {
            startActivity(Intent(this, SettingsActivity::class.java))
            finish()
            return
        }

        // Always use HTTPS to avoid cleartext blocks (targetSdk 34) and mixed redirects.
        baseUrl = baseUrl.trimEnd('/')
        if (baseUrl.startsWith("http://")) {
            baseUrl = "https://" + baseUrl.removePrefix("http://")
            prefs.edit().putString("base_url", baseUrl).apply()
        }

        setupWebView(baseUrl)
        webView.loadUrl("$baseUrl/operador-lite")
    }

    private fun setupWebView(baseUrl: String) {
        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            databaseEnabled = true
            cacheMode = WebSettings.LOAD_DEFAULT
            mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW
        }

        webView.webViewClient = object : WebViewClient() {
            override fun shouldOverrideUrlLoading(view: WebView?, url: String?): Boolean {
                if (url.isNullOrBlank()) return false
                // Keep navigation inside WebView; upgrade accidental http redirects.
                val safe = if (url.startsWith("http://")) {
                    "https://" + url.removePrefix("http://")
                } else {
                    url
                }
                view?.loadUrl(safe)
                return true
            }
        }
        webView.webChromeClient = WebChromeClient()
        webView.addJavascriptInterface(RioParkBridge(this), "RioParkBridge")
    }

    @Deprecated("Deprecated in Java")
    override fun onBackPressed() {
        if (webView.canGoBack()) {
            webView.goBack()
        }
    }
}

class RioParkBridge(private val activity: Activity) {

    private val mainHandler = Handler(Looper.getMainLooper())

    @JavascriptInterface
    fun printTicket(text: String) {
        // JavascriptInterface runs off the UI thread — must hop to main or the app crashes.
        mainHandler.post {
            try {
                PrintHelper.print(activity, text)
                Toast.makeText(activity, "Ticket enviado para impressão", Toast.LENGTH_SHORT).show()
            } catch (e: Exception) {
                Toast.makeText(activity, "Impressão indisponível neste aparelho", Toast.LENGTH_SHORT).show()
            }
        }
    }

    @JavascriptInterface
    fun getBaseUrl(): String {
        val prefs = activity.getSharedPreferences("riopark", Context.MODE_PRIVATE)
        return prefs.getString("base_url", "") ?: ""
    }
}
