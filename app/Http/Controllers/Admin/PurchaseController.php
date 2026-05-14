<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'purchases';
        if ($request->ajax()) {
            $purchases = Purchase::with(['category', 'supplier'])->latest();
            return DataTables::of($purchases)
                ->addColumn('product', function (Purchase $purchase) {
                    $img = $purchase->image
                        ? '<span class="avatar avatar-sm mr-2"><img class="avatar-img rounded" src="' . asset('storage/purchases/' . $purchase->image) . '" alt="product"></span>'
                        : '';
                    return $purchase->product . ' ' . $img;
                })
                ->addColumn('category', function (Purchase $purchase) {
                    return $purchase->category->name ?? '—';
                })
                ->addColumn('cost_price', function (Purchase $purchase) {
                    return settings('app_currency', '$') . ' ' . number_format($purchase->cost_price, 2);
                })
                ->addColumn('supplier', function (Purchase $purchase) {
                    return $purchase->supplier->name ?? '—';
                })
                ->addColumn('expiry_date', function (Purchase $purchase) {
                    $date  = \Illuminate\Support\Carbon::parse($purchase->expiry_date);
                    $badge = $date->isPast()
                        ? '<span class="badge badge-danger">' . $date->format('d M, Y') . '</span>'
                        : $date->format('d M, Y');
                    return $badge;
                })
                ->addColumn('action', function (Purchase $purchase) {
                    $user      = auth()->user();
                    $editbtn   = $user->hasPermissionTo('edit-purchase')
                        ? '<a href="' . route('purchases.edit', $purchase->id) . '" class="btn btn-sm btn-info mr-1" title="Edit"><i class="fas fa-edit"></i></a>'
                        : '';
                    $deletebtn = $user->hasPermissionTo('destroy-purchase')
                        ? '<a data-route="' . route('purchases.destroy', $purchase->id) . '" href="javascript:void(0)" class="deletebtn btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></a>'
                        : '';
                    return $editbtn . $deletebtn;
                })
                ->rawColumns(['product', 'expiry_date', 'action'])
                ->make(true);
        }
        return view('admin.purchases.index',compact(
            'title'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        return view('admin.purchases.create',compact(
            'title','categories','suppliers'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePurchaseRequest $request)
    {
        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
        $purchase = Purchase::create([
            'product'     => $request->product,
            'category_id' => $request->category,
            'supplier_id' => $request->supplier,
            'cost_price'  => $request->cost_price,
            'quantity'    => $request->quantity,
            'expiry_date' => $request->expiry_date,
            'image'       => $imageName,
        ]);
        activity_log('purchased', "Added {$request->quantity} units of {$request->product} to inventory", $purchase);
        return redirect()->route('purchases.index')->with(notify('Purchase added successfully'));
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Purchase $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        $title = 'edit purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        return view('admin.purchases.edit',compact(
            'title','purchase','categories','suppliers'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Purchase $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        $imageName = $purchase->image;
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
        $purchase->update([
            'product'     => $request->product,
            'category_id' => $request->category,
            'supplier_id' => $request->supplier,
            'cost_price'  => $request->cost_price,
            'quantity'    => $request->quantity,
            'expiry_date' => $request->expiry_date,
            'image'       => $imageName,
        ]);
        return redirect()->route('purchases.index')->with(notify('Purchase updated successfully'));
    }

    public function reports(){
        $title ='purchase reports';
        return view('admin.purchases.reports',compact('title'));
    }

    public function generateReport(Request $request){
        $this->validate($request,[
            'from_date' => 'required',
            'to_date' => 'required'
        ]);
        $title = 'purchases reports';
        $purchases = Purchase::whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))->get();
        return view('admin.purchases.reports',compact(
            'purchases','title'
        ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        return Purchase::findOrFail($request->id)->delete();
    }
}
