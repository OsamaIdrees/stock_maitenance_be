<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB,DateTime;

class ProductController extends Controller
{
    //
    public function insertProduct(Request $request){
        $p_name = $request['product_name'];
        $p_type = $request['product_type'];
        $p_price = $request['product_price'];
        $p_stock = $request['product_stock'];
        $ldate = new DateTime;
        $ldate->format('m-d-y H:i:s');
        $unique_product_id = DB::table('product')->where('product_name',$p_name)->pluck('id')->first();
        if(strlen($unique_product_id)>0){
            return response()->json(['status'=>false,'message'=>'Product name already taken.']);
        }
        else{
            $insert_data = DB::table('product')->insert(['product_name'=>$p_name,'product_type'=>$p_type,'product_price'=>$p_price,'created_at'=>$ldate]);
            if($insert_data){
                $fetch_product_id = DB::table('product')->where('product_name',$p_name)->pluck('id')->first();
                $insert_stock = DB::table('product_info')->insert(['stock'=>$p_stock,'updated_at'=>$ldate,'p_id'=>$fetch_product_id]);
                if($insert_data & $insert_stock){
                    return response()->json(['status'=>true,'message'=>'Product Added Successfully']);
                }
                else{
                    return response()->json(['status'=>false,'message'=>'Failed to Insert']);
                }
            }
            else{
                return response()->json(['status'=>false,'message'=>'Failed to  Add Product Successfully']);
            }
        
        }

    }

    public function getproductName(){
        $product_name =DB::table('product')->select('product_name')->get();
        if(strlen($product_name) == 2){
            return response()->json(['status'=>false,'message'=>'No Product in the record']);
            
        }
       else{
            return response()->json(['status'=>true,'product_name'=>$product_name]);
       }
   
    }

    public function updateStock(Request $request){
        $update_stock = $request['stock_value'];
        $product_name = $request['product_name'];
        $updation_type = $request['updation_type'];
        $product_id = DB::table('product')->where('product_name',$product_name)->pluck('id')->first();
        $inital_value = DB::table('product_info')->where('p_id',$product_id)->pluck('stock')->first();
        $ldate = new DateTime;
        $ldate->format('m-d-y H:i:s');
        if($updation_type == 'Add'){
            $value = $inital_value +  $update_stock;
        }
        else{
            if($inital_value<=0){
                return response()->json(['status'=>false,'message'=>'Sorry!Updation Failed. Stock Is Empty']);
            }
            $value = $inital_value -  $update_stock;
            if($value<0){
                return response()->json(['status'=>false,'message'=>'Sorry!Limit exceeds. ']);
            }
        }
        $update_result = DB::table('product_info')->where('p_id',$product_id)->update(['stock'=>$value,'updated_at'=>$ldate]);
        if($update_result){
            return response()->json(['status'=>true,'message'=>'Stock Updated Successfully']);
        }
        else{
            return response()->json(['status'=>false,'message'=>'Failed to Update Stock']);
        }
    }
    public function ViewStock(Request $request){
        $view_stock_name = $request['product_name'];
        $p_id = DB::table('product')->where('product_name',$view_stock_name)->pluck('id')->first();
        $product_detail = DB::table('product')->select('*')->where('product_name',$view_stock_name)->get();
        $stock_info = DB::table('product_info')->select('stock','updated_at')->where('p_id',$p_id)->get();
        return response()->json(['status'=>true,'product_detail'=>$product_detail,'stock_info'=>$stock_info]);
    }
}
