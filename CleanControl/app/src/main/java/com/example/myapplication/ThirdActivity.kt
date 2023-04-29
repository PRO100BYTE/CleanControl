package com.example.myapplication

import android.content.ContentValues
import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.provider.MediaStore
import android.util.Log
import android.widget.Button
import androidx.activity.result.contract.ActivityResultContracts
import java.io.File
import okhttp3.*
import okhttp3.RequestBody.Companion.asRequestBody
import java.io.IOException
import android.os.Handler
import android.os.Looper
import android.widget.TextView

val serverUrl = "YOUR_SERVER_URL"

val client = OkHttpClient()

class ThirdActivity : AppCompatActivity() {
    private var seconds = 0
    private var running = true
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.third_activity)

        val values = ContentValues()
        values.put(MediaStore.Images.Media.TITLE, "New Picture")
        values.put(MediaStore.Images.Media.DESCRIPTION, "From Camera")
        val photoUri = contentResolver.insert(MediaStore.Images.Media.EXTERNAL_CONTENT_URI, values)

        val takePicture = registerForActivityResult(ActivityResultContracts.TakePicture()) { success ->
            if (success) {
                // снимок сохранен по адресу photoUri
                var str = photoUri.toString()
                Log.d("myTag", str);

                val file = File(photoUri?.path ?: null)
                val requestBody = MultipartBody.Builder()
                    .setType(MultipartBody.FORM)
                    .addFormDataPart("image", file.name, file.asRequestBody())
                    .build()

                val request = Request.Builder()
                    .url(serverUrl)
                    .post(requestBody)
                    .build()

                client.newCall(request).enqueue(object : Callback {
                    override fun onFailure(call: Call, e: IOException) {
                        // Обработка ошибки
                    }

                    override fun onResponse(call: Call, response: Response) {
                        // Обработка успешного ответа
                    }
                })
            }
        }

        val cameraButton = findViewById<Button>(R.id.camera_button)
        cameraButton.setOnClickListener {
            takePicture.launch(photoUri)
        }
        runTimer()
    }
    private fun runTimer() {
        val timeView = findViewById<TextView>(R.id.text_view_timer)
        val handler = Handler(Looper.getMainLooper())

        handler.post(object : Runnable {
            override fun run() {
                val hours = seconds / 3600
                val minutes = (seconds % 3600) / 60
                val secs = seconds % 60

                val time = String.format("%d:%02d:%02d", hours, minutes, secs)
                timeView.text = time

                if (running) {
                    seconds++
                }

                handler.postDelayed(this, 1000)
            }
        })
    }
}
