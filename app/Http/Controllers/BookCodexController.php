<?php

	namespace App\Http\Controllers;

	use App\Models\SentencesTable;
	use GuzzleHttp\Client;
	use GuzzleHttp\Exception\ClientException;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\File;
	use App\Helpers\MyHelper;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Str;

	class BookCodexController extends Controller
	{
		public function updateCodex($llm, $chapterFilename, $bookSlug, $codexPart, $existingCodexPart, $currentBeatDescription, $currentBeatText)
		{
			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsJsonPath = "{$bookPath}/acts.json";

			$bookData = json_decode(File::get($bookJsonPath), true);
			$actsData = json_decode(File::get($actsJsonPath), true);

			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
			} else {
				$model = $llm;
			}

			// Reconstruct the chapter structure
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

			// Find current, previous, and next chapters
			$current_chapter = null;
			$previous_chapter = null;
			$next_chapter = null;
			foreach ($acts as $act) {
				foreach ($act['chapters'] as $chapter) {
					if ($current_chapter && !$next_chapter) {
						$next_chapter = $chapter;
						break;
					}

					if ($chapter['chapterFilename'] === $chapterFilename) {
						$current_chapter = $chapter;
					}

					if (!$current_chapter) {
						$previous_chapter = $chapter;
					}
				}
			}

			// Load the beat prompt template
			if ($codexPart === 'characters') {
				$beatPromptTemplate = File::get(resource_path('prompts/codex_characters.txt'));
			} elseif ($codexPart === 'locations') {
				$beatPromptTemplate = File::get(resource_path('prompts/codex_locations.txt'));
			} elseif ($codexPart === 'objects') {
				$beatPromptTemplate = File::get(resource_path('prompts/codex_objects.txt'));
			} elseif ($codexPart === 'lore') {
				$beatPromptTemplate = File::get(resource_path('prompts/codex_lore.txt'));
			}

			if (isset($current_chapter['events']) && is_array($current_chapter['events'])) {
				$current_chapter['events'] = implode("\n", $current_chapter['events']);
			}
			if (isset($current_chapter['places']) && is_array($current_chapter['places'])) {
				$current_chapter['places'] = implode("\n", $current_chapter['places']);
			}
			if (isset($current_chapter['people']) && is_array($current_chapter['people'])) {
				$current_chapter['people'] = implode("\n", $current_chapter['people']);
			}

			$replacements = [
				'##book_title##' => $bookData['title'] ?? 'no title',
				'##back_cover_text##' => $bookData['back_cover_text'] ?? 'no back cover text',
				'##book_blurb##' => $bookData['blurb'] ?? 'no blurb',
				'##language##' => $bookData['language'] ?? 'English',
				'##act##' => $current_chapter['row'] ?? 'no act',
				'##chapter##' => $current_chapter['name'] ?? 'no name',
				'##description##' => $current_chapter['short_description'] ?? 'no description',
				'##events##' => $current_chapter['events'] ?? 'no events',
				'##people##' => $current_chapter['people'] ?? 'no people',
				'##places##' => $current_chapter['places'] ?? 'no places',
				'##beat_summary##' => $currentBeatDescription ?? '',
				'##beat_text##' => $currentBeatText ?? '',
				'##previous_codex_part##' => $existingCodexPart,
			];

			$beatPrompt = str_replace(array_keys($replacements), array_values($replacements), $beatPromptTemplate);

			$resultData = MyHelper::llm_no_tool_call($llm, '', '', $beatPrompt, false);


			return $resultData;
		}

		public function saveCodex(Request $request, $bookSlug)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json(['success' => false, 'message' => $verified['message']]);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");

			$bookJsonPath = "{$bookPath}/book.json";
			$bookData = json_decode(File::get($bookJsonPath), true);

			$codex_beats = $request->input('beats', []) ?? [];

			$codexData = [
				'characters' => $request->input('characters', []),
				'locations' => $request->input('locations', []),
				'objects' => $request->input('objects', []),
				'lore' => $request->input('lore', []),
				'beats' => $codex_beats
			];

			$bookData['codex'] = $codexData;
			File::put($bookJsonPath, json_encode($bookData, JSON_PRETTY_PRINT));

			return response()->json(['success' => true, 'message' => __('default.Codex saved successfully')]);
		}

		public function showCodex($bookSlug)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return redirect()->route('user.showcase-library')->with('error', $verified['message']);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsFile = "{$bookPath}/acts.json";

			if (!File::exists($bookJsonPath) || !File::exists($actsFile)) {
				return response()->json(['success' => false, 'message' => __('Book not found ' . $bookJsonPath)], 404);
			}

			$bookData = json_decode(File::get($bookJsonPath), true);
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
					//if chapterData['events'] is an array, convert it to a string
					if (isset($chapterData['events']) && is_array($chapterData['events'])) {
						$chapterData['events'] = implode("\n", $chapterData['events']);
					}
					//places
					if (isset($chapterData['places']) && is_array($chapterData['places'])) {
						$chapterData['places'] = implode("\n", $chapterData['places']);
					}
					//people
					if (isset($chapterData['people']) && is_array($chapterData['people'])) {
						$chapterData['people'] = implode("\n", $chapterData['people']);
					}

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

			$bookData['acts'] = $acts;


			if (!isset($bookData['codex']) || !$bookData['codex']) {
				$codexData = [
					'characters' => "",
					'locations' => "",
					'objects' => "",
					'lore' => ""
				];
				$bookData['codex'] = $codexData;
			}

			return view('user.codex', compact('bookSlug', 'bookData'));
		}

		public function updateCodexFromBeat(Request $request, $bookSlug)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json(['success' => false, 'message' => $verified['message']]);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$bookData = json_decode(File::get($bookJsonPath), true);

			$chapterFilename = $request->input('chapterFilename');
			$beatIndex = $request->input('beatIndex');
			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');

			$chapterPath = "{$bookPath}/{$chapterFilename}";
			$chapterData = json_decode(File::get($chapterPath), true);

			$beatDescription = $chapterData['beats'][$beatIndex]['description'] ?? '';
			$beatText = $chapterData['beats'][$beatIndex]['beat_text'] ?? '';

			$codex_lore_results = $this->updateCodex($llm, $chapterFilename, $bookSlug, 'lore', $bookData['codex']['lore'] ?? '', $beatDescription, $beatText);

			$codex_character_results = $this->updateCodex($llm, $chapterFilename, $bookSlug, 'characters', $bookData['codex']['characters'] ?? '', $beatDescription, $beatText);

			$codex_location_results = $this->updateCodex($llm, $chapterFilename, $bookSlug, 'locations', $bookData['codex']['locations'] ?? '', $beatDescription, $beatText);

			$codex_object_results = $this->updateCodex($llm, $chapterFilename, $bookSlug, 'objects', $bookData['codex']['objects'] ?? '', $beatDescription, $beatText);

			return response()->json(['success' => true,
				'chapterFilename' => $chapterFilename,
				'beatIndex' => $beatIndex,
				'codex_character_results' => $codex_character_results,
				'codex_location_results' => $codex_location_results,
				'codex_object_results' => $codex_object_results,
				'codex_lore_results' => $codex_lore_results,
				'message' => __('Codex updated successfully')]);
		}

	}
