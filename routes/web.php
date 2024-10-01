<?php

	use App\Http\Controllers\BookActionController;
	use App\Http\Controllers\DreamStudioController;
	use App\Http\Controllers\JobController;
	use App\Http\Controllers\LoginWithGoogleController;
	use App\Http\Controllers\LoginWithLineController;
	use App\Http\Controllers\ProductController;
	use App\Http\Controllers\StaticPagesController;
	use App\Http\Controllers\UserController;
	use App\Http\Controllers\UserSettingsController;
	use App\Http\Controllers\VerifyThankYouController;
	use App\Mail\ThankYouForYourOrder;
	use App\Mail\WelcomeMail;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Mail;
	use Illuminate\Support\Facades\Route;


	/*
	|--------------------------------------------------------------------------
	| Web Routes
	|--------------------------------------------------------------------------
	|
	| Here is where you can register web routes for your application. These
	| routes are loaded by the RouteServiceProvider and all of them will
	| be assigned to the "web" middleware group. Make something great!
	|
	*/

	//-------------------------------------------------------------------------
	Route::get('/', [StaticPagesController::class, 'landing'])->name('landing.page');

	Route::get('login/google', [LoginWithGoogleController::class, 'redirectToGoogle']);
	Route::get('login/google/callback', [LoginWithGoogleController::class, 'handleGoogleCallback']);

	Route::get('/logout', [LoginWithGoogleController::class, 'logout']);

	Route::get('/verify-thank-you', [VerifyThankYouController::class, 'index'])->name('verify-thank-you')->middleware('verified');
	Route::get('/verify-thank-you-zh_TW', [VerifyThankYouController::class, 'index_zh_TW'])->name('verify-thank-you-zh_TW')->middleware('verified');

	Route::get('/showcase-library/genre/{genre}', [StaticPagesController::class, 'showcaseLibrary'])->name('user.showcase-library-genre');
	Route::get('/showcase-library/keyword/{keyword}', [StaticPagesController::class, 'showcaseLibrary'])->name('user.showcase-library-keyword');
	Route::get('/showcase-library', [StaticPagesController::class, 'showcaseLibrary'])->name('user.showcase-library');
	Route::get('/book-details/{slug}', [StaticPagesController::class, 'booksDetail'])->name('user.book-details');

	Route::get('/privacy', [StaticPagesController::class, 'privacy'])->name('privacy.page');
	Route::get('/terms', [StaticPagesController::class, 'terms'])->name('terms.page');
	Route::get('/help', [StaticPagesController::class, 'help'])->name('help.page');
	Route::get('/help/{topic}', [StaticPagesController::class, 'helpDetails'])->name('help-details.page');
	Route::get('/about', [StaticPagesController::class, 'about'])->name('about.page');
	Route::get('/contact', [StaticPagesController::class, 'contact'])->name('contact.page');
	Route::get('/onboarding', [StaticPagesController::class, 'onboarding'])->name('onboarding.page');
	Route::get('/change-log', [StaticPagesController::class, 'changeLog'])->name('change-log.page');
	Route::get('/buy-packages', [UserSettingsController::class, 'buyPackages'])->name('buy.packages');

	Route::get('/help', [StaticPagesController::class, 'help'])->name('help.page');

	Route::post('/send-contact-us-email', [OrderController::class, 'sendContactUsEmail'])->name('contact.sendEmail');

	//-------------------------------------------------------------------------

	Route::get('/buy-packages', [UserSettingsController::class, 'buyPackages'])->name('buy.packages');

	Route::get('/buy-credits-test/{id}', [PayPalController::class, 'beginTransaction'])->name('beginTransaction');
	Route::get('/buy-credits/{id}', [PayPalController::class, 'processTransaction'])->name('processTransaction');
	Route::get('/success-transaction', [PayPalController::class, 'successTransaction'])->name('successTransaction');
	Route::get('/cancel-transaction', [PayPalController::class, 'cancelTransaction'])->name('cancelTransaction');

	Route::get('/writer-profile/{username}', [StaticPagesController::class, 'userProfile'])->name('user-profile');

	Route::get('/read-book/{slug}', [StaticPagesController::class, 'readBook'])->name('user.read-book');

	//-------------------------------------------------------------------------
	Route::middleware(['auth'])->group(function () {

		Route::get('/prompts/{filename}.txt', function ($filename) {
			$filePath = resource_path("prompts/{$filename}.txt");

			if (File::exists($filePath)) {
				return response()->file($filePath);
			} else {
				abort(404, 'File not found.');
			}
		});

		Route::get('/start-writing', [StaticPagesController::class, 'startWriting'])->name('start-writing');

		Route::get('/edit-book/{slug}', [StaticPagesController::class, 'editBook'])->name('user.edit-book');

		Route::get('/book-beats/{slug}/{selected_chapter}/{beats_per_chapter}', [StaticPagesController::class, 'bookBeats'])->name('user.book-beats');


		Route::post('/write-book-character-profiles', [BookActionController::class, 'writeBookCharacterProfiles'])->name('book.write-book-character-profiles');
		Route::post('/write-book', [BookActionController::class, 'writeBook'])->name('book.write-book');
		Route::post('/book/{bookSlug}/chapter', [BookActionController::class, 'saveChapter'])->name('book.save-chapter');
		Route::post('/book/{bookSlug}/cover', [BookActionController::class, 'saveCover'])->name('book.save-cover');
		Route::post('/book/{bookSlug}/details', [BookActionController::class, 'saveBookDetails'])->name('book.save-details');

		Route::post('/rewrite-chapter', [BookActionController::class, 'rewriteChapter'])->name('rewrite-chapter');
		Route::post('/accept-rewrite', [BookActionController::class, 'acceptRewrite'])->name('accept-rewrite');

		Route::delete('/book/{bookSlug}', [BookActionController::class, 'deleteBook'])->name('book.delete');

		Route::post('/send-llm-prompt/{bookSlug}', [BookActionController::class, 'sendLlmPrompt'])->name('book.send-llm-prompt');

		Route::post('/cover-image/{bookSlug}', [BookActionController::class, 'makeCoverImage'])->name('book.make-cover-image');

		Route::post('/book/write-beats/{bookSlug}/{chapterFilename}', [BookActionController::class, 'writeChapterBeats'])->name('book.write-chapter-beats');

		Route::post('/book/save-beats/{bookSlug}/{chapterFilename}', [BookActionController::class, 'saveChapterBeats'])->name('book.save-chapter-beats');

		Route::post('/book/save-single-beat/{bookSlug}/{chapterFilename}', [BookActionController::class, 'saveChapterSingleBeat'])->name('book.save-chapter-single-beats');

		Route::post('/book/write-beat-description/{bookSlug}/{chapterFilename}', [BookActionController::class, 'writeChapterBeatDescription'])->name('book.write-beat-description');

		Route::post('/book/write-beat-text/{bookSlug}/{chapterFilename}', [BookActionController::class, 'writeChapterBeatText'])->name('book.write-beat-text');

		Route::post('/book/write-beat-summary/{bookSlug}/{chapterFilename}', [BookActionController::class, 'writeChapterBeatSummary'])->name('book.write-beat-summary');

		Route::post('/book/update-beat-lore-book/{bookSlug}/{chapterFilename}', [BookActionController::class, 'updateBeatLoreBook'])->name('book.update-beat-lore-book');


		Route::get('/my-books', [StaticPagesController::class, 'myBooks'])->name('my.books');
		Route::get('/settings', [UserSettingsController::class, 'editSettings'])->name('my.settings');
		Route::post('/settings', [UserSettingsController::class, 'updateSettings'])->name('settings.update');

		Route::post('/settings/password', [UserSettingsController::class, 'updatePassword'])->name('settings.password.update');

		Route::get('/users', [UserController::class, 'index'])->name('users.index');
		Route::post('/login-as', [UserController::class, 'loginAs'])->name('users.login-as');

		Route::get('/resendConfirmEmail/{orderID}', [OrderController::class, 'resendConfirmEmail'])->name('order.resendConfirmEmail');
		Route::get('/resendConfirmEmail2/{newOrder}', [OrderController::class, 'resendConfirmEmail2'])->name('order.resendConfirmEmail2');

		Route::post('/settings/password', [UserSettingsController::class, 'updatePassword'])->name('settings.password.update');

	});

//-------------------------------------------------------------------------

	Auth::routes();
	Auth::routes(['verify' => true]);
