function showBookStructureModal() {
	$.post('action-book.php', {
		action: 'get_book_structure',
		llm: savedLlm,
		book: bookParam
	}, function (response) {
		if (response.success) {
			let structureHtml = '<h2>' + response.bookTitle + '</h2>';
			structureHtml += '<p><i>' + __e('Blurb') + ':</i> ' + response.bookBlurb + '</p>';
			structureHtml += '<p><i>' + __e('Back Cover Text') + ':</i> ' + response.backCoverText + '</p>';
			
			response.acts.forEach(function (act, actIndex) {
				structureHtml += '<h3>' + __e('Act ${act}', {act: (actIndex + 1)}) + ':' + act.name + ' < /h3>';
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
					structureHtml += '<li><i>' + __e('From Previous Chapter') + '</i>: ' + chapter.from_prev_chapter + '</li>';
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
		} else {
			alert(__e('Failed to load book structure: ') + response.message);
		}
	}, 'json');
	
}

function loadChapters() {
	//empty the rows
	$('.book-chapter-act-ul').empty();
	
	$.post('action-book.php', {action: 'load_chapters', book: bookParam, llm: savedLlm}, function (data) {
		const stories = JSON.parse(data);
		
		// Group stories by row
		const groupedStories = stories.reduce((acc, chapter) => {
			if (!acc[chapter.row]) {
				acc[chapter.row] = [];
			}
			acc[chapter.row].push(chapter);
			return acc;
		}, {});
		
		// Iterate through each row
		Object.keys(groupedStories).forEach(row => {
			// Sort stories by order within each row
			groupedStories[row].sort((a, b) => a.order - b.order);
			
			// Append sorted stories to the respective row
			groupedStories[row].forEach(chapter => {
				const chapterCard = createChapter(chapter);
				$(`.book-chapter-act-ul[data-row="${row}"]`).append(chapterCard);
			});
		});
	});
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
        <br><strong>${__e('Prev')}:</strong> ${chapter.from_prev_chapter}
        <br><strong>${__e('Next')}:</strong> ${chapter.to_next_chapter}
    </div>
    <div class="row" style="margin: 0px;">
	    <div class="col-12 col-lg-6 mb-2" style="padding-left: 3px; padding-right: 3px;">
				<div class="btn bt-lg btn-info w-100" data-chapter-filename="${chapter.chapterFilename}" onclick="editChapter('${chapter.chapterFilename}')" >${__e('Edit Chapter')}</div>
			</div>
    <div class="col-12 col-lg-6 mb-2" style="padding-left: 3px; padding-right: 3px;">
			<a class="btn  bt-lg btn-primary w-100" href="chapter-beats.php?book=${bookParam}&chapter=${chapter.chapterFilename.replace('.json', '')}">${__e('Open Beats')}</a>
	    </div>
    </div>
  </div>
`;
}

function saveChapter() {
	const formData = new FormData(document.getElementById('chapterForm'));
	formData.append('chapterFilename', $('#chapterFilename').val());
	formData.append('name', $('#chapterName').val());
	formData.append('short_description', $('#chapterText').val());
	formData.append('events', $('#chapterEvents').val());
	formData.append('people', $('#chapterPeople').val());
	formData.append('places', $('#chapterPlaces').val());
	formData.append('from_prev_chapter', $('#chapterFromPrevChapter').val());
	formData.append('to_next_chapter', $('#chapterToNextChapter').val());
	
	formData.append('backgroundColor', $('#chapterBackgroundColor').val());
	formData.append('textColor', $('#chapterTextColor').val());
	formData.append('action', 'save_chapter');
	formData.append('book', bookParam);
	formData.append('llm', savedLlm);
	
	$.ajax({
		url: 'action-book.php',
		type: 'POST',
		data: formData,
		dataType: 'json',
		processData: false, // Prevent jQuery from automatically transforming the data into a query string
		contentType: false, // Prevent jQuery from overriding the Content-Type header
		success: function (response) {
			if (response.success) {
				$('#save_result').html('<div class="alert alert-success">' + __e('Chapter saved successfully!') + '</div>');
				alert('Chapter saved successfully!');
				const chapter = response;
				const chapterSelector = `.book-chapter-card-col[data-chapter-filename="${chapter.chapterFilename}"]`;
				const existingChapter = $(chapterSelector);
				if (existingChapter.length) {
					existingChapter.off('click'); // Unbind the click event
					existingChapter.replaceWith(createChapter(chapter));
				}
			} else {
				console.log(response);
				alert(__e('Failed to save chapter: ') + response.message);
			}
		}
	});
}

function editChapter(chapterFilename) {
	fetch(`${chaptersDirName}/${chapterFilename}`)
		.then(response => response.json())
		.then(chapter => {
			
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
			$('#chapterFromPrevChapter').val(chapter.from_prev_chapter);
			$('#chapterToNextChapter').val(chapter.to_next_chapter);
			$('#chapterBackgroundColor').val(chapter.backgroundColor);
			$('#chapterTextColor').val(chapter.textColor);
			$('#chapterModal').modal({backdrop: 'static', keyboard: true}).modal('show');
		})
		.catch(error => console.error('Error loading chapter:', error));
}


function generateAllBeats() {
	const modal = $('#generateAllBeatsModal');
	const progressBar = modal.find('.progress-bar');
	
	modal.modal({backdrop: 'static', keyboard: true}).modal('show');
	$('#generateAllBeatsLog').empty();
	progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
	
	$('#generateAllBeatsLog').append('<br>' + __e('This process will write 10 short beats for each chapter in the book. Later these beats will be turned into full book pages.'));
	$('#generateAllBeatsLog').append('<br>' + __e('Please wait...'));
	$('#generateAllBeatsLog').append('<br><br>' + __e('If the progress bar is stuck for a long time, please refresh the page and try again.') + '<br><br>');
	
	$.post('action-book.php', {action: 'load_chapters', book: bookParam, llm: savedLlm}, function (response) {
		let chapters = JSON.parse(response);
		
		const totalChapters = chapters.length;
		let processedChapters = 0;
		let lastChapterBeats = [];
		
		console.log(chapters);
		
		function processNextChapter() {
			if (processedChapters < totalChapters) {
				const chapter = chapters[processedChapters];
				$('#generateAllBeatsLog').append('<br><br>' + __e('Processing chapter: ${chapter}', {chapter: chapter.name}));
				$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
				
				// Check if the chapter already has beats
				if (chapter.beats && chapter.beats.length > 0) {
					$('#generateAllBeatsLog').append('<br>' + __e('Chapter "${chapter}" already has beats. Skipping...', {chapter: chapter.name}));
					$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
					lastChapterBeats = chapter.beats;
					continueProcessing();
				} else {
					let prevChapterBeats = '';
					if (lastChapterBeats.length > 0) {
						prevChapterBeats = `${lastChapterBeats.map((beat, index) => __e('Beat ${index}', {index: index}) + ' : ' + beat.description).join('\n')}`;
					}
					
					let nextChapterText = chapter.to_next_chapter;
					if (processedChapters + 1 < totalChapters) {
						let nextChapter = chapters[processedChapters + 1];
						nextChapterText = nextChapter.name + ': ' + nextChapter.short_description;
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
					
					$.ajax({
						url: 'action-beats.php',
						method: 'POST',
						data: {
							action: 'write_beats',
							llm: savedLlm,
							simulated: false,
							book: bookParam,
							chapterFilename: chapter.chapterFilename,
							chapterName: chapter.name,
							chapterText: chapter.short_description,
							chapterEvents: chapter_events,
							chapterPeople: chapter_people,
							chapterPlaces: chapter_places,
							chapterFromPrevChapter: prevChapterBeats, //chapter.from_prev_chapter,
							chapterToNextChapter: nextChapterText
						},
						dataType: 'json',
						success: function (response) {
							if (response.success) {
								// Save the generated beats back to the chapter
								$.ajax({
									url: 'action-beats.php',
									method: 'POST',
									data: {
										action: 'save_beats',
										llm: savedLlm,
										book: bookParam,
										chapterFilename: chapter.chapterFilename,
										beats: JSON.stringify(response.beats)
									},
									dataType: 'json',
									success: function (saveResponse) {
										if (saveResponse.success) {
											if (Array.isArray(response.beats)) {
												$('#generateAllBeatsLog').append('<br>' + __e('Beats generated and saved for chapter: ${chapter}', {chapter: chapter.name}));
												lastChapterBeats = response.beats;
												
												response.beats.forEach((beat, index) => {
													$('#generateAllBeatsLog').append(`<br>${beat.description}`);
												});
											} else {
												$('#generateAllBeatsLog').append('<br>' + __e('Beats generated but failed to save for chapter: ${chapter}', {chapter: chapter.name}));
												alert(__e('Failed to generate beats: ') + response.beats);
											}
											
										} else {
											$('#generateAllBeatsLog').append('<br>' + __e('Beats generated but failed to save for chapter: ${chapter}', {chapter: chapter.name}));
										}
										$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
										continueProcessing();
									},
									error: function () {
										$('#generateAllBeatsLog').append('<p>' + __e('Error saving beats for chapter: ${chapter}', {chapter: chapter.name}) + '</p>');
										$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
										continueProcessing();
									}
								});
							} else {
								$('#generateAllBeatsLog').append('<p>' + __e('Failed to generate beats for chapter: ${chapter} :: ${response}', {
									chapter: chapter.name,
									response: response
								}) + '</p>');
								$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
								continueProcessing();
							}
						},
						error: function () {
							$('#generateAllBeatsLog').append('<p>' + __e('Error generating beats for chapter: ${chapter}', {chapter: chapter.name}) + '</p>');
							$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
							continueProcessing();
						}
					});
				}
			} else {
				$('#generateAllBeatsLog').append('<p>' + __e('All chapters processed!') + '</p>');
				$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
			}
		}
		
		function continueProcessing() {
			processedChapters++;
			const progress = Math.round((processedChapters / totalChapters) * 100);
			progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
			processNextChapter();
		}
		
		processNextChapter();
	});
}

$(document).ready(function () {
	// Fetch initial data
	$.ajax({
		url: 'action-book.php',
		method: 'POST',
		data: {action: 'fetch_initial_data', book: bookParam, llm: savedLlm},
		dataType: 'json',
		success: function (data) {
			window.colorOptions = data.colorOptions;
			window.chaptersDirName = data.chaptersDirName;
			window.users = data.users;
			window.currentUserName = data.currentUser;
			window.defaultRow = data.defaultRow;
			window.rows = data.rows;
			bookData = data.bookData;
			let characters = (data.bookData.character_profiles ?? '');
			characters = characters.replace(/\n/g, '<br>');
			
			$("#bookTitle").text(data.bookData.title);
			$("#bookBlurb").text(data.bookData.blurb);
			$("#backCoverText").text(data.bookData.back_cover_text);
			$("#bookPrompt").html('<br><em>' + __e('Prompt For Book:') + '</em><br>' + (data.bookData.prompt ?? ''));
			$("#bookCharacters").html('<br><em>' + __e('Character Profiles:') + '</em><br>' + characters);
			
			if (data.bookData.cover_filename) {
				$('#bookCover').attr('src', 'ai-images/' + data.bookData.cover_filename);
				$("#bookCoverContainer").removeClass('d-none');
			}
			
			// Populate rows
			for (let row of window.rows) {
				$('#bookBoard').append(`
                            <div class="book-chapter-act">
                                <h3>${row.title}</h3>
                                <div class="row book-chapter-act-ul" id="${row.id}-row" data-row="${row.id}"></div>
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
			loadChapters();
		},
		error: function (xhr, status, error) {
			//redirect to login page
			window.location.href = 'login.php';
		}
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
			url: 'action-make-cover.php',
			method: 'POST',
			data: {
				action: 'make-cover',
				book: bookParam,
				llm: savedLlm,
				theme: $("#coverPrompt").val(),
				title_1: $("#coverBookTitle").val(),
				author_1: $("#coverBookAuthor").val(),
				creative: $("#enhancePrompt").is(':checked') ? 'more' : 'no'
			},
			dataType: 'json',
			success: function (data) {
				if (data.success) {
					$('#generatedCover').attr('src', "ai-images/" + data.output_filename);
					createCoverFileName = data.output_filename;
					$('#saveCoverBtn').prop('disabled', false);
				} else {
					alert(__e('Failed to generate cover: ') + data.message);
				}
				$('#generateCoverBtn').prop('disabled', false).text(__e('Generate'));
			}
		});
	});
	
	
	$('#saveCoverBtn').on('click', function () {
		$.ajax({
			url: 'action-book.php',
			method: 'POST',
			data: {
				action: 'save_cover',
				book: bookParam,
				llm: savedLlm,
				cover_filename: createCoverFileName
			},
			dataType: 'json',
			success: function (data) {
				if (data.success) {
					alert(__e('Cover saved successfully!'));
					$('#bookCover').attr('src', 'ai-images/' + createCoverFileName);
				} else {
					alert(__e('Failed to save cover: ') + data.message);
				}
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
		generateAllBeats();
	});
	
	$('#chapterModal').on('shown.bs.modal', function () {
		$('#chapterName').focus();
	});
});
