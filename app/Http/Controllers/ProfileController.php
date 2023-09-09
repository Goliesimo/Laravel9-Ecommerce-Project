<?php

// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $request->validate([
            'profile_picture' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_picture')) {
            $profilePicture = $request->file('profile_picture');
            $image = Image::make($profilePicture)->fit(200, 200);
            $fileName = time() . '.' . $profilePicture->getClientOriginalExtension();
            $image->save(public_path('uploads/profile_pictures/' . $fileName));

            $user->profile_picture = $fileName;
            $user->save();
        }

        return redirect('profile')->with('success', 'Profile picture updated successfully.');
    }

    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                $oldProfilePicture = public_path('uploads/profile_pictures/' . $user->profile_picture);
                if (file_exists($oldProfilePicture)) {
                    unlink($oldProfilePicture);
                }
            }

            $profilePicture = $request->file('profile_picture');
            $image = Image::make($profilePicture)->fit(200, 200);
            $fileName = time() . '.' . $profilePicture->getClientOriginalExtension();
            $image->save(public_path('uploads/profile_pictures/' . $fileName));

            $user->profile_picture = $fileName;
            $user->save();
        }

        return redirect()->back()->with('success', 'Profile picture updated successfully.');
    }
}
