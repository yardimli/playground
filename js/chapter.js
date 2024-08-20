function showBookStructureModal() {
	$.post('action-book.php', {
		action: 'get_book_structure',
		llm: savedLlm,
		book: bookParam
	}, function (response) {
		if (response.success) {
			let structureHtml = '<h2>' + response.bookTitle + '</h2>';
			structureHtml += '<p><i>Blurb:</i> ' + response.bookBlurb + '</p>';
			structureHtml += '<p><i>Back Cover Text:</i> ' + response.backCoverText + '</p>';
			
			response.acts.forEach(function (act, actIndex) {
				structureHtml += '<h3>Act ' + (actIndex + 1) + ': ' + act.name + '</h3>';
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
					structureHtml += '<li><i>Events</i>: ' + chapter_events + '</li>';
					structureHtml += '<li><i>People</i>: ' + chapter_people + '</li>';
					structureHtml += '<li><i>Places</i>: ' + chapter_places + '</li>';
					structureHtml += '<li><i>From Previous Chapter: </i>' + chapter.from_prev_chapter + '</li>';
					structureHtml += '<li><i>To Next Chapter</i>: ' + chapter.to_next_chapter + '</li>';
					structureHtml += '</ul>';
					if (chapter.beats && chapter.beats.length > 0) {
						structureHtml += '<h5>Beats:</h5>';
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
			;
		} else {
			alert('Failed to load book structure: ' + response.message);
		}
	}, 'json');
	
}

function createChapter(chapter) {
	const createdTime = formatRelativeTime(chapter.created);
	const updatedTime = formatRelativeTime(chapter.lastUpdated);
	const numComments = chapter.comments ? chapter.comments.length : 0;
	const numFiles = chapter.files ? chapter.files.length : 0;
	
	let numCommentsText = '';
	if (numComments > 0) {
		numCommentsText = numComments === 1 ? '1 Comment <br>' : `${numComments} Comments <br>`;
	}
	
	let numFilesText = '';
	if (numFiles > 0) {
		numFilesText = numFiles === 1 ? '1 File <br>' : `${numFiles} Files <br>`;
	}
	
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
	
	const beatsButton = `<a class="btn btn-primary beat-btn" href="chapter-beats.php?book=${bookParam}&chapter=${chapter.chapterFilename.replace('.json', '')}">Beats</a>`;
	
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
	
	return `<div class="col-3 kanban-card-col" data-chapter-filename="${chapter.chapterFilename}" onclick="editChapter('${chapter.chapterFilename}')" style="cursor: pointer;"><div class="kanban-card" style="background-color: ${chapter.backgroundColor}; color: ${chapter.textColor}">
				${beatsButton}
        <div style="font-size: 18px; margin-bottom: 15px;">${chapter.name}</div>
        <div class="mb-2">${truncatedText}</div>
        <strong>Events:</strong> ${chapter_events}
        <br><strong>People:</strong> ${chapter_people}
        <br><strong>Places:</strong> ${chapter_places}
        <br><strong>Prev.:</strong> ${chapter.from_prev_chapter}
        <br><strong>Next.:</strong> ${chapter.to_next_chapter}
<!--        <p>${numCommentsText}${numFilesText}<strong>Created:</strong> <span title="${moment.utc(chapter.created).local().format('LLLL')}">${createdTime}</span> <br><strong>Updated:</strong> <span title="${moment.utc(chapter.lastUpdated).local().format('LLLL')}">${updatedTime}</span></p>-->
    </div></div>`;
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
	
	let files = $('#chapterFiles')[0].files;
	for (let i = 0; i < files.length; i++) {
		formData.append('files[]', files[i]);
	}
	
	$.ajax({
		url: 'action-book.php',
		type: 'POST',
		data: formData,
		processData: false, // Prevent jQuery from automatically transforming the data into a query string
		contentType: false, // Prevent jQuery from overriding the Content-Type header
		success: function (response) {
			$('#save_result').html('<div class="alert alert-success">Chapter saved successfully!</div>');
			const chapter = JSON.parse(response);
			const chapterSelector = `.kanban-card-col[data-chapter-filename="${chapter.chapterFilename}"]`;
			const existingChapter = $(chapterSelector);
			if (existingChapter.length) {
				existingChapter.off('click'); // Unbind the click event
				existingChapter.replaceWith(createChapter(chapter));
			} else {
				const insertRow = $('.kanban-row-ul[data-row="' + defaultRow + '"]');
				insertRow.prepend(createChapter(chapter));
				
				insertRow.children().each(function (index) {
					const chapterFilename = $(this).attr('data-chapter-filename');
					updateChapterRow(chapterFilename, defaultRow, index);
				});
				//scroll to top of document
				setTimeout(function () {
					$("#chapterModal").modal('hide');
				}, 400);
				
				$('html, body').animate({scrollTop: 0}, 200);
			}
			updateUploadFilesList(chapter);
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
			$('#chapterFiles').val('');
			
			$('#showCommentModal').show();
			
			const commentsList = $('#commentsList');
			commentsList.empty(); // Clear existing comments
			$('.comments-section').hide();
			if (chapter.comments) {
				chapter.comments.forEach(comment => {
					$('.comments-section').show();
					comment.chapterFilename = chapterFilename; // Add chapterFilename to each comment
					commentsList.append(createCommentHtml(comment));
				});
			}
			updateUploadFilesList(chapter, chapterFilename);
			
			$('#chapterModal').modal({backdrop: 'static', keyboard: true}).modal('show');
		})
		.catch(error => console.error('Error loading chapter:', error));
}

function deleteChapter() {
	const chapterFilename = $('#chapterFilename').val();
	$.post('action-book.php', {
		action: 'delete_chapter',
		llm: savedLlm,
		book: bookParam,
		chapterFilename: chapterFilename
	}, function (response) {
		if (response.success) {
			$(`.kanban-card-col[data-chapter-filename="${chapterFilename}"]`).remove();
			$('#chapterModal').modal('hide');
			$('#deleteConfirmationModal').modal('hide');
			$('#save_result').html('<div class="alert alert-success">Chapter deleted successfully!</div>');
		} else {
			$('#save_result').html('<div class="alert alert-danger">Failed to delete the chapter: ' + response.message + '</div>');
		}
	}, 'json');
}

function generateAllBeats() {
	const modal = $('#generateAllBeatsModal');
	const progressBar = modal.find('.progress-bar');
	const log = $('#generateAllBeatsLog');
	modal.modal({backdrop: 'static', keyboard: true}).modal('show');
	log.empty();
	progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
	
	$.post('action-book.php', {action: 'load_chapters', book: bookParam, llm: savedLlm}, function (response) {
		let chapters = JSON.parse(response);
		
		const totalChapters = chapters.length;
		let processedChapters = 0;
		let lastChapterBeats = [];
		
		console.log(chapters);
		
		function processNextChapter() {
			if (processedChapters < totalChapters) {
				const chapter = chapters[processedChapters];
				log.append(`<br><br>Processing chapter: ${chapter.name}`);
				log.scrollTop(log[0].scrollHeight);
				
				// Check if the chapter already has beats
				if (chapter.beats && chapter.beats.length > 0) {
					log.append(`<br>Chapter "${chapter.name}" already has beats. Skipping...`);
					log.scrollTop(log[0].scrollHeight);
					lastChapterBeats = chapter.beats;
					continueProcessing();
				} else {
					let prevChapterBeats = '';
					if (lastChapterBeats.length > 0) {
						prevChapterBeats = `${lastChapterBeats.map((beat, index) => `Beat ${index} : ${beat.description}`).join('\n')}`;
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
												log.append(`<br>Beats generated and saved for chapter: ${chapter.name}`);
												lastChapterBeats = response.beats;
												
												response.beats.forEach((beat, index) => {
													log.append(`<br>${beat.description}`);
												});
											} else {
												log.append(`<br>Beats generated but failed to save for chapter: ${chapter.name}`);
												alert('Failed to generate beats: ' + response.beats);
											}
											
										} else {
											log.append(`<br>Beats generated but failed to save for chapter: ${chapter.name}`);
										}
										log.scrollTop(log[0].scrollHeight);
										continueProcessing();
									},
									error: function () {
										log.append(`<p>Error saving beats for chapter: ${chapter.name}</p>`);
										log.scrollTop(log[0].scrollHeight);
										continueProcessing();
									}
								});
							} else {
								log.append(`<p>Failed to generate beats for chapter: ${chapter.name}</p>`);
								log.scrollTop(log[0].scrollHeight);
								continueProcessing();
							}
						},
						error: function () {
							log.append(`<p>Error generating beats for chapter: ${chapter.name}</p>`);
							log.scrollTop(log[0].scrollHeight);
							continueProcessing();
						}
					});
				}
			} else {
				log.append('<p>All chapters processed!</p>');
				log.scrollTop(log[0].scrollHeight);
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
			
			$("#bookTitle").text(data.bookData.title);
			$("#bookBlurb").text(data.bookData.blurb);
			$("#backCoverText").text(data.bookData.back_cover_text);
			$("#bookPrompt").html('<br><em>Prompt For Book:</em><br>' + data.bookData.prompt);

			
			// Populate rows
			for (let row of window.rows) {
				$('#kanbanBoard').append(`
                            <div class="kanban-row">
                                <h3>${row.title}</h3>
                                <div class="row kanban-row-ul" id="${row.id}-row" data-row="${row.id}"></div>
                            </div>
                        `);
			}
			
			// Initialize Sortable for each kanban row
			$('.kanban-row-ul').each(function () {
				new Sortable(this, {
					group: 'kanban', // set the same group for all rows
					animation: 150,
					scroll: false,
					direction: 'horizontal', // Ensure the direction is horizontal
					ghostClass: 'sortable-ghost', // Add a class to the ghost element
					onStart: function (evt) {
						isDragging = true;
					},
					onEnd: function (evt) {
						isDragging = false;
						
						const item = evt.item;
						const newRow = $(item).closest('.kanban-row-ul').attr('data-row');
						const chapterFilename = $(item).attr('data-chapter-filename');
						const newOrder = $(item).index(); // Get the new index/order
						
						// Update the order of all items in the row
						$(item).closest('.kanban-row-ul').children().each(function (index) {
							const chapterFilename = $(this).attr('data-chapter-filename');
							updateChapterRow(chapterFilename, newRow, index);
						});
					}
				});
			});
			
			
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
			
			loadStories();
			
			
		},
		error: function (xhr, status, error) {
			//redirect to login page
			window.location.href = 'login.php';
		}
	});
	
	$('#deleteChapterBtn').on('click', function (e) {
		e.preventDefault();
		$('#deleteConfirmationModal').modal({backdrop: 'static', keyboard: true}).modal('show');
	});
	
	$('#confirmDeleteBtn').on('click', function (e) {
		e.preventDefault();
		deleteChapter();
	});
	
	$('#chapterForm').on('submit', function (e) {
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
