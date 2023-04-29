package com.example.myapplication

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TableLayout
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView

data class MyItem(var name: String)

class MyAdapter(private val myDataset: ArrayList<MyItem>) :
    RecyclerView.Adapter<MyAdapter.MyViewHolder>() {

    var onItemClick: ((MyItem) -> Unit)? = null

    class MyViewHolder(val tableLayout: TableLayout) : RecyclerView.ViewHolder(tableLayout)

    override fun onCreateViewHolder(
        parent: ViewGroup,
        viewType: Int
    ): MyAdapter.MyViewHolder {
        val tableLayout = LayoutInflater.from(parent.context)
            .inflate(R.layout.finished_works, parent, false) as TableLayout
        return MyViewHolder(tableLayout)
    }

    override fun onBindViewHolder(holder: MyViewHolder, position: Int) {
        val item = myDataset[position]
        item.name = item.name + " закончил уборку!"
        holder.tableLayout.findViewById<TextView>(R.id.name_text).text = item.name


        holder.itemView.setOnClickListener {
            onItemClick?.invoke(item)

        }
    }
    override fun getItemCount() = myDataset.size
}