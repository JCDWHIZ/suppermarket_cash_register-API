<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\PurchaseDetails;
use App\Models\User;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class cashierController extends Controller
{
    public function Login(Request $request)
    {

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json(['user' => $user, 'token' => $token, 'message' => 'Login successful']);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required',
            'password' => 'required|string|min:5|confirmed',
            'cover_img' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
            ],
        ]);
        if ($request->hasFile('cover_img')) {
            $filenameWithExt1 = $request->file('cover_img')->getClientOriginalName();
            $filename1 = pathinfo($filenameWithExt1, PATHINFO_FILENAME);

            $extension1 = $request->file('cover_img')->getClientOriginalExtension();
            $filenameToStore1 = $filename1 . '_' . time() . '.' . $extension1;

            $path = $request->file('cover_img')->storeAs('public/cover_img', $filenameToStore1);
        }

        $user = User::create([
            'name' => $request->name,
            'cover_img' => $filenameToStore1,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json(['user' => $user, 'message' => 'Registration successful', 'token' => $token]);
    }
    public function verify(Request $request)
    {
        $request->validate([
            'verification_code' => 'required',
        ]);

        $user = User::where('verification_code', $request->verification_code)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid verification code'], 422);
        }
        $user->email_verified_at = now();
        $user->verification_code = null;
        $user->save();

        return response()->json(['user' => $user, 'message' => 'Verification successful']);
    }
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();
            return response()->json(['message' => 'Logged Out Successfully'], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
    public function updatePassword(Request $request)
    {
        // Check if the user is authenticated
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:5|confirmed',
        ]);

        $user = auth()->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully'], 201);
    }
    public function findByIdUser($id)
    {
        $user = User::find($id);
        if ($user) {
            return response()->json(['user' => $user], 201);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }
    public function updateCredentials(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required',
            'cover_img' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
            ],
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        if ($request->hasFile('cover_img')) {
            $filenameWithExt1 = $request->file('cover_img')->getClientOriginalName();
            $filename1 = pathinfo($filenameWithExt1, PATHINFO_FILENAME);

            $extension1 = $request->file('cover_img')->getClientOriginalExtension();
            $filenameToStore1 = $filename1 . '_' . time() . '.' . $extension1;

            $path = $request->file('cover_img')->storeAs('public/cover_img', $filenameToStore1);
        } else {
            $filenameToStore1 = 'noimage.jpeg';
        }

        $user = auth()->user();
        $user->name = $request->name;
        $user->cover_img = $filenameToStore1;
        $user->phone_number = $request->phone_number;
        $user->email = $request->email;
        $user->save();

        return response()->json(['message' => 'Credentials updated successfully']);
    }
    public function search($search)
    {
        if (!$search) {
            return response()->json(['message' => 'Search term is required.'], 400);
        }
        $productsByName = Products::where('name', 'like', "%$search%")->get();
        return response()->json([
            'products' => $productsByName
        ]);
    }

    public function Products()
    {
        $products = Products::all();
        return response()->json(['products' => $products], 200);
    }

    public function purchaseDetails(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        // Create the purchase detail
        $purchaseProduct = PurchaseDetails::create($validatedData);

        // Update the product stock
        $product = Products::findOrFail($validatedData['product_id']);
        $product->stock -= $validatedData['quantity'];
        $product->save();

        // Return a response
        return response()->json(['message' => 'Purchase detail created successfully', 'data' => $purchaseProduct], 201);
    }

    public function purchase(Request $request)
    {
        $cashier = Auth::user();

        $validatedData = $request->validate([
            'total_price' => 'required|numeric',
            'transaction_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'required|string',
        ]);

        // Add customer_id to the validated data
        $validatedData['customer_id'] = Auth::id();

        // Create a new transaction record
        $transaction = Purchase::create($validatedData);

        // Update today_sales for the cashier
        $cashier->today_sales += $validatedData['total_price'];
        $cashier->save();

        return response()->json(['message' => 'Transaction created successfully', 'data' => $transaction], 201);
    }
}
