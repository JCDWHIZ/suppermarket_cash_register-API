<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function adminLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if ($credentials['email'] === 'Admin207@gmail.com' && $credentials['password'] === 'AdminJesse') {
            // Admin credentials matched, proceed to generate token
            $admin = User::where('email', $credentials['email'])->first(); // Assuming your User model is named User
            if ($admin) {
                $token = $admin->createToken('authToken')->plainTextToken;
                return response()->json(['admin' => $admin,'token' => $token, 'message' => 'Log in Successful'], 200);
            }
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    public function user()
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            $user = User::all();
            return response()->json(['users' => $user], 201);
        }
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    public function destroyProduct($id)
    {
        if (auth()->check() && auth()->user()->isAdmin()) {

            // Find the product by ID
            $product = Products::find($id);

            // Check if the product exists
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }
            $product->delete();

            // Return a success message
            return response()->json(['message' => 'Product deleted successfully'], 200);
        }
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    public function sendVerificationCode($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if (auth()->check() && auth()->user()->isAdmin()) {

            $verificationCode = mt_rand(1000, 9999);
            $user->verification_code = $verificationCode;
            $user->save();
            Mail::to($user->email)->send(new VerificationCodeMail($verificationCode));
            return response()->json(['message' => 'verification sent'], 200);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
    public function Store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|integer',
            'stock' => 'required|integer'
        ]);

        if (auth()->check() && auth()->user()->isAdmin()) {

            $products = Products::create([
                'name' => $request->input('name'),
                'price' => $request->input('price'),
                'stock' => $request->input('stock')
            ]);

            $products->save();

            return response()->json(['message' => 'Product Created Successfully']);
        }
        return response()->json(['message' => 'Unauthorized.'], 403);
    }
    public function updateProduct(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'price' => 'integer',
            'stock' => 'integer'
        ]);

        if (auth()->check() && auth()->user()->isAdmin()) {

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $product = Products::findOrFail($id);


            $product->name = $request->input('name');
            $product->price = $request->input('price');
            $product->name = $request->input('stock');


            $product->save();

            return response()->json(['message' => 'Product updated Successfully']);
        }
        return response()->json(['message' => 'Unauthorized.'], 403);
    }
    public function destroyCashier($id)
    {
        if (auth()->check() && auth()->user()->isAdmin()) {

            // Find the product by ID
            $product = User::find($id);

            // Check if the product exists
            if (!$product) {
                return response()->json(['error' => 'Cashier not found'], 404);
            }
            $product->delete();

            // Return a success message
            return response()->json(['message' => 'Cashier deleted successfully'], 200);
        }
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    public function purchases()
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            $purchase = Purchase::with('PurchaseDetails')->get();
            return response()->json([$purchase], 200);
        }
        return response()->json(['message' => 'Unauthorized.'], 403);
    }
}