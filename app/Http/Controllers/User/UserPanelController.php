<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Rules\TurkishPhoneRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPanelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * User panel dashboard
     */
    public function index()
    {
        $user = Auth::user();
        // Ücretsiz sürüm: admin onay kuyruğu yok; istatistiklerde "bekleyen onay" gösterilmez
        $stats = [
            'total_listings' => $user->listings()->count(),
            'active_listings' => $user->listings()->where('status', 'active')->count(),
            'draft_listings' => $user->listings()->where('status', 'draft')->count(),
        ];

        return view('user.dashboard', compact('user', 'stats'));
    }

    /**
     * User profile page
     */
    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            'phone' => ['nullable', new TurkishPhoneRule(true, Auth::id())],
            'bio' => 'nullable|string|max:2000',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            'user_id' => 'prohibited', // Prevent user_id manipulation
        ]);

        $user = Auth::user();
        $data = $request->only(['first_name', 'last_name', 'email', 'phone', 'bio']);

        // Profil fotoğrafı: public/uploads/user/ altına yazılır; ilan detay
        // sayfasındaki danışman kartı da bu görseli kullanır.
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $name = 'u'.$user->id.'-'.\Illuminate\Support\Str::random(8).'.'.strtolower($file->getClientOriginalExtension());
            $dir  = public_path('uploads/user');

            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            $file->move($dir, $name);

            // Önceki yüklenen fotoğrafı temizle (varsayılan görsellere dokunma).
            $eski = (string) $user->avatar;
            if ($eski !== '' && str_starts_with($eski, 'uploads/user/') && ! str_contains($eski, 'default-')) {
                @unlink(public_path($eski));
            }

            $data['avatar'] = 'uploads/user/'.$name;
        }

        $user->update($data); // Phone will be auto-sanitized by mutator


        return redirect()->route('user.profile')->with('success', __('user.profile_updated_successfully'));
    }

    /**
     * User's listings page
     */
    public function myListings()
    {
        $listings = Auth::user()->listings()
            ->with(['category', 'images'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.my-listings', compact('listings'));
    }
}
