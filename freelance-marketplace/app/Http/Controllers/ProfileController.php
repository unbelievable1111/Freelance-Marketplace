<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\UserAvatar;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('components.pages.profile.general');
    }

    public function publicProfileOverview(User $publicUser)
    {
        if ($publicUser->id === auth()->id())
        {
            return redirect()->route('profile.index');
        }

        if (!$publicUser->isExecutor() && !$publicUser->isCustomer())
        {
            abort(404);
        }

        $reviews = Review::where('target_id', $publicUser->id)
            ->latest()
            ->paginate(5);
        

        return view('components.pages.profile.public-overview', compact('publicUser', 'reviews'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048', 
        ]);

        $user = $request->user();

        $file = $request->file('avatar');
        $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();

        $manager = new ImageManager(new Driver());

        $image = $manager->read($file);

        if ($image->width() >= 500 || $image->height())
        {
            $image->scale(width: 500, height: 500);
        }

        $path = storage_path('app\\public\\avatars\\' . $filename);

        $image->toPng()->save($path);

        if ($user->UserAvatar) {
            if (!in_array($user->UserAvatar->path, ["admin.png", "customer.png", "executor.png", "no-avatar.png"])) 
            {
                Storage::disk('public')->delete('avatars/' . $user->UserAvatar->path);
            }

            $user->UserAvatar->update(['path' => $filename]);
        } 
        else 
        {
            UserAvatar::create([
                'user_id' => $user->id,
                'path' => $filename,
            ]);
        }

        return back()->with('success', 'Avatar updated successfully');
    }
}