package com.riopark.operator

import android.content.ActivityNotFoundException
import android.content.Context
import android.content.Intent
import android.os.Handler
import android.os.Looper
import android.util.Log
import android.widget.Toast

object PrintHelper {

    private const val TAG = "RioParkPrint"
    private const val RAWBT_PACKAGE = "ru.a402d.rawbtprinter"
    private const val RAWBT_ACTION = "ru.a402d.rawbtprinter.action.PRINT_TEXT"

    fun print(context: Context, text: String) {
        Handler(Looper.getMainLooper()).post {
            try {
                sendToRawBT(context, text)
            } catch (e: Exception) {
                Log.e(TAG, "PrintHelper failed", e)
            }
        }
    }

    private fun sendToRawBT(context: Context, rawText: String) {
        val formatted = formatTicket(context, rawText)

        val intent = Intent(RAWBT_ACTION).apply {
            setPackage(RAWBT_PACKAGE)
            putExtra("text", formatted)
            // Feed extra lines after print so ticket exits the cutter
            putExtra("extraLineFeed", 4)
        }

        try {
            context.startActivity(intent)
            Log.d(TAG, "Sent to RawBT OK")
        } catch (e: ActivityNotFoundException) {
            Log.w(TAG, "RawBT not installed — falling back to Toast")
            Toast.makeText(
                context,
                "Instale o app RawBT Library para imprimir tickets.",
                Toast.LENGTH_LONG
            ).show()
        }
    }

    /**
     * Receives text in any format from the JS bridge and normalises it into
     * a clean monospaced ticket layout understood by Epson thermal printers
     * via RawBT.
     *
     * Expected input (newline-separated key:value pairs or free text):
     *   "Rio Park\nENTRADA\nPlaca: ABC-1234\nEntrada: ..."
     */
    private fun formatTicket(context: Context, text: String): String {
        val divider = "================================"
        val lines = text.trim().lines()

        val body = StringBuilder()
        body.appendLine(divider)

        for (line in lines) {
            val trimmed = line.trim()
            if (trimmed.isNotEmpty()) {
                body.appendLine(trimmed.take(32))
            }
        }

        body.appendLine(divider)
        body.appendLine()

        return body.toString()
    }
}
