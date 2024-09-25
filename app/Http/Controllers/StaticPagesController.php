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

		private array $genres_array = array(
			"Action",
			"Biography",
			"Body, Mind & Spirit",
			"Business & Economics",
			"Education",
			"Family & Relationships",
			"Health & Fitness",
			"Romance",
			"Young Adult",
			"Horror",
			"Fantasy",
			"Realistic",
			"LGBTQ+",
			"Science Fiction",
			"Dark Humor",
			"Mystery",
			"Thriller",
			"Historical",
			"Paranormal",
			"Adventure",
			"Crime",
			"Children's Literature",
			"Steampunk",
			"Chick Lit",
			"Post-Apocalyptic",
			"Humor",
			"Drama",
			"Western",
			"Space Opera"
		);

		private array $adult_genres_array = array(
			"Contemporary Erotica", "Paranormal Erotica", "BDSM Erotica", "Romance Erotica", "LGBT+ Erotica", "Erotic Thriller", "Erotic Fantasy", "Taboo/Forbidden romance", "Polyamory/Menage", "Erotic Science Fiction", "Erotic Mystery", "Erotic Horror", "Erotic Comedy", "Erotic Steampunk", "Erotic Sword and Sorcery", "Erotic Noir");

		private array $writingStyles = [
			[
				"value" => "Descriptive",
				"label" => "Descriptive - Rich, detailed, and imaginative language",
			],
			[
				"value" => "Expository",
				"label" => "Expository - Informative and straightforward writing style",
			],
			[
				"value" => "Narrative",
				"label" => "Narrative - Tells a story through a series of events",
			],
			[
				"value" => "Persuasive",
				"label" => "Persuasive - Aims to convince the reader of a certain viewpoint",
			],
			[
				"value" => "Argumentative",
				"label" => "Argumentative - Presents a debatable issue with a clear position",
			],
			[
				"value" => "Stream Of Consciousness",
				"label" => "Stream Of Consciousness",
			],
			[
				"value" => "Satirical",
				"label" => "Satirical",
			],
			[
				"value" => "Minimalist",
				"label" => "Minimalist - Uses simple, concise language and avoids unnecessary detail or embellishment",
			]
		];

		private array $narrativeStyles = [
			[
				"value" => "First Person - The story is told from the perspective of a single character using \"I\" or \"we\" pronouns",
				"label" => "<span style=\"font-weight: bold;\">First-person:</span><br>The story is told from the perspective of a single character using \"I\" or \"we\" pronouns, providing direct access to the character's thoughts and feelings.",
			],
			[
				"value" => "Second Person - The narrator addresses the reader directly using \"you\" pronouns",
				"label" => "<span style=\"font-weight: bold;\">Second-person:</span><br>The narrator addresses the reader directly using \"you\" pronouns, engaging the reader as an active participant in the story.",
			],
			[
				"value" => "Third Person - The narrator has a godlike perspective",
				"label" => "<span style=\"font-weight: bold;\">Third-person Omniscient:</span><br>The narrator has a godlike perspective, knowing all characters' thoughts and feelings, as well as past, present, and future events.",
			],
		];

		//-------------------------------------------------------------------------
		// Index
		public function index(Request $request)
		{
			$posts = MyHelper::getBlogData();
			$locale = \App::getLocale() ?: config('app.fallback_locale', 'zh_TW');
			$json_translations = $this->write_js_translations();

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view("playground.index", compact('posts', 'locale', 'json_translations', 'genres_array', 'adult_genres_array'));

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

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view("playground.single-post", compact('blog_post', 'locale', 'genres_array', 'adult_genres_array'));
		}

		public function blog(Request $request)
		{
			$posts = MyHelper::getBlogData();

			// Return to the existing blog list view with the posts

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view("playground.blog-listing", compact('posts', 'genres_array', 'adult_genres_array'));

		}

		public function about_us(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view("playground.about-us", compact('posts', 'genres_array', 'adult_genres_array'));

		}

		public function faq(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view("playground.faq", compact('posts', 'genres_array', 'adult_genres_array'));
		}

		public function blogGrid(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view("playground.blog-grid", compact('posts', 'genres_array', 'adult_genres_array'));

		}

		public function blogDetail(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view("playground.blog-detail", compact('posts', 'genres_array', 'adult_genres_array'));

		}

		public function myProfile(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view("playground.my-profile", compact('posts', 'genres_array', 'adult_genres_array'));

		}

		public function help(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view("playground.help-center", compact('posts', 'genres_array', 'adult_genres_array'));

		}

		public function contact_us(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view("playground.contact-us", compact('posts', 'genres_array', 'adult_genres_array'));

		}

		public function privacy(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view("playground.privacy-policy", compact('posts', 'genres_array', 'adult_genres_array'));

		}

		public function terms(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view("playground.terms", compact('posts', 'genres_array', 'adult_genres_array'));

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

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view('playground.book-details', compact('locale', 'book', 'json_translations', 'book_slug', 'colorOptions', 'genres_array', 'adult_genres_array'));
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

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view('playground.all-books', compact('locale', 'json_translations', 'books', 'genres_array', 'adult_genres_array'));
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

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;


			return view('playground.books-list', compact('locale', 'json_translations', 'paginatedBooks', 'genres_array', 'adult_genres_array'));
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

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;

			return view('playground.books-detail', compact('locale', 'book', 'json_translations', 'book_slug', 'colorOptions', 'genres_array', 'adult_genres_array'));
		}

		public function startWriting(Request $request)
		{
			$random_int = rand(1, 16);
			$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';

			$genres_array = $this->genres_array;
			$adult_genres_array = $this->adult_genres_array;
			$writingStyles = $this->writingStyles;
			$narrativeStyles = $this->narrativeStyles;

			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts
			return view("playground.start-writing", compact('posts', 'coverFilename', 'adult_genres_array', 'genres_array', 'writingStyles', 'narrativeStyles'));

		}

		public function bookBeats(Request $request, $bookSlug, $selectedChapter = 'all-chapters', $beatsPerChapter = 3)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return redirect()->route('playground.books')->with('error', $verified['message']);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookData = json_decode(File::get("{$bookPath}/book.json"), true);
			$actsData = json_decode(File::get("{$bookPath}/acts.json"), true);

			$beatsPerChapter = intval($beatsPerChapter);

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
					if (!isset($chapterData['beats'])) {
						$chapterData['beats'] = [];
						//create 3 empty beats
						for ($i = 0; $i < $beatsPerChapter; $i++) {
							$chapterData['beats'][] = [
								'description' => '',
								'beat_text' => '',
								'beat_summary' => '',
							];
						}
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

			$selectedChapterIndex = 0;

			if ($selectedChapter !== 'all-chapters') {

				foreach ($bookData['acts'] as $act) {
					foreach ($act['chapters'] as $index => $chapter) {
						$selectedChapterIndex++;
						if ($chapter['chapterFilename'] === $selectedChapter . '.json') {
							break 2;
						}
					}
				}

				// Filter to only include the specified chapter
				foreach ($bookData['acts'] as &$act) {
					$act['chapters'] = array_filter($act['chapters'], function ($chapter) use ($selectedChapter) {
						return $chapter['chapterFilename'] === $selectedChapter . '.json';
					});
				}
				// Remove acts with no chapters
				$bookData['acts'] = array_filter($bookData['acts'], function ($act) {
					return !empty($act['chapters']);
				});
			}

			foreach ($bookData['acts'] as &$act) {
				foreach ($act['chapters'] as &$chapter) {
					if (array_key_exists('beats', $chapter)) {
						foreach ($chapter['beats'] as &$beat) {
							foreach ($beat as $key => &$content) {
								if (is_string($content)) {
									$content = str_replace("<BR><BR>", "\n", $content);
									$content = str_replace("<BR>", "\n", $content);
								}
							}
						}
					}
				}
			}

			if ($selectedChapter === 'all-chapters') {
				$selectedChapter = '';
			}

			return view('playground.all-beats', [
				'book' => $bookData,
				'book_slug' => $bookSlug,
				'selected_chapter' => $selectedChapter ?? '',
				'selected_chapter_index' => $selectedChapterIndex,
				'json_translations' => $this->write_js_translations(),
			]);
		}

	}
