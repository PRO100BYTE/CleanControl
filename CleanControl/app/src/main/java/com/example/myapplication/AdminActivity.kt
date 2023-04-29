package com.example.myapplication

import android.content.Intent
import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.myapplication.MyAdapter
import com.example.myapplication.R

class AdminActivity : AppCompatActivity() {
    private lateinit var recyclerView: RecyclerView
    private lateinit var viewAdapter: RecyclerView.Adapter<*>
    private lateinit var viewManager: RecyclerView.LayoutManager


    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.admin_activity)

        val names = arrayOf("Имя1", "Имя2", "Имя3")

        val myDataset = ArrayList<MyItem>()
        for (i in names.indices) {
            myDataset.add(MyItem(names[i]))
        }

        viewManager = LinearLayoutManager(this)
        viewAdapter = MyAdapter(myDataset)

        (viewAdapter as MyAdapter).onItemClick = { item ->
            val intent = Intent(this, ThirdActivity::class.java)
            startActivity(intent)
        }

        recyclerView = findViewById<RecyclerView>(R.id.finish_recycler_view).apply {
            setHasFixedSize(true)
            layoutManager = viewManager
            adapter = viewAdapter
        }
    }
}