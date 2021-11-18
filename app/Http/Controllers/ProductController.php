<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if(!empty($request->title)){
            $products=Product::where('title','like','%'.$request->title.'%')->paginate(10);
        }
        if(!empty($request->variant)){
            $products=Product::with('variant')->where('variant_id',$request->variant)->paginate(10);
        }
        if(!empty($request->date)){
            $products=Product::where('created_at',$request->date)->paginate(10);
        }
        if(!empty($request->price_from) && !empty($request->price_to)){
            $products=Product::with('product_price_variant')->wehere('price','>=',$request->price ,'&&' ,'price','=<',$request->price_to)->paginate(10);
        }
        $products = Product::paginate(10);
        $product_varients = ProductVariant::get();
        return view('products.index',compact('products','product_varients'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'title' => 'required'
        ]);
        $product = Product::find($request->id);
        try {
            DB::beginTransaction();
            $product = Product::Create(
                [
                    'id'=>$request->id
                ],[
                'title'=>$request->title,
                'sku'=>$request->sku,
                'description'=>$request->description
            ]);

            if (!empty($product)){
                $product_image = ProductImage::create([
                    'product_id' => $product->id,
                    'file_path' => session()->get('file_path'),
                ]);
//                 if(!empty($request->beds) && count($request->beds) > 0){
//                     $beds = [];
//                     foreach ($request->beds as $bed){
//                         array_push($beds, [
// //                            'room_id'=>$room->id,
//                             'bed_type'=>$bed['bed_type'],
//                             'bed_size'=>$bed['bed_size'],
//                         ]);
//                     }

//                     if(!empty($beds) && count($beds) > 0){
//                         $room->beds()->createMany($beds);
//                     }
//                 }

                DB::commit();
                return response()->json('success');
                // return redirect()->route('product.index')->with('success', 'New product added successfully');
            }

            throw new \Exception('Invalid information');
        }catch (\Exception $ex){
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        $product = Product::find($product->id);
        return view('products.edit', compact('variants','product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }

    public function upload_image(Request $request)
    {
        // dd($request->all());
        $result = [];
        $name = $request->file('file')->getClientOriginalName();
        $path = $request->file('file')->store('images/product');
        
        if(!empty($path)){
            $result['result'] = 'success';
            $result['message'] = 'Image Upload Successfully.';
            $result['path'] = URL::to('/').'/storage/'.$path;
            session()->put('file_path', $result['path']);
        // dd(session()->get('file_path'));
        } else{
            $result['result'] = 'error';
            $result['message'] = 'Image Upload Failed.';
        }
        return response()->json($result);
    }
}
