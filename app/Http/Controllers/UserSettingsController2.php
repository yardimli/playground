<?php

	namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use App\Models\User;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Str;
	use Illuminate\Support\Facades\Validator;
	use App\Helpers\MyHelper;
	use Illuminate\Support\Facades\Session;
	use Illuminate\Validation\Rule;
	use Illuminate\Support\Facades\Hash;
	use Illuminate\Validation\ValidationException;


	class UserSettingsController extends Controller
	{
		//-------------------------------------------------------------------------
		// Index
		public function index(Request $request)
		{

		}


//-------------------------------------------------------------------------
		// Update user password
		public function updatePassword(Request $request)
		{
			// Get the authenticated user
			$user = $request->user();

			// Validate input
			$validator = Validator::make($request->all(), [
				'current_password' => ['nullable', 'string'],
				'new_password' => ['required', 'string', 'min:8', 'confirmed'],
			]);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}

			$current_password = $request->input('current_password') ?? '123456dummy_password';
//dd($user->password, $current_password, Hash::check($current_password, $user->password), Hash::make($current_password));
			// Check if the current password is correct
			if (!Hash::check($current_password, $user->password)) {
				throw ValidationException::withMessages([
					'current_password' => ['當前密碼輸入錯誤'],
				]);
			}

			// Update user password
			$user->password = Hash::make($request->input('new_password'));
			$user->save();

			// Redirect back with success message
			Session::flash('success', '您的密碼已成功更新');
			return redirect()->back();
		}


		//-------------------------------------------------------------------------
		// settings


		public function editSettings(Request $request)
		{
			// Get the authenticated user
			$user = $request->user();

			$user_order_and_tokens = [];

			return view('user.my-books', compact('user', 'user_order_and_tokens'));
		}


// Update user settings
		public function updateSettings(Request $request)
		{
			// Get the authenticated user
			$user = $request->user();

			// Validate input
			$validator = Validator::make($request->all(), [
				'name' => ['required', 'string', 'max:255'],
				'username' => [
					'required', 'string', 'max:255', 'alpha_dash',
					Rule::unique('users')->ignore($user->id),
				],
				'email' => [
					'required', 'string', 'email', 'max:255',
					Rule::unique('users')->ignore($user->id),
				],
				'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:1024'],
			]);

			if ($validator->fails()) {
				return redirect()->back()->withErrors($validator)->withInput();
			}

			if ($request->hasFile('avatar')) {
				$avatar = $request->file('avatar');
				$avatarContents = file_get_contents($avatar->getRealPath());
				$avatarName = $user->id . '.jpg';
				$avatarPath = 'public/user_avatars/' . $avatarName;
				Storage::put($avatarPath, $avatarContents);
				$user->avatar = $avatarPath;
			}

			// Update user
			$user->name = $request->input('name');
			$user->username = $request->input('username');
			$user->email = $request->input('email');
			$user->save();

			// Redirect back with success message
			Session::flash('success', '您的設定已成功更新');
			return redirect()->back();
		}

	}
