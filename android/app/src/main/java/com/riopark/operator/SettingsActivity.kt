package com.riopark.operator

import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity

class SettingsActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_settings)

        val input = findViewById<EditText>(R.id.urlInput)
        val save = findViewById<Button>(R.id.saveButton)
        val prefs = getSharedPreferences("riopark", Context.MODE_PRIVATE)

        // Show current URL (strip http:// default from old versions)
        val current = prefs.getString("base_url", "") ?: ""
        input.setText(if (current == "http://10.0.2.2") "" else current)

        save.setOnClickListener {
            val raw = input.text.toString().trim().trimEnd('/')

            if (raw.isEmpty()) {
                Toast.makeText(this, "Informe a URL do servidor.", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            // Always store as https://
            val url = when {
                raw.startsWith("https://") -> raw
                raw.startsWith("http://")  -> "https://" + raw.removePrefix("http://")
                else                       -> "https://$raw"
            }

            prefs.edit().putString("base_url", url).apply()
            Toast.makeText(this, "URL salva: $url", Toast.LENGTH_SHORT).show()
            startActivity(Intent(this, MainActivity::class.java))
            finish()
        }
    }
}
