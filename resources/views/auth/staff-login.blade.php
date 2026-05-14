<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-96 border-t-4 border-[#003366]">
        <h2 class="text-2xl font-bold text-[#003366] mb-6 text-center">Staff Login</h2>
        
        <form action="{{ route('staff.login.submit') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700">Email Address</label>
                <input type="email" name="email" class="w-full border p-2 rounded mt-1 outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700">Password</label>
                <input type="password" name="password" class="w-full border p-2 rounded mt-1 outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <button type="submit" class="w-full bg-[#003366] text-white py-2 rounded font-bold hover:bg-blue-900 transition">
                Access Window Panel
            </button>
        </form>
    </div>
</div>