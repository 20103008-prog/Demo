<?php

namespace Modules\Site\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\SiteInquiry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        $featured = Product::where('is_published', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->get();

        $stats = [
            'modules' => Product::where('is_published', true)->count(),
            'employees' => User::where('role', '!=', 'admin')->where('status', 'Active')->count(),
            'companies' => 1,
        ];

        return view('site.home', compact('featured', 'stats'));
    }

    public function products(): View
    {
        $products = Product::where('is_published', true)->orderBy('sort_order')->get();
        $plans = $products->where('category', 'Plan');
        $modules = $products->where('category', 'Module');

        return view('site.products', compact('products', 'plans', 'modules'));
    }

    public function product(string $slug): View
    {
        $product = Product::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $related = Product::where('is_published', true)
            ->where('id', '!=', $product->id)
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        return view('site.product-show', compact('product', 'related'));
    }

    public function contact(): View
    {
        $products = Product::where('is_published', true)->orderBy('sort_order')->get(['id', 'name']);

        return view('site.contact', compact('products'));
    }

    public function storeInquiry(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'product_id' => 'nullable|exists:products,id',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        SiteInquiry::create($data + ['status' => 'New']);

        return back()->with('success', 'Thank you! Our team will contact you shortly.');
    }
}

