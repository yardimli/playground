<?php

	namespace App\Http\Controllers;

	use Carbon\Carbon;
	use Illuminate\Http\Request;
	use App\Models\User;
	use App\Models\NewOrder;
	use App\Models\NewOrderItem;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\File;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Str;
	use Illuminate\Support\Facades\Validator;
	use App\Helpers\MyHelper;
	use Illuminate\Support\Facades\Session;
	use Illuminate\Validation\Rule;
	use Illuminate\Support\Facades\Hash;
	use Illuminate\Validation\ValidationException;
	use Illuminate\Pagination\LengthAwarePaginator;

	use BinshopsBlog\Models\BinshopsCategory;
	use BinshopsBlog\Models\BinshopsCategoryTranslation;
	use BinshopsBlog\Models\BinshopsLanguage;
	use BinshopsBlog\Models\BinshopsPostTranslation;


	class StaticPagesController extends Controller
	{
		//-------------------------------------------------------------------------
		// Index
		public function index(Request $request)
		{
			$posts = MyHelper::getBlogData();
			$locale = \App::getLocale() ?: config('app.fallback_locale', 'zh_TW');
			$json_translations = $this->write_js_translations();

			return view("playground.index", compact('posts', 'locale', 'json_translations'));

		}

		public function blogArticle(Request $request, $slug)
		{
			$locale = \App::getLocale() ?: config('app.fallback_locale', 'zh_TW');

			$blog_post = BinshopsPostTranslation::where("slug", $slug)->first(); //->where('lang_id', 2)->first();

			$blog_post->category_name = '';
			//get post categories
			$categories = BinshopsCategory::join('binshops_post_categories', 'binshops_categories.id', '=', 'binshops_post_categories.category_id')
				->where('binshops_post_categories.post_id', $blog_post->post_id)
				->get();

			//get category translations
			$categories = json_decode(json_encode($categories), true);
			foreach ($categories as $category) {
				if ($blog_post->category_name == '' || $blog_post->category_name == null) {
					$blog_post->category_name = BinshopsCategoryTranslation::where('category_id', $category['category_id'])->first()->category_name ?? '';
				}
			}
			return view("playground.single-post", compact('blog_post'));
		}

		public function blog(Request $request)
		{
			$posts = MyHelper::getBlogData();

			// Return to the existing blog list view with the posts
			return view("playground.blog-listing", compact('posts'));

		}

		public function about_us(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts
			return view("playground.about-us", compact('posts'));

		}

		public function faq(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts
			return view("playground.faq", compact('posts'));
		}

		public function blogGrid(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts
			return view("playground.blog-grid", compact('posts'));

		}

		public function blogDetail(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts
			return view("playground.blog-detail", compact('posts'));

		}

		public function myProfile(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts
			return view("playground.my-profile", compact('posts'));

		}

		public function help(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts
			return view("playground.help-center", compact('posts'));

		}

		public function contact_us(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts
			return view("playground.contact-us", compact('posts'));

		}

		public function privacy(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts
			return view("playground.privacy-policy", compact('posts'));

		}

		public function terms(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts
			return view("playground.terms", compact('posts'));

		}

		//------------------------------------------------------------------------------

		private function write_js_translations()
		{
			$locale = app()->getLocale(); // Get the current locale
			$translations = [];

			// Get all translation files for the current locale
			$path = base_path("lang/{$locale}");

			if (is_dir($path)) {
				$files = glob("{$path}/*.php");
				foreach ($files as $file) {
					$filename = basename($file, '.php');
					$translations = array_merge($translations, trans($filename));
				}
			}

			$output = "\nconst translations = " . json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ";\n";

			return $output;
		}

		public function bookDetails(Request $request, $slug)
		{
			$locale = \App::getLocale() ?: config('app.fallback_locale', 'zh_TW');
			$json_translations = $this->write_js_translations();

			$bookPath = Storage::disk('public')->path("books/{$slug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsFile = "{$bookPath}/acts.json";

			if (!File::exists($bookJsonPath) || !File::exists($actsFile)) {
				return response()->json(['success' => false, 'message' => __('Book not found ' . $bookJsonPath)], 404);
			}

			$book = json_decode(File::get($bookJsonPath), true);

			//search $book['owner'] in users table name column
			$user = User::where('email', ($book['owner'] ?? 'admin'))->first();
			if ($user) {
				$book['owner'] = $user->name;
			}

			$actsData = json_decode(File::get($actsFile), true);

			$acts = [];
			foreach ($actsData as $act) {
				$actChapters = [];
				$chapterFiles = File::glob("{$bookPath}/*.json");
				foreach ($chapterFiles as $chapterFile) {
					$chapterData = json_decode(File::get($chapterFile), true);
					if (!isset($chapterData['row'])) {
						continue;
					}
					$chapterData['chapterFilename'] = basename($chapterFile);

					if ($chapterData['row'] === $act['id']) {
						$actChapters[] = $chapterData;

					}
				}

				usort($actChapters, fn($a, $b) => $a['order'] - $b['order']);
				$acts[] = [
					'id' => $act['id'],
					'title' => $act['title'],
					'chapters' => $actChapters
				];
			}


			$random_int = rand(1, 16);
			$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';
			$book['cover_filename'] = $book['cover_filename'] ?? '';

			$book_slug = $slug;

			if ($book['cover_filename'] && file_exists(Storage::disk('public')->path("ai-images/" . $book['cover_filename']))) {
				$coverFilename = asset("storage/ai-images/" . $book['cover_filename']);
			}

			$book['cover_filename'] = $coverFilename;

			$book['acts'] = $acts;

			$colorOptions = [
				['background' => '#F28B82', 'text' => '#000000'],
				['background' => '#FBBC04', 'text' => '#000000'],
				['background' => '#FFF475', 'text' => '#000000'],
				['background' => '#CCFF90', 'text' => '#000000'],
				['background' => '#A7FFEB', 'text' => '#000000'],
				['background' => '#CBF0F8', 'text' => '#000000'],
				['background' => '#AECBFA', 'text' => '#000000'],
				['background' => '#D7AEFB', 'text' => '#000000'],
				['background' => '#FDCFE8', 'text' => '#000000'],
				['background' => '#E6C9A8', 'text' => '#000000'],
				['background' => '#E8EAED', 'text' => '#000000'],
				['background' => '#FFFFFF', 'text' => '#000000']
			];


			return view('playground.book-details', compact('locale', 'book', 'json_translations', 'book_slug', 'colorOptions'));
		}

		public function bookBeats(Request $request, $slug, $chapter_file)
		{
			$locale = \App::getLocale() ?: config('app.fallback_locale', 'zh_TW');
			$json_translations = $this->write_js_translations();

			$bookPath = Storage::disk('public')->path("books/{$slug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsFile = "{$bookPath}/acts.json";

			if (!File::exists($bookJsonPath) || !File::exists($actsFile)) {
				return response()->json(['success' => false, 'message' => __('Book not found ' . $bookJsonPath)], 404);
			}

			$book = json_decode(File::get($bookJsonPath), true);

			//search $book['owner'] in users table name column
			$user = User::where('email', ($book['owner'] ?? 'admin'))->first();
			if ($user) {
				$book['owner'] = $user->name;
			}

			$actsData = json_decode(File::get($actsFile), true);

			$acts = [];
			foreach ($actsData as $act) {
				$actChapters = [];
				$chapterFiles = File::glob("{$bookPath}/*.json");
				foreach ($chapterFiles as $chapterFile) {
					$chapterData = json_decode(File::get($chapterFile), true);
					if (!isset($chapterData['row'])) {
						continue;
					}
					$chapterData['chapterFilename'] = basename($chapterFile);

					if ($chapterData['row'] === $act['id']) {
						$actChapters[] = $chapterData;
					}
				}

				usort($actChapters, fn($a, $b) => $a['order'] - $b['order']);
				$acts[] = [
					'id' => $act['id'],
					'title' => $act['title'],
					'chapters' => $actChapters
				];
			}

			$current_chapter = [];
			$previous_chapter = [];
			$next_chapter = [];
			foreach ($acts as $act) {
				foreach ($act['chapters'] as $chapter) {
					if ($current_chapter && !$next_chapter) {
						$next_chapter = $chapter;
						break;
					}

					if ($chapter['chapterFilename'] === $chapter_file . '.json') {
						$current_chapter = $chapter;
					}

					if (!$current_chapter) {
						$previous_chapter = $chapter;
					}

				}
			}
			if (!key_exists('beats', $current_chapter)) {
				$current_chapter['beats'] = [];
			}
			if (!key_exists('beats', $previous_chapter)) {
				$previous_chapter['beats'] = [];
			}
			if (!key_exists('beats', $next_chapter)) {
				$next_chapter['beats'] = [];
			}

			$next_chapter_text = $current_chapter['to_next_chapter'] ?? '';
			if ($next_chapter) {
				$next_chapter_text = ($next_chapter['name'] ?? '') . ' - ' . ($next_chapter['short_description'] ?? '');
			}

			$previous_chapter_text = $current_chapter['to_previous_chapter'] ?? __('default.Start of the book');
			if ($previous_chapter) {
				$previous_chapter_text = ($previous_chapter['name'] ?? '') . ' - ' . ($previous_chapter['short_description'] ?? '');
			}

			$random_int = rand(1, 16);
			$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';
			$book['cover_filename'] = $book['cover_filename'] ?? '';

			$book_slug = $slug;

			if ($book['cover_filename'] && file_exists(Storage::disk('public')->path("ai-images/" . $book['cover_filename']))) {
				$coverFilename = asset("storage/ai-images/" . $book['cover_filename']);
			}

			$book['cover_filename'] = $coverFilename;

			$book['acts'] = $acts;

			return view('playground.book-beats', compact('locale', 'book', 'json_translations', 'book_slug', 'chapter_file', 'current_chapter', 'next_chapter', 'previous_chapter', 'next_chapter_text', 'previous_chapter_text'));
		}

		public function allBooks(Request $request)
		{
			$locale = \App::getLocale() ?: config('app.fallback_locale', 'zh_TW');
			$json_translations = $this->write_js_translations();

			$booksDir = Storage::disk('public')->path('books');

			$books = [];
			if ($handle = opendir($booksDir)) {
				while (false !== ($subDir = readdir($handle))) {
					if ($subDir !== '.' && $subDir !== '..') {
						$bookJsonPath = "$booksDir/$subDir/book.json";
						if (file_exists($bookJsonPath)) {
							$bookJson = file_get_contents($bookJsonPath);
							$bookData = json_decode($bookJson, true);
							if ($bookData) {

								//search $book['owner'] in users table name column
								$user = User::where('email', ($bookData['owner'] ?? 'admin'))->first();
								if ($user) {
									$bookData['owner'] = $user->name;
								}

								$random_int = rand(1, 16);
								$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';
								$bookData['cover_filename'] = $bookData['cover_filename'] ?? '';

								if ($bookData['cover_filename'] && file_exists(Storage::disk('public')->path("ai-images/" . $bookData['cover_filename']))) {
									$coverFilename = asset("storage/ai-images/" . $bookData['cover_filename']);
								}

								$books[] = [
									'id' => $subDir,
									'title' => $bookData['title'],
									'blurb' => $bookData['blurb'],
									'owner' => $bookData['owner'] ?? 'admin',
									'back_cover_text' => $bookData['back_cover_text'],
									'cover_filename' => $coverFilename,
									'file_time' => filemtime($bookJsonPath)
								];
							}
						}
					}
				}
				closedir($handle);
			}

			usort($books, function ($a, $b) {
				return $b['file_time'] - $a['file_time'];
			});

			//remove books whose owner is not the current user or admin
			$books = array_filter($books, function ($book) {
				return (Auth::user() && (($book['owner'] ?? '') === Auth::user()->email || Auth::user()->isAdmin())) || (($book['owner'] ?? '') === 'admin');
			});


			return view('playground.all-books', compact('locale', 'json_translations', 'books'));
		}

		public function booksList(Request $request)
		{
			$locale = \App::getLocale() ?: config('app.fallback_locale', 'zh_TW');
			$json_translations = $this->write_js_translations();

			$booksDir = Storage::disk('public')->path('books');

			$books = [];
			if ($handle = opendir($booksDir)) {
				while (false !== ($subDir = readdir($handle))) {
					if ($subDir !== '.' && $subDir !== '..') {
						$bookJsonPath = "$booksDir/$subDir/book.json";
						if (file_exists($bookJsonPath)) {
							$bookJson = file_get_contents($bookJsonPath);
							$bookData = json_decode($bookJson, true);
							if ($bookData) {
								$random_int = rand(1, 16);
								$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';
								$bookData['cover_filename'] = $bookData['cover_filename'] ?? '';

								if ($bookData['cover_filename'] && file_exists(Storage::disk('public')->path("ai-images/" . $bookData['cover_filename']))) {
									$coverFilename = asset("storage/ai-images/" . $bookData['cover_filename']);
								}

								//search $book['owner'] in users table name column
								$user = User::where('email', ($bookData['owner'] ?? 'admin'))->first();
								if ($user) {
									$bookData['owner'] = $user->name;
								}


								$books[] = [
									'id' => $subDir,
									'title' => $bookData['title'],
									'blurb' => $bookData['blurb'],
									'owner' => $bookData['owner'] ?? 'admin',
									'back_cover_text' => $bookData['back_cover_text'],
									'cover_filename' => $coverFilename,
									'file_time' => filemtime($bookJsonPath)
								];
							}
						}
					}
				}
				closedir($handle);
			}

			usort($books, function ($a, $b) {
				return $b['file_time'] - $a['file_time'];
			});

			//remove books whose owner is not the current user or admin
			$books = array_filter($books, function ($book) {
				return (Auth::user() && (($book['owner'] ?? '') === Auth::user()->email || Auth::user()->isAdmin())) || (($book['owner'] ?? '') === 'admin');
			});


			$perPage = 12; // Number of items per page
			$currentPage = $request->input('page', 1);
			$offset = ($currentPage - 1) * $perPage;

			$paginatedBooks = new LengthAwarePaginator(
				array_slice($books, $offset, $perPage),
				count($books),
				$perPage,
				$currentPage,
				['path' => $request->url(), 'query' => $request->query()]
			);

			return view('playground.books-list', compact('locale', 'json_translations', 'paginatedBooks'));
		}

		public function booksDetail(Request $request, $slug)
		{
			$locale = \App::getLocale() ?: config('app.fallback_locale', 'zh_TW');
			$json_translations = $this->write_js_translations();

			$bookPath = Storage::disk('public')->path("books/{$slug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsFile = "{$bookPath}/acts.json";

			if (!File::exists($bookJsonPath) || !File::exists($actsFile)) {
				return response()->json(['success' => false, 'message' => __('Book not found ' . $bookJsonPath)], 404);
			}

			$book = json_decode(File::get($bookJsonPath), true);

			//search $book['owner'] in users table name column
			$user = User::where('email', ($book['owner'] ?? 'admin'))->first();
			if ($user) {
				$book['owner'] = $user->name;
			}

			$actsData = json_decode(File::get($actsFile), true);

			$acts = [];
			foreach ($actsData as $act) {
				$actChapters = [];
				$chapterFiles = File::glob("{$bookPath}/*.json");
				foreach ($chapterFiles as $chapterFile) {
					$chapterData = json_decode(File::get($chapterFile), true);
					if (!isset($chapterData['row'])) {
						continue;
					}
					$chapterData['chapterFilename'] = basename($chapterFile);

					if ($chapterData['row'] === $act['id']) {
						$actChapters[] = $chapterData;

					}
				}

				usort($actChapters, fn($a, $b) => $a['order'] - $b['order']);
				$acts[] = [
					'id' => $act['id'],
					'title' => $act['title'],
					'chapters' => $actChapters
				];
			}


			$random_int = rand(1, 16);
			$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';
			$book['cover_filename'] = $book['cover_filename'] ?? '';

			$book_slug = $slug;

			if ($book['cover_filename'] && file_exists(Storage::disk('public')->path("ai-images/" . $book['cover_filename']))) {
				$coverFilename = asset("storage/ai-images/" . $book['cover_filename']);
			}

			$book['cover_filename'] = $coverFilename;

			$book['acts'] = $acts;

			$colorOptions = [
				['background' => '#F28B82', 'text' => '#000000'],
				['background' => '#FBBC04', 'text' => '#000000'],
				['background' => '#FFF475', 'text' => '#000000'],
				['background' => '#CCFF90', 'text' => '#000000'],
				['background' => '#A7FFEB', 'text' => '#000000'],
				['background' => '#CBF0F8', 'text' => '#000000'],
				['background' => '#AECBFA', 'text' => '#000000'],
				['background' => '#D7AEFB', 'text' => '#000000'],
				['background' => '#FDCFE8', 'text' => '#000000'],
				['background' => '#E6C9A8', 'text' => '#000000'],
				['background' => '#E8EAED', 'text' => '#000000'],
				['background' => '#FFFFFF', 'text' => '#000000']
			];


			return view('playground.books-detail', compact('locale', 'book', 'json_translations', 'book_slug', 'colorOptions'));
		}

		public function startWriting(Request $request)
		{
			$random_int = rand(1, 16);
			$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';

			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts
			return view("playground.start-writing", compact('posts', 'coverFilename'));

		}

		public function showAllBeats($bookSlug)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return redirect()->route('playground.books')->with('error', $verified['message']);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookData = json_decode(File::get("{$bookPath}/book.json"), true);
			$actsData = json_decode(File::get("{$bookPath}/acts.json"), true);

			// Load all chapters and their beats
			$acts = [];
			foreach ($actsData as $act) {
				$actChapters = [];
				$chapterFiles = File::glob("{$bookPath}/*.json");
				foreach ($chapterFiles as $chapterFile) {
					$chapterData = json_decode(File::get($chapterFile), true);
					if (!isset($chapterData['row']) || $chapterData['row'] !== $act['id']) {
						continue;
					}
					$chapterData['chapterFilename'] = basename($chapterFile);
					$actChapters[] = $chapterData;
				}
				usort($actChapters, fn($a, $b) => $a['order'] - $b['order']);
				$acts[] = [
					'id' => $act['id'],
					'title' => $act['title'],
					'chapters' => $actChapters
				];
			}

			$bookData['acts'] = $acts;

			$random_int = rand(1, 16);
			$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';
			$bookData['cover_filename'] = $bookData['cover_filename'] ?? '';

			if ($bookData['cover_filename'] && file_exists(Storage::disk('public')->path("ai-images/" . $bookData['cover_filename']))) {
				$coverFilename = asset("storage/ai-images/" . $bookData['cover_filename']);
			}

			$bookData['cover_filename'] = $coverFilename;

			return view('playground.all-beats', [
				'book' => $bookData,
				'book_slug' => $bookSlug,
				'json_translations' => $this->write_js_translations(),
			]);
		}

	}
