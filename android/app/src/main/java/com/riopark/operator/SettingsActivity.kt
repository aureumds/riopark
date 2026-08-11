package com.riopark.operator

import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import androidx.appcompat.app.AppCompatActivity

class SettingsActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_settings)

        val input = findViewById<EditText>(R.id.urlInput)
        val save = findViewById<Button>(R.id.saveButton)
        val prefs = getSharedPreferences("riopark", Context.MODE_PRIVATE)

        input.setText(prefs.getString("base_url", "http://10.0.2.2"))

        save.setOnClickListener {
            val url = input.text.toString().trim().removeSuffix("/")
            prefs.edit().putString("base_url", url).apply()
            startActivity(Intent(this, MainActivity::class.java))
            finish()
        }
    }
}
