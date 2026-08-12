<?php

namespace Modules\Site\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SiteInquiry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function products(): View
    {
        $products = Product::orderBy('sort_order')->get();

        return view('admin.products', compact('products'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $data = $request->validate([
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        $product->update([
            'price_monthly' => $data['price_monthly'],
            'price_yearly' => $data['price_yearly'],
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return back()->with('success', 'Product updated.');
    }

    public function inquiries(): View
    {
        $inquiries = SiteInquiry::with('product')->latest()->get();

        return view('admin.inquiries', compact('inquiries'));
    }

    public function updateInquiry(Request $request, SiteInquiry $inquiry)
    {
        $data = $request->validate(['status' => 'required|in:New,Contacted,Closed']);
        $inquiry->update(['status' => $data['status']]);

        return back()->with('success', 'Inquiry status updated.');
    }
}
