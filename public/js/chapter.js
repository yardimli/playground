let reload_window = false;

function showBookStructureModal() {
	
	
	let structureHtml = '<h2>' + bookData.title + '</h2>';
	structureHtml += '<p><i>' + __e('Blurb') + ':</i> ' + bookData.blurb + '</p>';
	structureHtml += '<p><i>' + __e('Back Cover Text') + ':</i> ' + bookData.back_cover_text + '</p>';
	
	bookData.acts.forEach(function (act, actIndex) {
		structureHtml += '<h3>' + __e('Act ${act}', {act: (actIndex + 1)}) + ':' + act.title + ' </h3>';
		act.chapters.forEach(function (chapter) {
			
			var chapter_events = chapter.events;
			if (Array.isArray(chapter.events)) {
				chapter_events = chapter.events.join(', ');
			}
			var chapter_people = chapter.people;
			if (Array.isArray(chapter.people)) {
				chapter_people = chapter.people.join(', ');
			}
			var chapter_places = chapter.places;
			if (Array.isArray(chapter.places)) {
				chapter_places = chapter.places.join(', ');
			}
			
			structureHtml += '<h4>' + chapter.name + '</h4>';
			structureHtml += '<p>' + chapter.short_description + '</p>';
			structureHtml += '<ul>';
			structureHtml += '<li><i>' + __e('Events') + '</i>: ' + chapter_events + '</li>';
			structureHtml += '<li><i>' + __e('People') + '</i>: ' + chapter_people + '</li>';
			structureHtml += '<li><i>' + __e('Places') + '</i>: ' + chapter_places + '</li>';
			structureHtml += '<li><i>' + __e('From Previous Chapter') + '</i>: ' + chapter.from_previous_chapter + '</li>';
			structureHtml += '<li><i>' + __e('To Next Chapter') + '</i>: ' + chapter.to_next_chapter + '</li>';
			structureHtml += '</ul>';
			if (chapter.beats && chapter.beats.length > 0) {
				structureHtml += '<h5>' + __e('Beats') + ':</h5>';
				// structureHtml += '<ul>';
				chapter.beats.forEach(function (beat) {
					if (beat.beat_text) {
						let beatText = beat.beat_text;
						beatText = beatText.replace(/\n/g, '<br>');
						structureHtml += '<br>' + beatText + '<hr>';
					} else {
						structureHtml += '<br>-' + beat.description + '<hr>';
					}
				});
				// structureHtml += '</ul>';
			}
		});
	});
	
	$('#bookStructureContent').html(structureHtml);
	$('#bookStructureModal').modal({backdrop: 'static', keyboard: true}).modal('show');
}

function saveChapter(chapterData) {
	$.ajax({
		url: `/book/${bookSlug}/chapter`,
		type: 'POST',
		data: chapterData,
		dataType: 'json',
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		success: function (response) {
			if (response.success) {
				reload_window = true;
				
				$('#save_result').html('<div class="alert alert-success">' + __e('Chapter saved successfully!') + '</div>');
				$("#alertModalContent").html(__e('Chapter saved successfully!'));
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
			} else {
				console.log(response);
				$("#alertModalContent").html(__e('Failed to save chapter: ') + response.message);
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
			}
		},
		error: function (xhr, status, error) {
			console.error(xhr.responseText);
			$("#alertModalContent").html(__e('An error occurred while saving the chapter.'));
			$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
		}
	});
}

function generateAllBeats(beatsPerChapter = 3, writingStyle = 'Minimalist', narrativeStyle = 'Third Person - The narrator has a godlike perspective') {
	const modal = $('#generateAllBeatsModal');
	const progressBar = modal.find('.progress-bar');
	const log = $('#generateAllBeatsLog');
	
	modal.modal({backdrop: 'static', keyboard: true}).modal('show');
	$('#generateAllBeatsLog').empty();
	progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
	
	$('#generateAllBeatsLog').append('<br>' + __e('This process will write 10 short beats for each chapter in the book. Later these beats will be turned into full book pages.'));
	$('#generateAllBeatsLog').append('<br>' + __e('Please wait...'));
	$('#generateAllBeatsLog').append('<br><br>' + __e('If the progress bar is stuck for a long time, please refresh the page and try again.') + '<br><br>');
	
	chapters = bookData.acts.flatMap(act => act.chapters);
	
	console.log(chapters);
	generateSingleChapterBeats(chapters, beatsPerChapter, writingStyle, narrativeStyle, 0);
	
}

function generateSingleChapterBeats(chapters, beatsPerChapter, writingStyle, narrativeStyle, chapter_index = 0) {
	const modal = $('#generateAllBeatsModal');
	const log = $('#generateAllBeatsLog');
	
	const totalChapters = chapters.length;
	
	chapter_index++;
	
	const chapter = chapters[chapter_index - 1];
	$('#generateAllBeatsLog').append('<br><br>' + __e('Processing chapter: ${chapter}', {chapter: chapter.name}));
	$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
	
	// Check if the chapter already has beats
	if (chapter.beats && chapter.beats.length > 0) {
		$('#generateAllBeatsLog').append('<br>' + __e('Chapter "${chapter}" already has beats. Skipping...', {chapter: chapter.name}));
		
		const progressBar = modal.find('.progress-bar');
		const progress = Math.round((chapter_index / totalChapters) * 100);
		progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
		
		$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
		if (chapter_index < totalChapters) {
			generateSingleChapterBeats(chapters, beatsPerChapter, chapter_index);
		}
	} else {
		
		$.ajax({
			url: `/book/write-beats/${bookSlug}/${chapter.chapterFilename}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				beats_per_chapter: beatsPerChapter,
				writing_style: writingStyle,
				narrative_style: narrativeStyle,
				save_results: true,
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					// Save the generated beats back to the chapter
					
					const progressBar = modal.find('.progress-bar');
					const progress = Math.round((chapter_index / totalChapters) * 100);
					progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
					
					if (Array.isArray(response.beats)) {
						$('#generateAllBeatsLog').append('<br>' + __e('Beats generated and saved for chapter: ${chapter}', {chapter: chapter.name}));
						
						response.beats.forEach((beat, index) => {
							$('#generateAllBeatsLog').append(`<br>${beat.description}`);
						});
					} else {
						$('#generateAllBeatsLog').append('<br>' + __e('Beats failed for chapter: ${chapter}', {chapter: chapter.name}));
						$("#alertModalContent").html(__e('Failed to generate beats: ') + response.beats);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
					if (chapter_index < totalChapters) {
						generateSingleChapterBeats(chapters, beatsPerChapter, chapter_index);
					} else {
						$('#generateAllBeatsLog').append('<br>' + __e('All chapters processed!'));
						$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
					}
				} else {
					$('#generateAllBeatsLog').append('<br>' + __e('Beats failed for chapter: ${chapter}', {chapter: chapter.name}));
					$("#alertModalContent").html(__e('Failed to generate beats: ') + response.beats);
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			},
			
			error: function () {
				$('#generateAllBeatsLog').append('<p>' + __e('Error generating beats for chapter: ${chapter}', {chapter: chapter.name}) + '</p>');
				$('#generateAllBeats').scrollTop(log[0].scrollHeight);
				//break loop
			}
		});
	}
}

function rewriteChapter(chapterFilename) {
	const modal = $('#rewriteChapterModal');
	
	let chaptersToInclude = [];
	let foundCurrentChapter = false;
	let foundCurrentChapterData = [];
	for (let act of bookData.acts) {
		for (let chapter of act.chapters) {
			if (chapter.chapterFilename === chapterFilename) {
				foundCurrentChapter = true;
				foundCurrentChapterData = chapter;
				break;
			}
			chaptersToInclude.push(chapter);
		}
		if (foundCurrentChapter) {
			break;
		}
	}
	
	// Fetch the rewrite_chapter.txt template
	$.get('/prompts/rewrite_chapter.txt', function (template) {
		// Replace placeholders in the template
		const replacements = {
			'##user_blurb##': bookData.prompt || '',
			'##language##': bookData.language || 'English',
			'##book_title##': bookData.title || '',
			'##book_blurb##': bookData.blurb || '',
			'##book_keywords##': bookData.keywords ? bookData.keywords.join(', ') : '',
			'##back_cover_text##': bookData.back_cover_text || '',
			'##character_profiles##': bookData.character_profiles || '',
			'##genre##': bookData.genre || 'fantasy',
			'##adult_content##': bookData.adult_content || 'non-adult',
			'##writing_style##': $("#writingStyle").val() || 'Minimalist',
			'##narrative_style##': $("#narrativeStyle").val() || 'Third Person - The narrator has a godlike perspective',
			'##book_structure##': bookData.book_structure || 'the_1_act_story.txt',
			'##previous_chapters##': chaptersToInclude.map(ch =>
				`name: ${ch.name}\nshort description: ${ch.short_description}\nevents: ${ch.events}\npeople: ${ch.people}\nplaces: ${ch.places}\nfrom previous chapter: ${ch.from_previous_chapter}\nto next chapter: ${ch.to_next_chapter}\n\nbeats:\n${ch.beats ? ch.beats.map(b => b.beat_summary || b.description).join('\n') : ''}`
			).join('\n\n'),
			'##current_chapter##': `name: ${foundCurrentChapterData.name}\nshort description: ${foundCurrentChapterData.short_description}\nevents: ${foundCurrentChapterData.events}\npeople: ${foundCurrentChapterData.people}\nplaces: ${foundCurrentChapterData.places}\nfrom previous chapter: ${foundCurrentChapterData.from_previous_chapter}\nto next chapter: ${foundCurrentChapterData.to_next_chapter}`
		};
		
		for (const [key, value] of Object.entries(replacements)) {
			template = template.replace(new RegExp(key, 'g'), value);
		}
		
		$('#rewriteUserPrompt').val(template.trim());
		
		// Show the modal
		modal.modal('show');
	});
	
	// Handle the rewrite button click
	$('#sendRewritePromptBtn').off('click').on('click', function () {
		const userPrompt = $('#rewriteUserPrompt').val();
		$('#sendRewritePromptBtn').prop('disabled', true).text(__e('Rewriting...'));
		
		$.ajax({
			url: '/rewrite-chapter',
			method: 'POST',
			data: {
				book_slug: bookSlug,
				llm: savedLlm,
				user_prompt: userPrompt
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					// Display the rewritten chapter in the modal
					$('#rewriteResult').val(JSON.stringify(response.rewrittenChapter, null, 2));
					$('#acceptRewriteBtn').show();
				} else {
					$("#alertModalContent").html(__e('Failed to rewrite chapter:') + response.message);
					$("#alertModal").modal('show');
				}
				$('#sendRewritePromptBtn').prop('disabled', false).text(__e('Rewrite Chapter'));
			},
			error: function () {
				$("#alertModalContent").html(__e('Error rewriting chapter'));
				$("#alertModal").modal('show');
				$('#sendRewritePromptBtn').prop('disabled', false).text(__e('Rewrite Chapter'));
			}
		});
	});
	
	$('#acceptRewriteBtn').off('click').on('click', function () {
		$.ajax({
			url: '/accept-rewrite',
			method: 'POST',
			data: {
				book_slug: bookSlug,
				chapter_filename: chapterFilename,
				rewritten_content: $('#rewriteResult').val()
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					reload_window = true;
					$("#alertModalContent").html(__e('Chapter rewritten successfully!'));
					$("#alertModal").modal('show');
				} else {
					$("#alertModalContent").html(__e('Failed to save rewritten chapter:') + response.message);
					$("#alertModal").modal('show');
				}
			},
			error: function () {
				$("#alertModalContent").html(__e('Error saving rewritten chapter'));
				$("#alertModal").modal('show');
			}
		});
	});
	
}


$(document).ready(function () {
	$('.closeAndRefreshButton').on('click', function () {
		location.reload();
	});
	
	let bookToDelete = null;
	
	$('.delete-book-btn').on('click', function(e) {
		e.preventDefault();
		bookToDelete = $(this).data('book-id');
		$('#deleteConfirmModal').modal('show');
	});
	
	$('#confirmDeleteBtn').on('click', function() {
		if (bookToDelete) {
			$.ajax({
				url: `/book/${bookToDelete}`,
				type: 'DELETE',
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					if (response.success) {
						$('#deleteConfirmModal').modal('hide');
						window.location.href = '/my-books';
					} else {
						alert(response.message);
					}
				},
				error: function() {
					alert('An error occurred while deleting the book.');
				}
			});
		}
	});
	
	$('#createCoverBtn').on('click', function (e) {
		e.preventDefault();
		$('#createCoverModal').modal({backdrop: 'static', keyboard: true}).modal('show');
		$("#coverBookTitle").val(bookData.title);
		$("#coverBookAuthor").val(bookData.author_name);
		$("#coverPrompt").val(__e('An image describing: ') + bookData.blurb);
	});
	
	let createCoverFileName = '';
	
	$('#generateCoverBtn').on('click', function () {
		$('#generateCoverBtn').prop('disabled', true).text(__e('Generating...'));
		
		$.ajax({
			url: '/make-cover-image/' + bookSlug,
			method: 'POST',
			data: {
				theme: $("#coverPrompt").val(),
				title_1: $("#coverBookTitle").val(),
				author_1: $("#coverBookAuthor").val(),
				creative: $("#enhancePrompt").is(':checked') ? 'more' : 'no',
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (data) {
				if (data.success) {
					$('#generatedCover').attr('src', "/storage/ai-images/" + data.output_filename);
					createCoverFileName = data.output_filename;
					$('#saveCoverBtn').prop('disabled', false);
				} else {
					$("#alertModalContent").html(__e('Failed to generate cover: ') + data.message);
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
				$('#generateCoverBtn').prop('disabled', false).text(__e('Generate'));
			}
		});
	});
	
	
	$('#saveCoverBtn').on('click', function () {
		$.ajax({
			url: '/book/' + bookSlug + '/cover',
			method: 'POST',
			data: {
				cover_filename: createCoverFileName
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (data) {
				if (data.success) {
					$("#alertModalContent").html(__e('Cover saved successfully!'));
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					
					$('#bookCover').attr('src', '/storage/ai-images/' + createCoverFileName);
				} else {
					$("#alertModalContent").html(__e('Failed to save cover: ') + data.message);
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			},
			error: function (xhr, status, error) {
				$("#alertModalContent").html(__e('An error occurred while saving the cover.'));
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
			}
		});
	});
	
	$('.update-chapter-btn').on('click', function () {
		var chapterFilename = $(this).data('chapter-filename');
		var chapterCard = $(this).closest('.card');
		
		var chapterData = {
			chapter_filename: chapterFilename,
			name: chapterCard.find('.chapterName').val(),
			order: chapterCard.find('.chapterOrder').val(),
			short_description: chapterCard.find('.chapterShortDescription').val(),
			events: chapterCard.find('.chapterEvents').val(),
			people: chapterCard.find('.chapterPeople').val(),
			places: chapterCard.find('.chapterPlaces').val(),
			from_previous_chapter: chapterCard.find('.chapterFromPreviousChapter').val(),
			to_next_chapter: chapterCard.find('.chapterToNextChapter').val()
		};
		saveChapter(chapterData);
	});
	
	$('#showBookStructureBtn').on('click', function (e) {
		e.preventDefault();
		showBookStructureModal();
	});
	
	$('#generateAllBeatsBtn').on('click', function (e) {
		e.preventDefault();
		generateAllBeats(parseInt($('#beatsPerChapter').val()), $("#writingStyle").val(), $("#narrativeStyle").val());
	});
	
	$('#rewriteChapterModal').on('shown.bs.modal', function () {
		$('#rewriteUserPrompt').focus();
	});
	
	$('#beatsPerChapter').on('change', function () {
		let selectedBeats = $(this).val();
		$('.editBeatsLink').each(function () {
			let currentHref = $(this).attr('href');
			console.log(currentHref);
			let newHref = currentHref.replace(/\/\d+$/, '/' + selectedBeats);
			$(this).attr('href', newHref);
		});
	});
	
	
	// Open the edit book details modal
	$('#editBookDetailsBtn').on('click', function () {
		$('#editBlurb').val(bookData.blurb);
		$('#editBackCoverText').val(bookData.back_cover_text);
		$('#editCharacterProfiles').val(bookData.character_profiles);
		$('#editAuthorName').val(bookData.author_name);
		$('#editPublisherName').val(bookData.publisher_name);
		$('#editBookDetailsModal').modal('show');
	});
	
	$(".alert-modal-close-button").on('click', function () {
		if (reload_window) {
			location.reload();
		}
	});
	
	// Save book details
	$('#saveBookDetailsBtn').on('click', function () {
		const updatedBookData = {
			blurb: $('#editBlurb').val(),
			back_cover_text: $('#editBackCoverText').val(),
			character_profiles: $('#editCharacterProfiles').val(),
			author_name: $('#editAuthorName').val(),
			publisher_name: $('#editPublisherName').val()
		};
		
		$.ajax({
			url: `/book/${bookSlug}/details`,
			type: 'POST',
			data: updatedBookData,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function (response) {
				if (response.success) {
					// Update the bookData object
					Object.assign(bookData, updatedBookData);
					
					// Update the displayed information
					$('#bookBlurb').text(bookData.blurb);
					$('#backCoverText').html(bookData.back_cover_text.replace(/\n/g, '<br>'));
					$('#bookCharacters').html('<em>' + __e("Character Profiles:") + '</em><br>' + bookData.character_profiles.replace(/\n/g, '<br>'));
					
					reload_window = true;
					$('#editBookDetailsModal').modal('hide');
					$("#alertModalContent").html(__e("Book details updated successfully!"));
					$("#alertModal").modal('show');
				} else {
					$("#alertModalContent").html(__e("Failed to update book details:") + response.message);
					$("#alertModal").modal('show');
				}
			},
			error: function () {
				$("#alertModalContent").html(__e("An error occurred while updating book details."));
				$("#alertModal").modal('show');
			}
		});
	});
	
	
	// Open LLM Prompt Modal
	$('#openLlmPromptModalBtn').on('click', function () {
		$('#llmPromptModal').modal('show');
	});
	
	// Send Prompt to LLM
	$('#sendPromptBtn').on('click', function () {
		const userPrompt = $('#userPrompt').val();
		const llm = savedLlm; // Assuming you have a savedLlm variable
		
		// Disable buttons and show loading state
		$('#sendPromptBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
		$('#llmResponse').val('Processing...');
		
		$.ajax({
			url: '/send-llm-prompt/' + bookSlug,
			method: 'POST',
			data: {
				user_prompt: userPrompt,
				llm: llm
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					$('#llmResponse').val(response.result);
				} else {
					$('#llmResponse').val('Error: ' + response.message);
				}
			},
			error: function (xhr, status, error) {
				$('#llmResponse').val('An error occurred while processing the request.');
			},
			complete: function () {
				// Re-enable button and restore original text
				$('#sendPromptBtn').prop('disabled', false).text('Send Prompt');
			}
		});
	});
	
	
});
