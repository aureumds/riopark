package com.riopark.operator

import android.content.Context
import android.os.Handler
import android.os.Looper
import android.print.PrintAttributes
import android.print.PrintManager
import android.util.Log
import android.webkit.WebView
import android.webkit.WebViewClient

object PrintHelper {

    private const val TAG = "RioParkPrint"

    fun print(context: Context, text: String) {
        val appContext = context.applicationContext
        Handler(Looper.getMainLooper()).post {
            try {
                val webView = WebView(appContext)
                webView.webViewClient = object : WebViewClient() {
                    override fun onPageFinished(view: WebView?, url: String?) {
                        try {
                            val printManager = appContext.getSystemService(Context.PRINT_SERVICE) as? PrintManager
                            if (printManager == null) {
                                Log.w(TAG, "PrintManager unavailable on this device")
                                return
                            }
                            val adapter = webView.createPrintDocumentAdapter("RioParkTicket")
                            printManager.print(
                                "RioPark Ticket",
                                adapter,
                                PrintAttributes.Builder().build()
                            )
                        } catch (e: Exception) {
                            Log.e(TAG, "Failed to start print job", e)
                        }
                    }
                }

                val safe = text
                    .replace("&", "&amp;")
                    .replace("<", "&lt;")
                    .replace(">", "&gt;")
                    .replace("\n", "<br>")

                val html = """
                    <html><body style="font-family: monospace; font-size: 12px;">
                    <pre>$safe</pre>
                    </body></html>
                """.trimIndent()

                webView.loadDataWithBaseURL(null, html, "text/html", "UTF-8", null)
            } catch (e: Exception) {
                Log.e(TAG, "Print helper failed", e)
            }
        }
    }
}
