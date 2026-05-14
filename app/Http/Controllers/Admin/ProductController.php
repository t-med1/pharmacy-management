<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $title = 'products';
        if ($request->ajax()) {
            $products = Product::with(['purchase.category'])->latest();
            return $this->buildProductDataTable(DataTables::of($products));
        }
        return view('admin.products.index', compact('title'));
    }

    public function create()
    {
        $title = 'add product';
        $purchases = Purchase::orderBy('product')->get();
        return view('admin.products.create', compact('title', 'purchases'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product'     => 'required',
            'price'       => 'required|numeric|min:0.01',
            'discount'    => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|max:500',
        ]);

        Product::create([
            'purchase_id' => $request->product,
            'price'       => $this->calculateSellingPrice($request->price, $request->discount),
            'discount'    => $request->discount ?? 0,
            'description' => $request->description,
        ]);

        return redirect()->route('products.index')->with(notify('Product has been added'));
    }

    public function edit(Product $product)
    {
        $title = 'edit product';
        $purchases = Purchase::orderBy('product')->get();
        return view('admin.products.edit', compact('title', 'product', 'purchases'));
    }

    public function update(Request $request, Product $product)
    {
        $this->validate($request, [
            'product'     => 'required',
            'price'       => 'required|numeric|min:0.01',
            'discount'    => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|max:500',
        ]);

        $product->update([
            'purchase_id' => $request->product,
            'price'       => $this->calculateSellingPrice($request->price, $request->discount),
            'discount'    => $request->discount ?? 0,
            'description' => $request->description,
        ]);

        return redirect()->route('products.index')->with(notify('Product has been updated'));
    }

    public function expired(Request $request)
    {
        $title = 'Expired Medicines';
        if ($request->ajax()) {
            $purchases = Purchase::with('category')
                ->whereDate('expiry_date', '<=', Carbon::today())
                ->latest();
            return $this->buildPurchaseDataTable(DataTables::of($purchases));
        }
        return view('admin.products.expired', compact('title'));
    }

    public function outstock(Request $request)
    {
        $title = 'Out of Stock';
        if ($request->ajax()) {
            $purchases = Purchase::with('category')
                ->where('quantity', '<=', 0)
                ->latest();
            return $this->buildPurchaseDataTable(DataTables::of($purchases));
        }
        return view('admin.products.outstock', compact('title'));
    }

    public function destroy(Request $request)
    {
        return Product::findOrFail($request->id)->delete();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Apply discount percentage to base price.
     * e.g. price=100, discount=10 → 90.00
     */
    private function calculateSellingPrice(float $price, ?float $discount): float
    {
        if ($discount > 0) {
            return round($price - ($price * ($discount / 100)), 2);
        }
        return round($price, 2);
    }

    /** DataTable builder for Product rows (index page). */
    private function buildProductDataTable($datatable)
    {
        return $datatable
            ->addColumn('product', function (Product $product) {
                if (empty($product->purchase)) {
                    return '—';
                }
                $img = $product->purchase->image
                    ? '<span class="avatar avatar-sm mr-2"><img class="avatar-img rounded" src="' . asset('storage/purchases/' . $product->purchase->image) . '" alt="img"></span>'
                    : '';
                return $product->purchase->product . ' ' . $img;
            })
            ->addColumn('category', function (Product $product) {
                return $product->purchase->category->name ?? '—';
            })
            ->addColumn('price', function (Product $product) {
                return settings('app_currency', '$') . ' ' . number_format($product->price, 2);
            })
            ->addColumn('quantity', function (Product $product) {
                $qty = $product->purchase->quantity ?? 0;
                $badge = $qty <= 0
                    ? '<span class="badge badge-danger">Out of Stock</span>'
                    : ($qty <= 5 ? '<span class="badge badge-warning">' . $qty . '</span>' : $qty);
                return $badge;
            })
            ->addColumn('expiry_date', function (Product $product) {
                if (empty($product->purchase->expiry_date)) {
                    return '—';
                }
                $date   = Carbon::parse($product->purchase->expiry_date);
                $badge  = $date->isPast()
                    ? '<span class="badge badge-danger">' . $date->format('d M, Y') . '</span>'
                    : $date->format('d M, Y');
                return $badge;
            })
            ->addColumn('action', function (Product $product) {
                return $this->actionButtons(
                    route('products.edit', $product->id),
                    route('products.destroy', $product->id),
                    'edit-product',
                    'destroy-purchase'
                );
            })
            ->rawColumns(['product', 'quantity', 'expiry_date', 'action'])
            ->make(true);
    }

    /** DataTable builder for Purchase rows (expired / outstock pages). */
    private function buildPurchaseDataTable($datatable)
    {
        return $datatable
            ->addColumn('product', function (Purchase $purchase) {
                $img = $purchase->image
                    ? '<span class="avatar avatar-sm mr-2"><img class="avatar-img rounded" src="' . asset('storage/purchases/' . $purchase->image) . '" alt="img"></span>'
                    : '';
                return $purchase->product . ' ' . $img;
            })
            ->addColumn('category', function (Purchase $purchase) {
                return $purchase->category->name ?? '—';
            })
            ->addColumn('cost_price', function (Purchase $purchase) {
                return settings('app_currency', '$') . ' ' . number_format($purchase->cost_price, 2);
            })
            ->addColumn('quantity', function (Purchase $purchase) {
                return $purchase->quantity;
            })
            ->addColumn('expiry_date', function (Purchase $purchase) {
                return $purchase->expiry_date
                    ? Carbon::parse($purchase->expiry_date)->format('d M, Y')
                    : '—';
            })
            ->addColumn('action', function (Purchase $purchase) {
                return $this->actionButtons(
                    route('purchases.edit', $purchase->id),
                    route('purchases.destroy', $purchase->id),
                    'edit-purchase',
                    'destroy-purchase'
                );
            })
            ->rawColumns(['product', 'action'])
            ->make(true);
    }

    /** Build edit + delete action buttons with permission checks. */
    private function actionButtons(
        string $editRoute,
        string $deleteRoute,
        string $editPermission,
        string $deletePermission
    ): string {
        $user       = auth()->user();
        $editbtn    = $user->hasPermissionTo($editPermission)
            ? '<a href="' . $editRoute . '" class="btn btn-sm btn-info mr-1" title="Edit"><i class="fas fa-edit"></i></a>'
            : '';
        $deletebtn  = $user->hasPermissionTo($deletePermission)
            ? '<a data-id="" data-route="' . $deleteRoute . '" href="javascript:void(0)" class="deletebtn btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></a>'
            : '';
        return $editbtn . $deletebtn;
    }
}
