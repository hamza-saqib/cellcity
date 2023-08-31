<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::with('category', 'creator', 'model')->orderby('id', 'desc')->get();
        $categories = ProductCategory::all();
        return view('adminpanel.pages.product_list', compact('products', 'categories'));
    }

    public function search(Request $request)
    {
        $products = Product::
            when($request->filled('product_category_id') , function ($query) use ($request){
                if($request->product_category_id == 'All'){
                    return $query;
                }
                else {
                    return $query->where('product_category_id' , $request->product_category_id );
                }
            })
            ->when($request->filled('old_or_purchased') , function ($query) use ($request){
                if($request->old_or_purchased == 'All'){
                    return $query;
                }
                else if($request->old_or_purchased == 'Old'){
                    return $query->where('opening_qty' , '>', 0);
                }
                else if($request->old_or_purchased == 'Purchased'){
                    return $query->where('opening_qty' , 0);
                }
                else{
                    return $query;
                }
            })
            ->orderby('id', 'desc')->get();
        $request->flash();
        $categories = ProductCategory::all();
        return view('adminpanel.pages.product_list', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = ProductCategory::all();
        $models = ProductModel::all();
        return view('adminpanel.pages.product_create', compact('categories', 'models'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:products,code',
            'product_category_id' => 'required',
            'product_model_id' => 'required',
            'name'=> 'required',
        ]);

        $inputs = $request->all();
        $data[] = null;
        if($request->hasfile('images'))
        {
            foreach($request->file('images') as $key => $image)
            {
                $name=time().'_'. $key . '_'.$image->getClientOriginalName();
                $image->move(public_path().'/storage/images/products', $name);
                $data[] = $name;
            }
        }
        $inputs['images'] = json_encode($data);
        $inputs['created_by'] = Auth::guard('admin')->id();
        $inputs['available_qty'] = $inputs['opening_qty'];
        //$inputs['product_subcategory_id'] = 1;//not in use

        Product::create($inputs);
        return redirect()->route('admin.product.index')->with('success', 'Created Successfuly !');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $categories = ProductCategory::all();
        $invoices = Invoice::join('invoice_details', 'invoices.id', 'invoice_details.invoice_id')
        ->where('product_id', $id)
        ->get();
        return view('adminpanel.pages.product_show', compact('product', 'categories', 'invoices'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::find($id);
        $categories = ProductCategory::all();
        $models = ProductModel::all();
        return view('adminpanel.pages.product_edit', compact('product', 'categories', 'models'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|unique:products,code,'.$id,
            'product_category_id' => 'required',
            'product_model_id' => 'required',
            'name'=> 'required',
        ]);

        $product = Product::find($id);
        $data[] = null;
        $inputs = $request->all();
        if($request->hasfile('images'))
        {
            foreach($request->file('images') as $key => $image)
            {
                $name=time().'_'. $key . '_'.$image->getClientOriginalName();
                $image->move(public_path().'/storage/images/products', $name);
                $data[] = $name;
            }
            $inputs['images'] = json_encode($data);
        }
        $inputs['created_by'] = Auth::guard('admin')->id();
        if($product->opening_qty != $inputs['opening_qty']){
            $no_of_products_sales = InvoiceDetail::join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->where('invoices.type', 'Sale')
            ->where('invoice_details.product_id', $product->id)
            ->sum('invoice_details.sale_quantity');

            $no_of_products_purcahse = InvoiceDetail::join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->where('invoices.type', 'Purchase')
            ->where('invoice_details.product_id', $product->id)
            ->sum('invoice_details.sale_quantity');

            $inputs['available_qty'] = $inputs['opening_qty'] + $no_of_products_purcahse - $no_of_products_sales;
        }
        if($product){
            $product->update($inputs);
            return redirect()->back()->with('success', 'Created Successfuly !');
        }
        return redirect()->back()->with('error', 'Error while creating !');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if($product){
            $product->delete();
            return response()->json(['success'=>'Product deleted successfully !']);
        }
        return response()->json(['error'=>'Product not found !']);
    }
}
