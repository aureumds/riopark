package com.riopark.operator

import android.content.Context
import android.print.PrintAttributes
import android.print.PrintDocumentAdapter
import android.print.PrintManager
import android.webkit.WebView
import android.webkit.WebViewClient

object PrintHelper {

    fun print(context: Context, text: String) {
        val webView = WebView(context)
        webView.webViewClient = object : WebViewClient() {
            override fun onPageFinished(view: WebView?, url: String?) {
                val printManager = context.getSystemService(Context.PRINT_SERVICE) as PrintManager
                val adapter: PrintDocumentAdapter = webView.createPrintDocumentAdapter("RioParkTicket")
                printManager.print(
                    "RioPark Ticket",
                    adapter,
                    PrintAttributes.Builder().build()
                )
            }
        }

        val html = """
            <html><body style="font-family: monospace; font-size: 12px;">
            <pre>${text.replace("\n", "<br>")}</pre>
            </body></html>
        """.trimIndent()

        webView.loadDataWithBaseURL(null, html, "text/html", "UTF-8", null)
    }
}
