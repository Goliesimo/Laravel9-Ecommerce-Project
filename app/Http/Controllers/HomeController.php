<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Comment; // Add the Comment model
use App\Models\CommentReply; // Add the CommentReply model
use Session;
use Stripe\Stripe;
use Stripe\Charge;

use RealRashid\SweetAlert\Facades\Alert;


class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $usertype = Auth::user()->usertype;

            if ($usertype == '1') {
                // Admin Dashboard logic here
                $total_product = Product::count();
                $total_order = Order::count();
                $total_user = User::count();
                $order = Order::all();
                $total_revenue = 0;

                foreach ($order as $order) 
                {
                    $total_revenue += $order->price;
                }

                $total_delivered = Order::where('delevery_status', 'delivered')->count();
                $total_proccessing = Order::where('delevery_status', 'Processing')->count();

                return view('admin.home', compact('total_product', 'total_order', 'total_user', 'total_revenue', 'total_delivered', 'total_proccessing'));
            }

              else 

         {
            $product = Product::paginate(3);

            $comments = Comment::with('replies')->get();
            

             return view('home.userpage', compact('product','comments'));
        }

        }
        
          else 

         {
            $product = Product::paginate(3);

            $comments = Comment::with('replies')->get();
            

             return view('home.userpage', compact('product','comments'));
        }

    }





    public function redirect()
    {
        $usertype = Auth::user()->usertype;

        if ($usertype == '1')

         {
            $total_product = Product::count();
            $total_order = Order::count();
            $total_user = User::count();
            $order = Order::all();
            $total_revenue = 0;

            foreach ($order as $order) 
            {
                $total_revenue += $order->price;
            }

            $total_delivered = Order::where('delevery_status', 'delivered')->count();
            $total_proccessing = Order::where('delevery_status', 'Proccessing')->count();

            return view('admin.home', compact('total_product', 'total_order', 'total_user', 'total_revenue', 'total_delivered', 'total_proccessing'));
        }
        
         else 

         {
            $product = Product::paginate(3);

            $comments = Comment::with('replies')->get();
            

             return view('home.userpage', compact('product','comments'));
        }
    }


    public function product_details($id)
    {
        if(Auth::id())
        {
            $user = Auth::user();
             $cart_count = Cart::where('user_id', Auth::id())->count();
        Session::put('cart_count', $cart_count);
            $product = Product::find($id);
        return view('home.product_details', compact('product'));
        }

        else
        {
            $product = Product::find($id);
        return view('home.product_details', compact('product'));
        }
    }






    public function add_cart(Request $request, $id)
    {
        if (Auth::id()) {
            $user = Auth::user();

            $cart_count = Cart::where('user_id', Auth::id())->count();
        Session::put('cart_count', $cart_count);

            $userid=$user->id;            
            $product = Product::find($id);


            $product_exist_id=cart::where('product_id','=',$id)->where('user_id','=',$userid)->get('id')->first();

            if($product_exist_id)
            {
                $cart=cart::find($product_exist_id)->first();

                $quantity=$cart->quantity;

                $cart->quantity=$quantity + $request->quantity;

                if ($product->discount_price != null) 

            {
                $cart->price = $product->discount_price * $cart->quantity;
            }
            
             else 
             {
                $cart->price = $product->price * $cart->quantity;
            }

                $cart->save();
Alert::success('Product Added Successfully', 'We have added the item to your cart');
return redirect()->back();

            }

            else
            {

                $cart = new Cart();

            $cart->name = $user->name;
            $cart->email = $user->email;
            $cart->phone = $user->phone;
            $cart->address = $user->address;
            $cart->user_id = $user->id;

            $cart->product_title = $product->title;

            if ($product->discount_price != null) 

            {
                $cart->price = $product->discount_price * $request->quantity;
            }
            
             else 
             {
                $cart->price = $product->price * $request->quantity;
            }

            $cart->image = $product->image;
            $cart->product_id = $product->id;
            $cart->quantity = $request->quantity;
         $cart->save();
Alert::success('Product Added Successfully', 'We have added the item to your cart');
return redirect()->back();

            }

        }

         else
         {
            return redirect('login');
        }
    }










    public function show_cart()
    {
        if (Auth::id()) {
            $id = Auth::user()->id;
            $cart = Cart::where('user_id', $id)->get();
            $cart_count = $cart->count(); // Get the cart count
        Session::put('cart_count', $cart_count); // Store the cart count in the session

            return view('home.showcart', compact('cart'));
        } else {
            return redirect('login');
        }
    }






    public function remove_cart($id)
    {
        $cart = Cart::find($id);
        $cart->delete();
        Alert::success('Item has be removed from your Cart');
return redirect()->back();
    }





    public function cash_order()
    {
        $user = Auth::user();
        $userid = $user->id;
        $data = Cart::where('user_id', $userid)->get();

        foreach ($data as $data) {
            $order = new Order();
            $order->name = $data->name;
            $order->email = $data->email;
            $order->phone = $data->phone;
            $order->address = $data->address;
            $order->user_id = $data->user_id;
            $order->product_title = $data->product_title;
            $order->price = $data->price;
            $order->quantity = $data->quantity;
            $order->image = $data->image;
            $order->product_id = $data->product_id;

            $order->payment_status = 'Cash on Delivery';
            $order->delevery_status = 'Processing';

            $order->save();

            $cart_id = $data->id;
            $cart = Cart::find($cart_id);
            $cart->delete();
        }

      Alert::success('Item has be removed from your Cart');
return redirect()->back();
    }






    public function stripe($totalprice)
    {
        return view('home.stripe', compact('totalprice'));
    }






    public function stripePost(Request $request, $totalprice)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        Charge::create([
            "amount" => $totalprice * 100,
            "currency" => "usd",
            "source" => $request->stripeToken,
            "description" => "Thanks for payment!<span><i class='fa fa-thumb'/i></span>"
        ]);

        $user = Auth::user();
        $userid = $user->id;
        $data = Cart::where('user_id', $userid)->get();

        foreach ($data as $data) {
            $order = new Order();
            $order->name = $data->name;
            $order->email = $data->email;
            $order->phone = $data->phone;
            $order->address = $data->address;
            $order->user_id = $data->user_id;
            $order->product_title = $data->product_title;
            $order->price = $data->price;
            $order->quantity = $data->quantity;
            $order->image = $data->image;
            $order->product_id = $data->product_id;

            $order->payment_status = 'Paid';
            $order->delevery_status = 'Processing';

            $order->save();

            $cart_id = $data->id;
            $cart = Cart::find($cart_id);
            $cart->delete();
        }

        Session::flash('success', 'Payment Successful!');

        return back();
    }






    public function show_order()
    {
        if (Auth::id()) {
            $user = Auth::user();
            $userid = $user->id;
            $order = Order::where('user_id', $userid)->get();

            return view('home.order', compact('order'));
        } else {
            return redirect('login');
        }
    }






    public function cancel_order($id)
    {
        $order = Order::find($id);
        $order->delevery_status = 'You canceled the Order';
        $order->save();

        return redirect()->back();
    }







    public function store(Request $request)
    {
        // Validate the request data

        $comment = new Comment();
        $comment->username = $request->username;
        $comment->comment = $request->comment;
        $comment->user_id = $request->user_id;
        $comment->save();

        Alert::success('Order Received!', 'Wait for Order confirmation...');
return redirect()->back();

    }






    public function storeReply(Request $request)
    {
        // Validate the request data

        $reply = new CommentReply();
        $reply->comment_id = $request->comment_id;
        $reply->username = $request->username;
        $reply->reply = $request->reply;
         $reply->user_id = $request->user_id;
        $reply->save();

        return redirect()->back();
    }





  
    public function product_search(Request $request)
    {
        $comments = Comment::with('replies')->get();

        $searchTerm = $request->input('search');
        
        // Perform the search query
        $product = Product::where('title', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                           ->paginate(10); // Paginate the results (show 10 products per page)
        
       return view('home.userpage', compact('product','comments'));
    }






    public function products()
    {

          if(Auth::id())
        {
            $user = Auth::user();
            $cart_count = Cart::where('user_id', Auth::id())->count();
        Session::put('cart_count', $cart_count);

        $product = Product::paginate(10);

        $comments = Comment::with('replies')->get();

        return view('home.all_products', compact('product','comments'));
    }
      
      else
      {
        $product = Product::paginate(10);

        $comments = Comment::with('replies')->get();

        return view('home.all_products', compact('product','comments'));
      }

    }



     public function search_product(Request $request)
    {
        $comments = Comment::with('replies')->get();

        $searchTerm = $request->input('search');
        
        // Perform the search query
        $product = Product::where('title', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                           ->paginate(10); // Paginate the results (show 10 products per page)
        
       return view('home.all_products', compact('product','comments'));
    }




}



       
        
       
        
        