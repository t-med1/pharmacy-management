<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Events\PurchaseOutStock;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $title = 'sales';
        if ($request->ajax()) {
            $sales = Sale::with(['product.purchase'])->latest();
            return DataTables::of($sales)
                ->addIndexColumn()
                ->addColumn('product', function (Sale $sale) {
                    if (empty($sale->product->purchase)) {
                        return '—';
                    }
                    $img = $sale->product->purchase->image
                        ? '<span class="avatar avatar-sm mr-2"><img class="avatar-img rounded" src="' . asset('storage/purchases/' . $sale->product->purchase->image) . '" alt="img"></span>'
                        : '';
                    return $sale->product->purchase->product . ' ' . $img;
                })
                ->addColumn('total_price', function (Sale $sale) {
                    return settings('app_currency', '$') . ' ' . number_format($sale->total_price, 2);
                })
                ->addColumn('date', function (Sale $sale) {
                    return Carbon::parse($sale->created_at)->format('d M, Y');
                })
                ->addColumn('action', function (Sale $sale) {
                    $user      = auth()->user();
                    $editbtn   = $user->hasPermissionTo('edit-sale')
                        ? '<a href="' . route('sales.edit', $sale->id) . '" class="btn btn-sm btn-info mr-1" title="Edit"><i class="fas fa-edit"></i></a>'
                        : '';
                    $deletebtn = $user->hasPermissionTo('destroy-sale')
                        ? '<a data-route="' . route('sales.destroy', $sale->id) . '" href="javascript:void(0)" class="deletebtn btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></a>'
                        : '';
                    return $editbtn . $deletebtn;
                })
                ->rawColumns(['product', 'action'])
                ->make(true);
        }

        $products = Product::with('purchase')->get();
        return view('admin.sales.index', compact('title', 'products'));
    }

    public function create()
    {
        $title    = 'create sale';
        $products = Product::with('purchase')->get();
        return view('admin.sales.create', compact('title', 'products'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product'  => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $soldProduct   = Product::with('purchase')->findOrFail($request->product);
        $purchasedItem = $soldProduct->purchase;

        if (empty($purchasedItem)) {
            return redirect()->back()->with(notify('Invalid product — no inventory record found.', 'danger'));
        }

        $newQuantity = $purchasedItem->quantity - $request->quantity;

        if ($newQuantity < 0) {
            return redirect()->back()->with(notify('Not enough stock. Available: ' . $purchasedItem->quantity, 'danger'));
        }

        DB::transaction(function () use ($purchasedItem, $newQuantity, $soldProduct, $request) {
            $purchasedItem->update(['quantity' => $newQuantity]);

            $sale = Sale::create([
                'product_id'  => $request->product,
                'quantity'    => $request->quantity,
                'total_price' => $request->quantity * $soldProduct->price,
            ]);

            activity_log('sold', "Sold {$request->quantity}× {$soldProduct->purchase->product} for " . settings('app_currency', '$') . number_format($sale->total_price, 2), $sale);
        });

        $notification = notify('Sale recorded successfully');

        if ($newQuantity <= 5 && $newQuantity > 0) {
            $product = Purchase::where('quantity', '<=', 5)->first();
            if ($product) {
                event(new PurchaseOutStock($product));
            }
            $notification = notify('Sale recorded. Warning: stock is running low (' . $newQuantity . ' remaining)!', 'warning');
        }

        return redirect()->route('sales.index')->with($notification);
    }

    public function edit(Sale $sale)
    {
        $title    = 'edit sale';
        $products = Product::with('purchase')->get();
        return view('admin.sales.edit', compact('title', 'sale', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        $this->validate($request, [
            'product'  => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $soldProduct   = Product::with('purchase')->findOrFail($request->product);
        $purchasedItem = $soldProduct->purchase;

        if (empty($purchasedItem)) {
            return redirect()->back()->with(notify('Invalid product — no inventory record found.', 'danger'));
        }

        // Restore the old quantity before recalculating
        $restoredQuantity = $purchasedItem->quantity + $sale->quantity;
        $newQuantity      = $restoredQuantity - $request->quantity;

        if ($newQuantity < 0) {
            return redirect()->back()->with(notify('Not enough stock. Available: ' . $restoredQuantity, 'danger'));
        }

        DB::transaction(function () use ($purchasedItem, $newQuantity, $soldProduct, $request, $sale) {
            $purchasedItem->update(['quantity' => $newQuantity]);

            $sale->update([
                'product_id'  => $request->product,
                'quantity'    => $request->quantity,
                'total_price' => $request->quantity * $soldProduct->price,
            ]);
        });

        $notification = notify('Sale updated successfully');

        if ($newQuantity <= 5 && $newQuantity > 0) {
            $product = Purchase::where('quantity', '<=', 5)->first();
            if ($product) {
                event(new PurchaseOutStock($product));
            }
            $notification = notify('Sale updated. Warning: stock is running low (' . $newQuantity . ' remaining)!', 'warning');
        }

        return redirect()->route('sales.index')->with($notification);
    }

    public function reports()
    {
        $title = 'sales reports';
        return view('admin.sales.reports', compact('title'));
    }

    public function generateReport(Request $request)
    {
        $this->validate($request, [
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
        ]);

        $title = 'sales reports';
        $sales = Sale::with(['product.purchase'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$request->from_date, $request->to_date])
            ->get();

        return view('admin.sales.reports', compact('sales', 'title'));
    }

    public function destroy(Request $request)
    {
        $sale = Sale::findOrFail($request->id);

        // Restore inventory on delete
        if ($sale->product && $sale->product->purchase) {
            $sale->product->purchase->increment('quantity', $sale->quantity);
        }

        $sale->delete();
        return response()->json(['success' => true]);
    }
}
