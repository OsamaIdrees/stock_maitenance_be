<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB,DateTime;

class ProductController extends Controller
{
    //
    public function insertProduct(Request $request){
        #insert a new product in database
        $p_name = $request['product_name'];
        $p_type = $request['product_type'];
        $p_price = $request['product_price'];
        $p_stock = $request['product_stock'];
        $ldate = new DateTime;
        $ldate->format('m-d-y H:i:s');
        $unique_product_id = DB::table('product')->where('product_name',$p_name)->pluck('id')->first();
        #check for unique ness of product name
        if(strlen($unique_product_id)>0){
            return response()->json(['status'=>false,'message'=>'Product name already taken.']);
        }
        else{
            #if product name is unique insert in database
            $insert_data = DB::table('product')->insert(['product_name'=>$p_name,'product_type'=>$p_type,'product_price'=>$p_price,'created_at'=>$ldate]);
            if($insert_data){
                #after successfull insertion in database enter first stock entry of product in product info and stock input record table
                $fetch_product_id = DB::table('product')->where('product_name',$p_name)->pluck('id')->first();
                $insert_stock = DB::table('product_info')->insert(['stock'=>$p_stock,'updated_at'=>$ldate,'p_id'=>$fetch_product_id]);
                $stock_input = DB::table('stock_input_record')->insert(['p_id'=>$fetch_product_id,'stock_in'=>$p_stock,'cost_price'=>$p_price,'Date'=>$ldate]);
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
        #get all the product names we have in the database
        $product_name =DB::table('product')->select('product_name')->get();
        if(strlen($product_name) == 2){
            return response()->json(['status'=>false,'message'=>'No Product in the record']);
            
        }
       else{
            return response()->json(['status'=>true,'product_name'=>$product_name]);
       }
   
    }

    public function updateStock(Request $request){
        #update the stock hitory of the product
        #both in add in stock and remove from stock
        $update_stock = $request['stock_value'];
        $product_name = $request['product_name'];
        $updation_type = $request['updation_type'];
        $product_id = DB::table('product')->where('product_name',$product_name)->pluck('id')->first();
        $product_price = DB::table('product')->where('product_name',$product_name)->pluck('product_price')->first();
        $inital_value = DB::table('product_info')->where('p_id',$product_id)->pluck('stock')->first();
        $ldate = new DateTime;
        $ldate->format('m-d-y H:i:s');
        $date = $ldate->format('y-m-d H:i:s');
        $current_date =  explode(' ',$date);
        $current_day = explode('-',$current_date[0]);
        
        if($updation_type == 'Add'){
            #in case of add in must enter cost price on what price you are getting the product
            $cost_price = $request['cost_price'];
            $value = $inital_value +  $update_stock;
            #update cost price in product table and also make  a new entry in stock input record
            $update_cost_price = DB::table('product')->where('product_name',$product_name)->update(['product_price'=>$cost_price]);
            $last_insertion_date = DB::table('stock_input_record')->where('p_id',$product_id)->pluck('Date')->last();
            $extract = explode(' ', $last_insertion_date);
            $extract_updated_day = explode('-',$extract[0]);
            #if last insertion of prodcut has same date and month as of insertion time than update previous stock with updated value
            if($last_insertion_date != ''){
                if($current_day[1] == $extract_updated_day[1] && $current_day[2] == $extract_updated_day[2]){
                    $last_stock_in = DB::table('stock_input_record')->where('p_id',$product_id)->pluck('stock_in')->first();
                    $updated_stock_in = $last_stock_in + $update_stock;
                    $stock_input =  DB::table('stock_input_record')->select('*')->where(['p_id'=>$product_id])->orderBy('Date','desc')->limit(1)->update(['stock_in'=>$updated_stock_in,'cost_price'=>$cost_price,'Date'=>$ldate]);
                }
                else{
                    #make a new entry of stock in stock_input_record_table
                    $stock_input = DB::table('stock_input_record')->insert(['p_id'=>$product_id,'stock_in'=>$update_stock,'cost_price'=>$cost_price,'Date'=>$ldate]);
                }
            }
            

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
        #udpate the total stock we have in product info table
        $update_result = DB::table('product_info')->where('p_id',$product_id)->update(['stock'=>$value,'updated_at'=>$ldate]);
        if($updation_type == 'Subtract'){
            #in case of removal from existence stock user must enter average price on which he is selling the product whole day
            $avg_price = $request['avg_price'];
            #get profit margin by subtracting sellig product cost price from average price 
            $profit_margin = $avg_price - $product_price;
            $profit_revenue = $profit_margin * $update_stock;
            # multiply that difference with item sell and you get your profit
            $previous_sell = DB::table('product_sale')->where('p_id',$product_id)->pluck('sell_record')->first();
            $previous_revenue = DB::table('product_sale')->where('p_id',$product_id)->pluck('revenue_earned')->first();
            $sale_of_product_so_far = 0;
            if($previous_sell == ''){
                #if there is no previous sale record insert sell record ,profit earned nad revenue earned in the product sale table
               $sale_of_product_so_far = $update_stock;
               $revenue_earned =  $avg_price * $update_stock;
               $insert_sell_reocrd = DB::table('product_sale')->insert(['p_id'=>$product_id,'sell_record'=>$sale_of_product_so_far,'revenue_earned'=>$revenue_earned,'profit_earned'=>$profit_revenue,'last_updated'=>$ldate]);
    
            }
            else{
                #if there is already any data in product sale
                #get previous record
                $sale_of_product_so_far = $previous_sell + $update_stock;

                $revenue_of_product =  $update_stock * $avg_price;
                $reveenue_earned  = $previous_revenue + $revenue_of_product;
                
                $previous_profit = DB::table('product_sale')->where('p_id',$product_id)->pluck('profit_earned')->first();
                
                $profit_margin = $avg_price - $product_price;
                $profit_get_by_product_sell = $avg_price * $update_stock;
                $revenue_earned = $previous_profit + $profit_get_by_product_sell;
                #if current date is 1 of month thana add new entry in the product sale table to keep record of this month revenue and profit
                if($current_day[2] != "1"){
                    $update_sell_record = DB::table('product_sale')->where('p_id',$product_id)->update(['sell_record'=> $sale_of_product_so_far,'revenue_earned'=>$reveenue_earned,'profit_earned'=>$revenue_earned,'last_updated'=>$ldate]);
                }
                else{
                    #any other day update the previous value for the row of particular month
                    $revenue = $avg_price * $update_stock;
                    DB::table('product_sale')->insert(['p_id'=>$product_id,'sell_record'=>$update_stock,'revenue_earned'=>$revenue_earned,'profit_earned'=>$profit_revenue,'last_updated'=>$ldate]);
                }

                
    
            }
            # In case of product_per_day_sale if last updated date is same as current date update the particualr row.
            # if date is different add in new row  
            $check_for_date = DB::table('product_per_day_sale')->where('p_id',$product_id)->pluck('Date')->last();
            $updation_date =  explode(' ', $check_for_date);
            $last_updated_day = explode('-',$updation_date[0]);
            if($check_for_date != ''){
                if($current_day[1] == $last_updated_day[1] && $current_day[2] == $last_updated_day[2]){
                    $last_stock_sell = DB::table('product_per_day_sale')->where('p_id',$product_id)->pluck('stock_sell')->last();
                    $previous_average_price = DB::table('product_per_day_sale')->where('p_id',$product_id)->pluck('average_price')->last();
                    $total_stock_sell =  $last_stock_sell + $update_stock;
                    $update_today_stock_info = DB::table('product_per_day_sale')->select('*')->where(['p_id'=>$product_id])->orderBy('Date','desc')->limit(1)->update(['stock_sell'=>$total_stock_sell,'average_price'=>$avg_price]);;
                   
                }
                else{
                    DB::table('product_per_day_sale')->insert(['p_id'=>$product_id,'stock_sell'=>$update_stock,'average_price'=>$avg_price,'Date'=>$ldate]);
                   
                }
            }
            else{
                # if no previous record in product per day sale add as a first entry of the product sale in the table
                $per_day_product_sale = DB::table('product_per_day_sale')->insert(['p_id'=>$product_id,'stock_sell'=>$update_stock,'average_price'=>$avg_price,'Date'=>$ldate]);
            }
           
            
        }
    
       
        if($update_result){
            return response()->json(['status'=>true,'message'=>'Stock Updated Successfully']);
        }
        else{
            return response()->json(['status'=>false,'message'=>'Failed to Update Stock']);
        }
    }
    public function ViewStock(Request $request){
        #get all required information from the respective tables
        $view_stock_name = $request['product_name'];
        $p_id = DB::table('product')->where('product_name',$view_stock_name)->pluck('id')->first();
        $product_detail = DB::table('product')->select('*')->where('product_name',$view_stock_name)->get();
        $stock_info = DB::table('product_info')->select('stock','updated_at')->where('p_id',$p_id)->get();
        $stock_sale_record = DB::table('product_sale')->select('sell_record','revenue_earned','profit_earned','last_updated')->where('p_id',$p_id)->orderBy('last_updated', 'desc')->get();
        $required_info_from_per_day_sale = DB::table('product_per_day_sale')->select('stock_sell','average_price','Date')->where('p_id',$p_id)->orderBy('Date', 'desc')->get();
        $stock_in_record = DB::table('stock_input_record')->select('stock_in','cost_price','Date')->where('p_id',$p_id)->orderBy('Date', 'desc')->get();
        if($stock_sale_record == ''){
            return response()->json(['status'=>true,'product_detail'=>$product_detail,'stock_info'=>$stock_info,'sub_result'=>false,'stock_in_record'=>$stock_in_record]);
        }
        else{
            return response()->json(['status'=>true,'product_detail'=>$product_detail,'stock_info'=>$stock_info,'product_sell_record'=>$stock_sale_record,'sub_result'=>true,'per_day_sale'=>$required_info_from_per_day_sale,'stock_in_record'=>$stock_in_record]);
        }
       
    }
}
