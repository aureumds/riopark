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
    private val mainHandler = Handler(Looper.getMainLooper())

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
        url = when {
            url.startsWith("https://") -> url
            url.startsWith("http://")  -> "https://" + url.removePrefix("http://")
            else                       -> "https://$url"
        }
        prefs.edit().putString("base_url", url).apply()
        baseUrl = url

        setupWebView()

        // Load from cache when offline to avoid error on startup.
        if (!isNetworkAvailable()) {
            webView.settings.cacheMode = WebSettings.LOAD_CACHE_ONLY
        }

        webView.loadUrl("$baseUrl/operador-lite")
    }

    private fun setupWebView() {
        CookieManager.getInstance().apply {
            setAcceptCookie(true)
            setAcceptThirdPartyCookies(webView, true)
            flush()
        }

        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            databaseEnabled = true
            cacheMode = WebSettings.LOAD_CACHE_ELSE_NETWORK
            mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW
        }

        webView.webViewClient = object : WebViewClient() {

            override fun onPageFinished(view: WebView?, url: String?) {
                offlineRetry = false
                view?.settings?.cacheMode = WebSettings.LOAD_CACHE_ELSE_NETWORK
                CookieManager.getInstance().flush()
            }

            /**
             * Android 6 calls this deprecated callback for ALL resource errors.
             * We only react when the main document fails.
             */
            @Suppress("DEPRECATION", "OverridingDeprecatedMember")
            override fun onReceivedError(
                view: WebView?,
                errorCode: Int,
                description: String?,
                failingUrl: String?
            ) {
                if (failingUrl.isNullOrBlank()) return

                val pageUrl = view?.url ?: ""
                val isMainFrame = pageUrl.isEmpty()
                    || failingUrl == pageUrl
                    || pageUrl.startsWith(failingUrl.substringBefore("?"))

                if (!isMainFrame) return

                handleLoadError(view, failingUrl)
            }

            /**
             * New API (API 21+) — called on Android 6+.
             * NEVER intercept POST requests (form submissions): that would convert
             * the POST to a GET and break CSRF / login flow.
             * For GET http:// URLs: schedule the https:// reload via Handler to
             * avoid the Android 6 WebView bug where calling loadUrl() directly
             * inside shouldOverrideUrlLoading() silently drops the navigation.
             */
            override fun shouldOverrideUrlLoading(
                view: WebView?,
                request: WebResourceRequest?
            ): Boolean {
                val url = request?.url?.toString() ?: return false

                // Never intercept POST (form submissions, login, etc.).
                if (request.method?.uppercase() == "POST") return false

                // Only rewrite insecure http:// GET navigations.
                if (!url.startsWith("http://")) return false

                val httpsUrl = "https://" + url.removePrefix("http://")
                mainHandler.post { view?.loadUrl(httpsUrl) }
                return true
            }

            /**
             * Deprecated API — called on very old WebView builds.
             * Cannot detect POST here, so never override; let WebView handle
             * all navigations natively to avoid breaking form submissions.
             */
            @Suppress("DEPRECATION", "OverridingDeprecatedMember")
            override fun shouldOverrideUrlLoading(view: WebView?, url: String?): Boolean {
                return false
            }
        }

        webView.webChromeClient = WebChromeClient()
        webView.addJavascriptInterface(RioParkBridge(this), "RioParkBridge")
    }

    private fun handleLoadError(view: WebView?, failingUrl: String?) {
        if (offlineRetry) {
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
        return try {
            val cm = getSystemService(Context.CONNECTIVITY_SERVICE) as? ConnectivityManager
            @Suppress("DEPRECATION")
            cm?.activeNetworkInfo?.isConnected == true
        } catch (e: Exception) {
            true
        }
    }

    override fun onPause() {
        super.onPause()
        CookieManager.getInstance().flush()
    }

    override fun onStop() {
        super.onStop()
        CookieManager.getInstance().flush()
    }

    @Suppress("DEPRECATION", "OverridingDeprecatedMember")
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

    @JavascriptInterface
    fun getServerUrl(): String {
        val prefs = activity.getSharedPreferences("riopark", Context.MODE_PRIVATE)
        return (prefs.getString("base_url", "") ?: "").trimEnd('/')
    }
}
