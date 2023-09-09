<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Models\User;

use App\Models\Category;

use App\Models\Product;

use App\Models\Order;

use PDF;

use Notification;

use RealRashid\SweetAlert\Facades\Alert;

use App\Notifications\SendEmailNotification;

class AdminController extends Controller
{
    public function index()
    {   
        $usertype = Auth::user()->usertype;

        if ($usertype == '1')
        {
           return view('admin.body'); 
        }
        else
        {
            return view('home.userpage');
        }
        

    }

   public function view_category()
{
    if (Auth::check()) {
        $usertype = Auth::user()->usertype;

        if ($usertype == '1') {
            $data = category::all();
            return view('admin.category', compact('data'));
        }
    }

    return redirect('login');
}

     public function add_category(Request $request)
    {

    	$data=new category;

    	$data->category_name=$request->category;

    	$data->save();

    	return redirect()->back()->with('alert','Category Added Successfully');
    }




    public function delete_category($id)
    {

    	$data=category::find($id);

    	$data->delete();

    	return redirect()->back()->with('alert','Category Deleted Successfully!');


    }

    public function view_product()
    {
        if (Auth::check()) 
        {
            $usertype = Auth::user()->usertype;

            if ($usertype == '1')
            {
                 $category=category::all();
        return view('admin.product',compact('category'));
            }

        }

        return redirect('login');
       
    }




    public function add_product(Request $request)
    {

        $product=new product;

        $product->title=$request->title;

        $product->description=$request->description;

        $product->price=$request->price;

        $product->discount_price=$request->discount;

        $product->quantity=$request->quantity;

        $product->category=$request->category;

        $image=$request->image;

        $image=$request->image;

        $imagename=time().'.'.$image->getClientOriginalExtension();

        $request->image->move('product',$imagename);


        $product->image=$imagename;

        $product->save();



        return redirect()->back()->with('alert','Product Successfully Added!');

    }





    public function show_product()
    {
        if (Auth::check()) 
        {
            $usertype = Auth::user()->usertype;

            if ($usertype == '1')
             {
                $product=product::all();
                return view('admin.show_product',compact('product'));
            }
        }

      return redirect('login');
    }


    public function delete_product($id)
    {
        $product=product::find($id);

        $product->delete();

        return redirect()->back()->with('alert','Product Successfully Added!');
    }






    public function update_product($id)

    {
        if (Auth::check())
        {
            $usertype = Auth::user()->usertype;

            if ($usertype == '1')
            {
                 $product=product::find($id);

        $category=category::all();

        return view('admin.update_product',compact('product','category'));
            }

        }   
       
        return redirect('login');
    }





    public function update_product_confirm(Request $request, $id)
    {



        $product=product::find($id);

        $product->title=$request->title;

        $product->description=$request->description;

        $product->price=$request->price;

        $product->discount_price=$request->discount;

        $product->category=$request->category;

        $product->quantity=$request->quantity;

        $image=$request->image;

        if($image)
        {

        $imagename=time().'.'.$image->getClientOriginalExtension();
        $request->image->move('product',$imagename);
        $product->image=$imagename;

        }
        $product->save();


        return redirect()->back()->with('alert','Product Successfully Updated!');

    }






    public function order()

    {
        if (Auth::check())
        {
            $usertype = Auth::user()->usertype;
            if ($usertype == '1')
            {
            $order=order::all();

        return view('admin.order',compact('order'));
            }
        }
      return redirect('login');
    }

        public function delivered($id)

        {

            $order=order::find($id);

            $order->delevery_status="Delivered";

            $order->payment_status="Paid";

            $order->save();

             return redirect()->back()->with('alert','Product Successfully Updated!');

        }



        public function cancel_order($id)
    {
        $order = Order::find($id);
        $order->delevery_status = 'Canceled'; // You can set the status to any appropriate value for canceled orders.
        $order->save();

         return redirect()->back()->with('alert','Product Successfully Updated!');

    }



        public function print_pdf ($id)

        {
            $order=order::find($id);

            $pdf=PDF::loadView('admin.pdf',compact('order'));

            return $pdf->download('order_details.pdf');
        }


        public function send_email ($id)
        {

            $order=order::find($id);

            return view ('admin.email_info',compact('order'));
        }



        public function send_user_email(Request $request , $id)
        {

            $order=order::find($id);

            $details = [

                'greeting' =>$request->greeting,

                'firstline' =>$request->firstline,

                'body' =>$request->body,

                'button' =>$request->button,

                'url' =>$request->url,

                'lastline' =>$request->lastline,

            ];

            Notification::send($order,new SendEmailNotification($details));

            return redirect()->back();
        }


        public function searchdata(Request $request)

        {

            $searchText=$request->search;

            $order=order::where('name','LIKE',"%$searchText%")->orWhere('phone','LIKE',"%$searchText%")->orWhere('address','LIKE',"%$searchText%")->orWhere('product_title','LIKE',"%$searchText%")->orWhere('quantity','LIKE',"%$searchText%")->orWhere('price','LIKE',"%$searchText%")->orWhere('image','LIKE',"%$searchText%")->orWhere('payment_status','LIKE',"%$searchText%")->orWhere('delevery_status','LIKE',"%$searchText%")->orWhere('email','LIKE',"%$searchText%")->get();

            return view('admin.order',compact('order'));
        }

   

    }


