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


function createChapter(chapter) {
	let truncatedText;
	if (chapter.short_description.length > 128) {
		const words = chapter.short_description.split(' ');
		let charCount = 0;
		truncatedText = '';
		for (const word of words) {
			if ((charCount + word.length + 1) > 128) {
				truncatedText += '...';
				break;
			}
			truncatedText += (truncatedText.length ? ' ' : '') + word;
			charCount += word.length + 1;
		}
	} else {
		truncatedText = chapter.short_description;
	}
	
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
	
	return `<div class="col-xl-3 col-lg-3 col-12 mb-5 book-chapter-card-col" data-chapter-filename="${chapter.chapterFilename}"><div class="book-chapter-card" style="background-color: ${chapter.backgroundColor}; color: ${chapter.textColor}">
        <div style="font-size: 18px; margin-bottom: 15px;">${chapter.name}</div>
        <div class="mb-2">${truncatedText}</div>
        <strong>${__e('Events')}:</strong> ${chapter_events}
        <br><strong>${__e('People')}:</strong> ${chapter_people}
        <br><strong>${__e('Places')}:</strong> ${chapter_places}
        <br><strong>${__e('Prev')}:</strong> ${chapter.from_previous_chapter}
        <br><strong>${__e('Next')}:</strong> ${chapter.to_next_chapter}
    </div>
    <div class="row" style="margin: 0px;">
	    <div class="col-12 col-lg-6 mb-2 zero-right-padding-on-mobile">
				<div class="btn bt-lg btn-info w-100" data-chapter-filename="${chapter.chapterFilename}" onclick="editChapter('${chapter.chapterFilename}')" >${__e('Edit Chapter')}</div>
			</div>
    <div class="col-12 col-lg-6 mb-2 zero-left-padding-on-mobile">
			<a class="btn bt-lg btn-primary w-100" href="/book-beats/${bookSlug}/${chapter.chapterFilename.replace('.json', '')}">${__e('Open Beats')}</a>
	    </div>
    </div>
  </div>
`;
}

function saveChapter() {
	$.ajax({
		url: `/book/${bookSlug}/chapter`,
		type: 'POST',
		data: {
			chapterFilename: $('#chapterFilename').val(),
			name: $('#chapterName').val(),
			short_description: $('#chapterText').val(),
			events: $('#chapterEvents').val(),
			people: $('#chapterPeople').val(),
			places: $('#chapterPlaces').val(),
			from_previous_chapter: $('#chapterFromPreviousChapter').val(),
			to_next_chapter: $('#chapterToNextChapter').val(),
			backgroundColor: $('#chapterBackgroundColor').val(),
			textColor: $('#chapterTextColor').val(),
			llm: savedLlm
		},
		dataType: 'json',
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		success: function (response) {
			if (response.success) {
				$('#save_result').html('<div class="alert alert-success">' + __e('Chapter saved successfully!') + '</div>');
				$("#alertModalContent").html(__e('Chapter saved successfully!'));
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				// Refresh the page
				location.reload();
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

function editChapter(chapterFilename) {
	let chapter = null;
	for (let bookAct of bookData['acts']) {
		for (let bookChapter of bookAct['chapters']) {
			if (bookChapter.chapterFilename == chapterFilename) {
				chapter = bookChapter;
				break
				2;
			}
		}
	}
	console.log(chapter);
	
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
	
	$('#save_result').html('');
	$('#chapterFilename').val(chapterFilename);
	$('#chapterName').val(chapter.name);
	$('#chapterText').val(chapter.short_description);
	$('#chapterEvents').val(chapter_events);
	$('#chapterPeople').val(chapter_people);
	$('#chapterPlaces').val(chapter_places);
	$('#chapterFromPreviousChapter').val(chapter.from_previous_chapter);
	$('#chapterToNextChapter').val(chapter.to_next_chapter);
	$('#chapterBackgroundColor').val(chapter.backgroundColor);
	$('#chapterTextColor').val(chapter.textColor);
	$('#chapterModal').modal({backdrop: 'static', keyboard: true}).modal('show');
	
}

function generateAllBeats(beatsPerChapter = 3) {
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
	generateSingleChapterBeats(chapters, beatsPerChapter, 0);
	
}

function generateSingleChapterBeats(chapters, beatsPerChapter, chapter_index = 0) {
	const modal = $('#generateAllBeatsModal');
	const log = $('#generateAllBeatsLog');
	
	const totalChapters = chapters.length;
	
	chapter_index++;
	
	const chapter = chapters[chapter_index - 1];
	$('#generateAllBeatsLog').append('<br><br>' + __e('Processing chapter: ${chapter}', {chapter: chapter.name}));
	$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
	
	// Check if the chapter already has beats
	// if (chapter.beats && chapter.beats.length > 0) {
	// 	$('#generateAllBeatsLog').append('<br>' + __e('Chapter "${chapter}" already has beats. Skipping...', {chapter: chapter.name}));
	//
	// 	const progressBar = modal.find('.progress-bar');
	// 	const progress = Math.round((chapter_index / totalChapters) * 100);
	// 	progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
	//
	// 	$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
	// 	if (chapter_index < totalChapters) {
	// 		generateSingleChapterBeats(chapters, beatsPerChapter, chapter_index);
	// 	}
	// } else {
		
		$.ajax({
			url: `/book/write-beats/${bookSlug}/${chapter.chapterFilename}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				beats_per_chapter: beatsPerChapter,
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
	// }
}


$(document).ready(function () {
	
	// Populate bookActs
	for (let bookAct of bookData['acts']) {
		$('#bookBoard').append(`
	<div class="book-chapter-act">
	  <h3>${bookAct.title}</h3>
	  <div class="row book-chapter-act-ul" id="${bookAct.id}-book-act" data-book-act="${bookAct.id}"></div>
	</div>
	`);
	}
	
	
	// Create color buttons
	const colorPalette = $('#colorPalette');
	colorOptions.forEach(option => {
		const button = $(`<button type="button" class="btn m-1" style="background-color: ${option.background}; color: ${option.text};">${option.text}</button>`);
		button.on('click', function () {
			$('#chapterBackgroundColor').val(option.background);
			$('#chapterTextColor').val(option.text);
			$('#colorPalette button').removeClass('active');
			$(this).addClass('active');
		});
		colorPalette.append(button);
	});
	
	//set default color
	$('#colorPalette button').first().click();
	
	
	$('.book-chapter-act-ul').empty();
	
	for (let bookAct of bookData['acts']) {
		for (let chapter of bookAct['chapters']) {
			const chapterCard = createChapter(chapter);
			$(`#${bookAct.id}-book-act`).append(chapterCard);
		}
	}
	
	$('.closeAndRefreshButton').on('click', function () {
		location.reload();
	});
	
	$('#createCoverBtn').on('click', function (e) {
		e.preventDefault();
		$('#createCoverModal').modal({backdrop: 'static', keyboard: true}).modal('show');
		$("#coverBookTitle").val(bookData.title);
		$("#coverBookAuthor").val(currentUserName);
		$("#coverPrompt").val(__e('An image describing: ') + bookData.blurb);
	});
	
	let createCoverFileName = '';
	
	$('#generateCoverBtn').on('click', function () {
		$('#generateCoverBtn').prop('disabled', true).text(__e('Generating...'));
		
		$.ajax({
			url: '/cover-image/' + bookSlug,
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
	
	
	$('#saveChapter').on('click', function (e) {
		e.preventDefault();
		saveChapter();
	});
	
	$('#showBookStructureBtn').on('click', function (e) {
		e.preventDefault();
		showBookStructureModal();
	});
	
	$('#generateAllBeatsBtn').on('click', function (e) {
		e.preventDefault();
		generateAllBeats( parseInt($('#beatsPerChapter').val()) );
	});
	
	$('#chapterModal').on('shown.bs.modal', function () {
		$('#chapterName').focus();
	});
});
