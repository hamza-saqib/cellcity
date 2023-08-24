<?php

namespace App\Http\Controllers\Adminpanel;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$products = Product::with('category', 'creator')->orderby('id', 'desc')->get();
        $invoices = Invoice::where('invoices.type', 'Purchase')->orderby('invoices.id', 'desc')
        ->leftJoin('payments', 'payments.invoice_id', 'invoices.id')
        ->select('invoices.*', DB::raw('SUM(payments.amount) as amount_paid'))
        ->groupBy('invoices.id')
        ->get();
        return view('adminpanel.pages.purchase_invoice_list', compact('invoices'));
    }

    public function search(Request $request)
    {
        $invoices = Invoice::where('invoices.type', 'Purchase')
            ->when($request->filled('start_date') , function ($query) use ($request){
                return $query->where('invoices.issue_date' , '>=', $request->start_date);

            })
            ->when($request->filled('end_date') , function ($query) use ($request){
                return $query->where('invoices.issue_date' , '<=', $request->end_date);
            })
            ->leftJoin('payments', 'payments.invoice_id', 'invoices.id')
            ->select('invoices.*', DB::raw('SUM(payments.amount) as amount_paid'))
            ->groupBy('invoices.id')
            ->get();
            $request->flash();
        return view('adminpanel.pages.purchase_invoice_list', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $accounts = Account::all();
        $vendors = Vendor::all();
        $products = Product::with('category', 'creator')->orderby('id', 'desc')->get();
        return view('adminpanel.pages.purchase_invoice_create', compact('products', 'vendors', 'accounts'));

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
            'vendor_id'=> 'required',
            'product_id'=> 'required',
        ]);
        $vendor = Vendor::find($request->vendor_id);
        $inputs = $request->all();
        $inputs['cash_recieved'] = $inputs['cash_paid'];
        $no_of_items = 0;
        foreach ($inputs['product_qty'] as $key => $value) {
            $no_of_items = $no_of_items + $value;
        }
        $inputs['no_of_items'] = $no_of_items;
        $inputs['no_of_products'] = sizeof($inputs['product_id']);
        $inputs['type'] = 'Purchase';
        if($vendor->type == 'Cash'){
            $inputs['group'] = 'Cash';
        }
        else{
            $inputs['group'] = 'Credit';
            $preBalance = $vendor->balance;
            $inputs['pre_balance'] = $vendor->balance;
            if($inputs['cash_paid'] > 0){
                $vendor->balance = $preBalance + intval($inputs['amount']) - intval($inputs['cash_paid']);
            }
            else{
                $vendor->balance = $preBalance + intval($inputs['amount']);
            }
            $vendor->save();
        }
        $inputs['created_by'] = Auth::guard('admin')->id();
        $inputs['amount'] = intval($inputs['amount']);
        $product_ids = $inputs['product_id'];
        $product_qtys = $inputs['product_qty'];
        $product_purchase_price = $inputs['product_purchase_price'];

        unset($inputs['product_id']);
        unset($inputs['product_qty']);
        unset($inputs['product_purchase_price']);

        $invoice = Invoice::create($inputs);
        for ($i=0; $i < sizeof($product_ids); $i++) {
            $product = Product::find($product_ids[$i]);
            InvoiceDetail::create(['product_id'=>$product_ids[$i],
                             'sale_quantity'=>$product_qtys[$i],
                             'purchase_price'=>$product_purchase_price[$i],
                             'total_ammount'=>$product_purchase_price[$i] * $product_qtys[$i],
                             'invoice_id'=>$invoice->id]);
            $product->available_qty = $product->available_qty + $product_qtys[$i];
            $product->save();
        }

        if($vendor->type == 'Cash'){
            $account = Account::find($request->account_id);
            Payment::create(['amount'=>intval($inputs['amount']), 'payment_date'=>$inputs['issue_date'], 'group'=>'Out', 'note'=>'Created Auto By System',
             'type'=>'Purchase', 'invoice_id'=>$invoice->id, 'vendor_id'=>$vendor->id, 'account_id'=>$account->id,  'created_by'=>Auth::guard('admin')->id()]);
            $current_balance = $account->balance;
            $account->balance = $current_balance - $inputs['amount'];
            $account->save();
        }
        else{
            if($inputs['cash_paid'] > 0){
                Payment::create(['amount'=>intval($inputs['cash_paid']), 'payment_date'=>date('Y-m-d'), 'group'=>'Out', 'note'=>'Paid in Credit Invoice # '. $invoice->id,
             'type'=>'Purchase', 'invoice_id'=>$invoice->id, 'vendor_id'=>$vendor->id, 'account_id'=>1,  'created_by'=>Auth::guard('admin')->id()]);
            }
        }

        if($request->button == 'Save & Print'){
            return redirect()->back()->with('success', 'Created & Sent For Print Successfuly !');
        }
        return redirect()->back()->with('success', 'Created Successfuly !');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice =  Invoice::with('detail')->find($id);
        return view('adminpanel.pages.purchase_invoice_show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoice = Invoice::where('id', $id)->with('detail')->get()->first();
        $accounts = Account::all();
        $vendors = Vendor::all();
        $products = Product::with('category', 'creator')->orderby('id', 'desc')->get();
        return view('adminpanel.pages.purchase_invoice_edit', compact('invoice', 'products', 'vendors', 'accounts'));
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
            'vendor_id'=> 'required',
            'product_id'=> 'required',
        ]);

        $invoice = Invoice::find($id);

        if($invoice){
            $vendor = Vendor::find($request->vendor_id);
            $inputs = $request->all();
            $inputs['cash_recieved'] = $inputs['cash_paid'];
            $no_of_items = 0;
            foreach ($inputs['product_qty'] as $key => $value) {
                $no_of_items = $no_of_items + $value;
            }
            $inputs['no_of_items'] = $no_of_items;
            $inputs['no_of_products'] = sizeof($inputs['product_id']);
            $inputs['type'] = 'Purchase';
            if($vendor->type == 'Cash'){
                $inputs['group'] = 'Cash';
            }
            else{
                $inputs['group'] = 'Credit';
                $preBalance = $vendor->balance;
                $inputs['pre_balance'] = $vendor->balance;
                if($inputs['cash_paid'] > 0){
                    $vendor->balance = $preBalance + intval($inputs['amount']) - intval($inputs['cash_paid']);
                }
                else{
                    $vendor->balance = $preBalance + intval($inputs['amount']);
                }
                $vendor->save();
            }
            $inputs['created_by'] = Auth::guard('admin')->id();
            $inputs['amount'] = intval($inputs['amount']);
            $product_ids = $inputs['product_id'];
            $product_qtys = $inputs['product_qty'];
            $product_purchase_price = $inputs['product_purchase_price'];

            unset($inputs['product_id']);
            unset($inputs['product_qty']);
            unset($inputs['product_purchase_price']);

            $invoice->no_of_items = $inputs['no_of_items'];
            $invoice->amount = $inputs['amount'];
            $invoice->no_of_products = $inputs['no_of_products'];
            $invoice->vendor_id = $inputs['vendor_id'];
            $invoice->cash_recieved = $inputs['cash_paid'];
            $invoice->description = $inputs['description'];
            $invoice->reference_no = $inputs['reference_no'];
            $invoice->discount = $inputs['discount'];
            
            foreach ($invoice->detail as $key => $invoiceDetail) {
                $product = Product::find($invoiceDetail->product_id);
                $invoiceDetailTemp = InvoiceDetail::find($invoiceDetail->id);
                $product->available_qty = $product->available_qty - $invoiceDetail->sale_quantity;
                $product->save();
                $invoiceDetailTemp->delete();
            }

            for ($i=0; $i < sizeof($product_ids); $i++) {
                $product = Product::find($product_ids[$i]);
                InvoiceDetail::create(['product_id'=>$product_ids[$i],
                                 'sale_quantity'=>$product_qtys[$i],
                                 'purchase_price'=>$product_purchase_price[$i],
                                 'total_ammount'=>$product_purchase_price[$i] * $product_qtys[$i],
                                 'invoice_id'=>$invoice->id]);
                $product->available_qty = $product->available_qty + $product_qtys[$i];
                $product->save();
            }

            $payment = Payment::where('invoice_id', $invoice->id)->where('type', 'Purchase')->first();
            if($payment){
                if($inputs['cash_paid'] > 0){
                    $payment->payment_date = intval($inputs['amount']);
                    $payment->customer_id = $vendor->id;
                    if($vendor->type == 'Cash'){
                        $payment->amount = intval($inputs['amount']);
                        $payment->note = 'Created Auto By System in Invoice # ' . $invoice->id;
                   }
                    else{
                        $payment->amount = intval($inputs['cash_paid']);
                        $payment->note = 'Paid in Credit Invoice # ' . $invoice->id;
                    }
                } else {
                    if($vendor->type == 'Cash'){
                        $payment->amount = intval($inputs['amount']);
                        $payment->note = 'Created Auto By System in Invoice # ' . $invoice->id;
                    }
                    else{
                        $payment->delete();
                    }
                }
                $payment->save();
            }

            $invoice->save();
            return redirect()->back()->with('success', 'Invoice updated Successfuly !');
        }
        return redirect()->back()->with('error', 'Invoice not found');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $invoice = Invoice::find($id);
        if($invoice){
            $payment = Payment::where('invoice_id', $invoice->id)->first();
            if($payment){
                $vendor = Vendor::find($invoice->vendor_id);
                if($vendor->type == 'Credit'){
                    $vendor->balance = $vendor->balance - $payment->amount;
                    $vendor->save();
                }
                $payment->delete();
            }
            foreach ($invoice->detail as $key => $item) {
                $product = Product::find($item->product_id);
                $current_qty = $product->available_qty;
                $product->update(['available_qty'=>($current_qty-$item->sale_quantity)]);
            }
            $invoice->delete();
            return response()->json(['success'=>'invoice deleted successfully !']);
        }
        return response()->json(['error'=>'invoice not found !']);
    }
}
