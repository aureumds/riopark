package com.riopark.operator

import android.app.Activity
import android.content.Context
import android.content.Intent
import android.net.ConnectivityManager
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.webkit.CookieManager
import android.webkit.JavascriptInterface
import android.webkit.WebChromeClient
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity

class MainActivity : AppCompatActivity() {

    private lateinit var webView: WebView
    private var baseUrl: String = ""

    // Prevents an infinite reload loop when both network AND cache fail.
    private var offlineRetry = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        webView = WebView(this)
        setContentView(webView)

        val prefs = getSharedPreferences("riopark", Context.MODE_PRIVATE)
        var url = prefs.getString("base_url", null)

        if (url.isNullOrBlank()) {
            startActivity(Intent(this, SettingsActivity::class.java))
            finish()
            return
        }

        // Always use HTTPS.
        url = url.trimEnd('/')
        if (url.startsWith("http://")) {
            url = "https://" + url.removePrefix("http://")
            prefs.edit().putString("base_url", url).apply()
        }
        baseUrl = url

        setupWebView()

        // Load from cache when offline to avoid "ERR_INTERNET_DISCONNECTED" on startup.
        if (!isNetworkAvailable()) {
            webView.settings.cacheMode = WebSettings.LOAD_CACHE_ONLY
        }

        webView.loadUrl("$baseUrl/operador-lite")
    }

    private fun setupWebView() {
        // Accept and persist cookies across sessions.
        CookieManager.getInstance().apply {
            setAcceptCookie(true)
            setAcceptThirdPartyCookies(webView, true)
            flush()
        }

        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            databaseEnabled = true
            // Use cache when available; fall back to network when stale.
            cacheMode = WebSettings.LOAD_CACHE_ELSE_NETWORK
            mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW
        }

        webView.webViewClient = object : WebViewClient() {

            override fun onPageFinished(view: WebView?, url: String?) {
                // Reset offline retry flag and persist cookies after every successful load.
                offlineRetry = false
                view?.settings?.cacheMode = WebSettings.LOAD_CACHE_ELSE_NETWORK
                CookieManager.getInstance().flush()
            }

            override fun onReceivedError(
                view: WebView?,
                errorCode: Int,
                description: String?,
                failingUrl: String?
            ) {
                handleLoadError(view, failingUrl)
            }

            override fun shouldOverrideUrlLoading(
                view: WebView?,
                request: WebResourceRequest?
            ): Boolean {
                val url = request?.url?.toString() ?: return false
                return rewriteHttpToHttps(view, url)
            }

            @Deprecated("Deprecated in Java")
            override fun shouldOverrideUrlLoading(view: WebView?, url: String?): Boolean {
                if (url.isNullOrBlank()) return false
                return rewriteHttpToHttps(view, url)
            }

            private fun rewriteHttpToHttps(view: WebView?, url: String): Boolean {
                if (!url.startsWith("http://")) return false
                view?.loadUrl("https://" + url.removePrefix("http://"))
                return true
            }
        }

        webView.webChromeClient = WebChromeClient()
        webView.addJavascriptInterface(RioParkBridge(this), "RioParkBridge")
    }

    /**
     * Called when a page load fails (no network or DNS error).
     * Strategy:
     *   1st failure → switch to LOAD_CACHE_ONLY and reload (uses cached pages).
     *   2nd failure → cache is empty too; load the bundled offline fallback from assets.
     */
    private fun handleLoadError(view: WebView?, failingUrl: String?) {
        if (offlineRetry) {
            // Cache was empty too — load bundled offline page.
            offlineRetry = false
            view?.settings?.cacheMode = WebSettings.LOAD_CACHE_ELSE_NETWORK
            view?.loadUrl("file:///android_asset/offline/index.html")
            return
        }

        offlineRetry = true
        view?.settings?.cacheMode = WebSettings.LOAD_CACHE_ONLY
        if (!failingUrl.isNullOrBlank()) {
            view?.loadUrl(failingUrl)
        } else {
            view?.reload()
        }
    }

    private fun isNetworkAvailable(): Boolean {
        val cm = getSystemService(Context.CONNECTIVITY_SERVICE) as? ConnectivityManager
        val info = cm?.activeNetworkInfo
        return info?.isConnected == true
    }

    override fun onPause() {
        super.onPause()
        // Flush cookies to disk so the session survives process death.
        CookieManager.getInstance().flush()
    }

    override fun onStop() {
        super.onStop()
        CookieManager.getInstance().flush()
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

    /** Called from offline.html to get the server base URL so it can redirect when back online. */
    @JavascriptInterface
    fun getServerUrl(): String {
        val prefs = activity.getSharedPreferences("riopark", Context.MODE_PRIVATE)
        return (prefs.getString("base_url", "") ?: "").trimEnd('/')
    }
}
