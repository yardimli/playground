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

	Route::get('/all-books', [StaticPagesController::class, 'allBooks'])->name('playground.all-books');
	Route::get('/book-details/{slug}', [StaticPagesController::class, 'bookDetails'])->name('playground.book-details');
	Route::get('/book-beats/{slug}/{chapter_file}', [StaticPagesController::class, 'bookBeats'])->name('playground.book-beats');
	Route::get('/book-all-beats/{slug}', [StaticPagesController::class, 'showAllBeats'])->name('playground.book-all-beats');

	Route::get('/', [StaticPagesController::class, 'index'])->name('index');

	Route::get('/books-list', [StaticPagesController::class, 'booksList'])->name('playground.books-list');
	Route::get('/books-detail/{slug}', [StaticPagesController::class, 'booksDetail'])->name('playground.books-detail');

	Route::get('/my-profile', [StaticPagesController::class, 'myProfile'])->name('my-profile');
	Route::get('/start-writing', [StaticPagesController::class, 'startWriting'])->name('start-writing');

	Route::get('/faq', [StaticPagesController::class, 'faq'])->name('faq');
	Route::get('/blog-grid', [StaticPagesController::class, 'blogGrid'])->name('blog-grid');
	Route::get('/blog-detail', [StaticPagesController::class, 'blogDetail'])->name('blog-detail');

	Route::get('/privacy-policy', [StaticPagesController::class, 'privacy'])->name('privacy-policy');
	Route::get('/terms', [StaticPagesController::class, 'terms'])->name('terms');
	Route::get('/contact-us', [StaticPagesController::class, 'contact_us'])->name('contact-us');
	Route::get('/about-us', [StaticPagesController::class, 'about_us'])->name('about-us');

	Route::get('/blog', [StaticPagesController::class, 'blogV2'])->name('blog');
	Route::get('/blog-article/{slug}', [StaticPagesController::class, 'blogArticleV2'])->name('blog-article');
	Route::get('/help', [StaticPagesController::class, 'help'])->name('help.page');

//	Route::get('/login', [StaticPagesController::class, 'login'])->name('login');
//	Route::get('/signup', [StaticPagesController::class, 'signup'])->name('signup');

	Route::get('/products', [ProductController::class, 'index'])->name('new-products.page');
	Route::get('/show-packages', [OrderController::class, 'showPackagesV2'])->name('show.packages');

	Route::get('login/google', [LoginWithGoogleController::class, 'redirectToGoogle']);
	Route::get('login/google/callback', [LoginWithGoogleController::class, 'handleGoogleCallback']);

	Route::get('/logout', [LoginWithGoogleController::class, 'logout']);

	Route::get('/test-email', function () {
		Mail::to('support@playground.computer')->send(new WelcomeMail('Test123', 'support@playground.computer'));
		return 'Email has been sent';
	});

	Route::get('/email/verification-notification', function (Request $request) {
		$request->user()->sendEmailVerificationNotification();
		return back()->with('resent', true);
	})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

	Route::get('/verify-thank-you', [VerifyThankYouController::class, 'index'])->name('verify-thank-you')->middleware('verified');
	Route::get('/verify-thank-you-zh_TW', [VerifyThankYouController::class, 'index_zh_TW'])->name('verify-thank-you-zh_TW')->middleware('verified');

	Route::post('/send-contact-us-email', [OrderController::class, 'sendContactUsEmail'])->name('contact.sendEmail');

	//-------------------------------------------------------------------------



	Route::post('/write-book-character-profiles', [BookActionController::class, 'writeBookCharacterProfiles'])->name('book.write-book-character-profiles');
	Route::post('/write-book', [BookActionController::class, 'writeBook'])->name('book.write-book');
	Route::post('/book/{bookSlug}/chapter', [BookActionController::class, 'saveChapter'])->name('book.save-chapter');
	Route::post('/book/{bookSlug}/cover', [BookActionController::class, 'saveCover'])->name('book.save-cover');
	Route::delete('/book/{bookSlug}', [BookActionController::class, 'deleteBook'])->name('book.delete');

	Route::post('/cover-image/{bookSlug}', [BookActionController::class, 'makeCoverImage'])->name('book.make-cover-image');

	Route::post('/book/write-beats/{bookSlug}/{chapterFilename}', [BookActionController::class, 'writeChapterBeats'])->name('book.write-chapter-beats');

	Route::post('/book/save-beats/{bookSlug}/{chapterFilename}', [BookActionController::class, 'saveChapterBeats'])->name('book.save-chapter-beats');

	Route::post('/book/save-single-beat/{bookSlug}/{chapterFilename}', [BookActionController::class, 'saveChapterSingleBeat'])->name('book.save-chapter-single-beats');

	Route::post('/book/write-beat-text/{bookSlug}/{chapterFilename}', [BookActionController::class, 'writeChapterBeatText'])->name('book.write-beat-text');

	Route::post('/book/write-beat-summary/{bookSlug}/{chapterFilename}', [BookActionController::class, 'writeChapterBeatSummary'])->name('book.write-beat-summary');


	Route::middleware(['auth'])->group(function () {

		Route::get('/resendConfirmEmail/{orderID}', [OrderController::class, 'resendConfirmEmail'])->name('order.resendConfirmEmail');
		Route::get('/resendConfirmEmail2/{newOrder}', [OrderController::class, 'resendConfirmEmail2'])->name('order.resendConfirmEmail2');

		Route::post('/settings/password', [UserSettingsController::class, 'updatePassword'])->name('settings.password.update');

	});

//-------------------------------------------------------------------------

	Auth::routes();
	Auth::routes(['verify' => true]);
