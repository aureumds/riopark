package com.riopark.operator

import android.content.Context
import android.content.Intent
import android.os.Bundle
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
        val baseUrl = prefs.getString("base_url", null)

        if (baseUrl.isNullOrBlank()) {
            startActivity(Intent(this, SettingsActivity::class.java))
            finish()
            return
        }

        setupWebView(baseUrl)
        webView.loadUrl("$baseUrl/operador")
    }

    private fun setupWebView(baseUrl: String) {
        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            databaseEnabled = true
            cacheMode = WebSettings.LOAD_DEFAULT
            mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW
        }

        webView.webViewClient = WebViewClient()
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

class RioParkBridge(private val context: Context) {

    @JavascriptInterface
    fun printTicket(text: String) {
        PrintHelper.print(context, text)
        Toast.makeText(context, "Ticket enviado para impressão", Toast.LENGTH_SHORT).show()
    }

    @JavascriptInterface
    fun getBaseUrl(): String {
        val prefs = context.getSharedPreferences("riopark", Context.MODE_PRIVATE)
        return prefs.getString("base_url", "") ?: ""
    }
}
