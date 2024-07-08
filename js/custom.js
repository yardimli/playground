const urlParams = new URLSearchParams(window.location.search);
const bookParam = urlParams.get('book');

function loadStories(showArchived = false) {
	//empty the rows
	$('.kanban-row-ul').empty();
	
	$.post('action.php', {action: 'load_stories', book: bookParam, showArchived: showArchived}, function (data) {
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
				$(`.kanban-row-ul[data-row="${row}"]`).append(chapterCard);
			});
		});
	});
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
	
	const archiveButton = chapter.archived ?
		`<button class="btn btn-sm btn-warning archive-btn" onclick="unarchiveChapter(event, '${chapter.chapterFilename}')">Unarchive</button>` :
		`<button class="btn btn-sm btn-secondary archive-btn" onclick="archiveChapter(event, '${chapter.chapterFilename}')">Archive</button>`;
	
	const archivedLabel = chapter.archived ? '<span class="badge bg-secondary">Archived</span> ' : '';
	
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
			  ${archiveButton}
				${archivedLabel}
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

function createCommentHtml(comment) {
	const commentTime = formatRelativeTime(comment.timestamp);
	const editDeleteButtons = comment.user === currentUser ? `
        <button class="btn btn-sm btn-warning" onclick="editComment(event, '${comment.id}', '${comment.chapterFilename}')">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteComment(event, '${comment.id}', '${comment.chapterFilename}')">Delete</button>
    ` : '';
	return `
        <div class="comment mb-2" data-id="${comment.id}">
            <p>${comment.text}</p>
            <p><strong>${comment.user}</strong> <span title="${moment.utc(comment.timestamp).local().format('LLLL')}">${commentTime}</span></p>
            ${editDeleteButtons}
        </div>`;
}

function showCommentModal(event, chapterFilename) {
	event.stopPropagation();
	$('#commentChapterFilename').val(chapterFilename);
	$('#commentId').val('');
	$('#commentText').val('');
	$('#commentModalLabel').text('Add Comment');
	$('#commentModal').modal({backdrop: 'static', keyboard: true}).modal('show');
}

function editComment(event, commentId, chapterFilename) {
	event.stopPropagation();
	const comment = $(`.comment[data-id="${commentId}"]`);
	const commentText = comment.find('p:first').text();
	$('#commentChapterFilename').val(chapterFilename);
	$('#commentId').val(commentId);
	$('#commentText').val(commentText);
	$('#commentModalLabel').text('Edit Comment');
	$('#commentModal').modal({backdrop: 'static', keyboard: true}).modal('show');
}

function deleteComment(event, commentId, chapterFilename) {
	event.stopPropagation();
	if (confirm('Are you sure you want to delete this comment?')) {
		$.post('action.php', {
			action: 'delete_comment',
			book: bookParam,
			id: commentId,
			chapterFilename: chapterFilename
		}, function (response) {
			if (response.success) {
				$(`.comment[data-id="${commentId}"]`).remove();
			}
		}, 'json');
	}
}

function saveComment() {
	const commentData = {
		action: 'save_comment',
		book: bookParam,
		chapterFilename: $('#commentChapterFilename').val(),
		id: $('#commentId').val(),
		text: $('#commentText').val(),
	};
	$.post('action.php', commentData, function (response) {
		$('#commentModal').modal('hide');
		$('#commentForm')[0].reset();
		const comment = JSON.parse(response);
		comment.chapterFilename = commentData.chapterFilename; // Add chapterFilename to the comment
		const commentsList = $('#commentsList');
		$('.comments-section').show();
		if (comment.isNew) {
			commentsList.append(createCommentHtml(comment));
		} else {
			const commentElement = commentsList.find(`.comment[data-id="${comment.id}"]`);
			commentElement.replaceWith(createCommentHtml(comment));
		}
	});
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
	
	let files = $('#chapterFiles')[0].files;
	for (let i = 0; i < files.length; i++) {
		formData.append('files[]', files[i]);
	}
	
	$.ajax({
		url: 'action.php',
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

function deleteUploadFile(event, uploadFilename, chapterFilename) {
	event.stopPropagation();
	if (confirm('Are you sure you want to delete this file?')) {
		$.post('action.php', {
			action: 'delete_file',
			book: bookParam,
			uploadFilename: uploadFilename,
			chapterFilename: chapterFilename
		}, function (response) {
			if (response.success) {
				$(`.file[data-uploadFilename="${uploadFilename}"]`).remove();
			}
		}, 'json');
	}
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

function addChapter() {
	$('#save_result').html('');
	$('#chapterFilename').val('');
	$('#chapterName').val('');
	$('#chapterText').val('');
	$('#chapterFiles').val('');
	
	const commentsList = $('#commentsList');
	commentsList.empty(); // Clear existing comments
	$('.comments-section').hide();
	
	const UploadFilesList = $('#UploadFilesList');
	UploadFilesList.empty(); // Clear existing files
	$('.upload-files-section').hide();
	
	$('#colorPalette button').removeClass('active').first().click(); // Reset the color selection to default
	
	//hide the add comment button
	$('#showCommentModal').hide();
	
	$('#chapterModal').modal({backdrop: 'static', keyboard: true}).modal('show');
}

function updateChapterRow(chapterFilename, newRow, newOrder) {
	$.post('action.php', {
		action: 'update_chapter_row',
		book: bookParam,
		chapterFilename: chapterFilename,
		row: newRow,
		order: newOrder
	}, function (response) {
		const chapter = JSON.parse(response);
	});
}

function createUploadFileHtml(uploadFile) {
	const isImage = /\.(jpg|jpeg|png|gif)$/i.test(uploadFile.uploadFilename);
	const deleteButton = `<button class="btn btn-sm btn-danger" onclick="deleteUploadFile(event, '${uploadFile.uploadFilename}', '${uploadFile.chapterFilename}')">Delete</button>`;
	const uploadFileLink = `${chaptersDirName}/uploads/${uploadFile.uploadFilename}`;
	
	let uploadFileHtml = `<div class="uploadFile mb-2 col-4" style="border: 1px solid #ccc; padding: 5px;" data-uploadFilename="${uploadFile.uploadFilename}">`;
	
	if (isImage) {
		uploadFileHtml += `<a href="${uploadFileLink}" target="_blank"><img src="${uploadFileLink}" alt="${uploadFile.uploadFilename}" style="max-width: 100px; max-height: 100px; margin-right: 10px;"></a>`;
	}
	
	uploadFileHtml += `<a href="${uploadFileLink}" target="_blank">${uploadFile.uploadFilename}</a> ${deleteButton}</div>`;
	
	return uploadFileHtml;
}

function updateUploadFilesList(chapter, chapterFilename) {
	const UploadFilesList = $('#UploadFilesList');
	UploadFilesList.empty(); // Clear existing files
	$(".upload-files-section").hide();
	
	if (chapter.files) {
		chapter.files.forEach(uploadFile => {
			$(".upload-files-section").show();
			uploadFile.chapterFilename = chapterFilename;
			UploadFilesList.append(createUploadFileHtml(uploadFile));
		});
	}
}

function autoScroll() {
	if (!isDragging) return;
	
	clearTimeout(scrollTimeout);
	
	const scrollSensitivity = 60; // Distance from the edge of the viewport to start scrolling
	const scrollSpeed = 200; // Speed at which the page scrolls
	const viewportHeight = window.innerHeight;
	if (lastMouseY < scrollSensitivity) {
		// Scroll up
		window.scrollBy(0, -scrollSpeed);
		scrollTimeout = setTimeout(() => {
			autoScroll();
		}, 100);
	} else if (lastMouseY > viewportHeight - scrollSensitivity) {
		// Scroll down
		window.scrollBy(0, scrollSpeed);
		scrollTimeout = setTimeout(() => {
			autoScroll();
		}, 100);
	}
}

function deleteChapter() {
	const chapterFilename = $('#chapterFilename').val();
	$.post('action.php', {
		action: 'delete_chapter',
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

function createHistoryHtml(history) {
	const historyTime = formatRelativeTime(history.timestamp);
	return `
        <div class="history-entry mb-1">
            ${moment.utc(history.timestamp).local().format('LLLL')} <strong>${history.user}</strong> ${history.action}
        </div>`;
}

function showHistoryModal(event, chapterFilename) {
	event.stopPropagation();
	fetch(`${chaptersDirName}/${chapterFilename}`)
		.then(response => response.json())
		.then(chapter => {
			const historyList = $('#historyList');
			historyList.empty(); // Clear existing history
			if (chapter.history) {
				chapter.history.forEach(entry => {
					historyList.append(createHistoryHtml(entry));
				});
			}
			$('#historyModal').modal({backdrop: 'static', keyboard: true}).modal('show');
		})
		.catch(error => console.error('Error loading history:', error));
}

function createAllHistoryHtml(history) {
	const historyTime = formatRelativeTime(history.timestamp);
	return `
        <div class="history-entry mb-1">
            ${moment.utc(history.timestamp).local().format('LLLL')} <strong>${history.title}</strong> <strong>${history.user}</strong> ${history.action}
        </div>`;
}

function showAllHistoryModal() {
	$.post('action.php', {action: 'fetch_all_history', book: bookParam}, function (data) {
		const histories = JSON.parse(data);
		const allHistoryList = $('#allHistoryList');
		allHistoryList.empty(); // Clear existing history
		histories.forEach(entry => {
			allHistoryList.append(createAllHistoryHtml(entry));
		});
		$('#allHistoryModal').modal({backdrop: 'static', keyboard: true}).modal('show');
	});
}

function archiveChapter(event, chapterFilename) {
	event.stopPropagation();
	$.post('action.php', {
		'action': 'archive_chapter',
		book: bookParam,
		chapterFilename: chapterFilename,
		archived: true
	}, function (response) {
		if (response.success) {
			$(`.kanban-card-col[data-chapter-filename="${chapterFilename}"]`).remove();
		}
	}, 'json');
}

function unarchiveChapter(event, chapterFilename) {
	event.stopPropagation();
	$.post('action.php', {
		action: 'archive_chapter',
		book: bookParam,
		chapterFilename: chapterFilename,
		archived: false
	}, function (response) {
		if (response.success) {
			loadStories(true);
		}
	}, 'json');
}


function showBeatModal(event, chapterFilename) {
	event.stopPropagation();
	fetch(`${chaptersDirName}/${chapterFilename}`)
		.then(response => response.json())
		.then(chapter => {
			const beatsList = $('#beatsList');
			beatsList.empty(); // Clear existing beats
			if (chapter.beats) {
				beatsList.data('beats', chapter.beats);
				chapter.beats.forEach((beat, index) => {
					const beatHtml = `
            <div class="mb-3">
              <h5>Beat ${index + 1}</h5>
              <p>${beat.description}</p>
              <button class="btn btn-primary btn-sm view-beat-btn" data-beat-index="${index}">View Details              ${beat.beat_text ? '<span class="ms-2 text-success">✓ Text Written</span>' : ''}</button>
            </div>
          `;
					beatsList.append(beatHtml);
					
					// beatsList.append(`
					//               <div class="mb-3">
					//                   <label for="beat${index}" class="form-label">Beat ${index + 1}</label>
					//                   <textarea class="form-control beat-textarea" id="beat${index}" rows="3">${beat.description}</textarea>
					//               </div>
					// `);
				});
				
				$('.view-beat-btn').off('click').on('click', function () {
					const beatIndex = $(this).data('beat-index');
					showBeatDetailModal(beatIndex, chapterFilename);
				});
			}
			$('#beatModal').modal({backdrop: 'static', keyboard: true}).modal('show');
		})
		.catch(error => console.error('Error loading beats:', error));
}

function saveBeats() {
	const chapterFilename = $('#chapterFilename').val();
	const beats = $('.beat-textarea').map(function (index) {
		return {
			description: $(this).val()
		};
	}).get();
	
	$.post('action.php', {
		action: 'save_beats',
		book: bookParam,
		chapterFilename: chapterFilename,
		beats: JSON.stringify(beats)
	}, function (response) {
		if (response.success) {
			$('#beatModal').modal('hide');
			alert('Beats saved successfully!');
		} else {
			alert('Failed to save beats: ' + response.message);
		}
	}, 'json');
}

function createBeats() {
	const chapterFilename = $('#chapterFilename').val();
	const chapterName = $('#chapterName').val();
	const chapterText = $('#chapterText').val();
	const chapterEvents = $('#chapterEvents').val();
	const chapterPeople = $('#chapterPeople').val();
	const chapterPlaces = $('#chapterPlaces').val();
	const chapterFromPrevChapter = $('#chapterFromPrevChapter').val();
	const chapterToNextChapter = $('#chapterToNextChapter').val();
	
	let spinner = $('#beat-spinner');
	spinner.removeClass('d-none');
	
	// First, fetch all chapters to find the previous chapter
	$.post('action.php', {action: 'get_all_chapters', book: bookParam}, function (response) {
		let chapters = JSON.parse(response);
		
		// Sort chapters incrementally
		chapters.sort((a, b) => {
			const aMatch = a.name.match(/\d+/);
			const bMatch = b.name.match(/\d+/);
			const aNum = aMatch ? parseInt(aMatch[0]) : 0;
			const bNum = bMatch ? parseInt(bMatch[0]) : 0;
			return aNum - bNum;
		});
		
		// Find the current chapter and the previous chapter
		let currentChapterIndex = chapters.findIndex(chapter => chapter.chapterFilename === chapterFilename);
		let prevChapter = currentChapterIndex > 0 ? chapters[currentChapterIndex - 1] : null;
		
		let prevChapterBeats = '';
		if (prevChapter && prevChapter.beats && prevChapter.beats.length > 0) {
			prevChapterBeats = `${prevChapter.beats.map((beat, index) => `Beat ${index + 1}: ${beat.description}`).join('\n')}`;
		}
		
		// Now proceed with creating beats
		$.ajax({
			url: 'action.php',
			method: 'POST',
			data: {
				action: 'write_beats',
				book: bookParam,
				chapterFilename: chapterFilename,
				chapterName: chapterName,
				chapterText: chapterText,
				chapterEvents: chapterEvents,
				chapterPeople: chapterPeople,
				chapterPlaces: chapterPlaces,
				chapterFromPrevChapter: prevChapterBeats || chapterFromPrevChapter,
				chapterToNextChapter: chapterToNextChapter
			},
			dataType: 'json',
			success: function (response) {
				spinner.addClass('d-none');
				if (response.success) {
					const beatsList = $('#beatsList');
					beatsList.empty();
					response.beats.forEach((beat, index) => {
						beatsList.append(`
                            <div class="mb-3">
                                <label for="beat${index}" class="form-label">Beat ${index + 1}</label>
                                <textarea class="form-control beat-textarea" id="beat${index}" rows="3">${beat.description}</textarea>
                            </div>
                        `);
					});
					$('#beatModal').modal({backdrop: 'static', keyboard: true}).modal('show');
				} else {
					alert('Failed to create beats: ' + response.message);
				}
			},
			error: function () {
				spinner.addClass('d-none');
				alert('An error occurred while creating beats.');
			}
		});
	});
}


// Function to generate beats for all chapters
function generateAllBeats() {
	const modal = $('#generateAllBeatsModal');
	const progressBar = modal.find('.progress-bar');
	const log = $('#generateAllBeatsLog');
	modal.modal({backdrop: 'static', keyboard: true}).modal('show');
	log.empty();
	progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
	
	$.post('action.php', {action: 'get_all_chapters', book: bookParam}, function (response) {
		let chapters = JSON.parse(response);
		
		// Sort chapters incrementally
		chapters.sort((a, b) => {
			const aMatch = a.name.match(/\d+/);
			const bMatch = b.name.match(/\d+/);
			const aNum = aMatch ? parseInt(aMatch[0]) : 0;
			const bNum = bMatch ? parseInt(bMatch[0]) : 0;
			return aNum - bNum;
		});
		
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
						url: 'action.php',
						method: 'POST',
						data: {
							action: 'write_beats',
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
									url: 'action.php',
									method: 'POST',
									data: {
										action: 'save_beats',
										book: bookParam,
										chapterFilename: chapter.chapterFilename,
										beats: JSON.stringify(response.beats)
									},
									dataType: 'json',
									success: function (saveResponse) {
										if (saveResponse.success) {
											log.append(`<br>Beats generated and saved for chapter: ${chapter.name}`);
											lastChapterBeats = response.beats;
											
											response.beats.forEach((beat, index) => {
												log.append(`<br>${beat.description}`);
											});
											
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


function showBeatDetailModal(beatIndex, chapterFilename) {
	const beatsList = $('#beatsList');
	const beat = beatsList.data('beats')[beatIndex];
	
	$('#beatDescription').text(beat.description);
	$('#beatDetailModalLabel').text(`Beat ${beatIndex + 1}`);
	
	$('#writeBeatTextBtn').off('click').on('click', function () {
		writeBeatText(beatIndex, chapterFilename);
	});
	
	$('#beatDetailModal').modal('show');
}

function writeBeatText(beatIndex, chapterFilename) {
	$.post('action.php', {
		action: 'get_beat_prompt',
		book: bookParam,
		chapterFilename: chapterFilename,
		beatIndex: beatIndex
	}, function (response) {
		if (response.success) {
			$('#beatTextArea').show();
			$('#beatText').val(response.prompt);
			$('#saveBeatTextBtn').off('click').on('click', function () {
				saveBeatText(beatIndex, chapterFilename);
			});
		} else {
			alert('Failed to get beat prompt: ' + response.message);
		}
	}, 'json');
}

function saveBeatText(beatIndex, chapterFilename) {
	const beatText = $('#beatText').val();
	$.post('action.php', {
		action: 'save_beat_text',
		book: bookParam,
		chapterFilename: chapterFilename,
		beatIndex: beatIndex,
		beatText: beatText
	}, function (response) {
		if (response.success) {
			alert('Beat text saved successfully!');
			$('#beatDetailModal').modal('hide');
			showBeatModal(null, chapterFilename);
		} else {
			alert('Failed to save beat text: ' + response.message);
		}
	}, 'json');
}

function showBookStructureModal() {
	$.post('action.php', {
		action: 'get_book_structure',
		book: bookParam
	}, function (response) {
		if (response.success) {
			let structureHtml = '<h2>' + response.bookTitle + '</h2>';
			structureHtml += '<p><i>Blurb:</i> ' + response.bookBlurb + '</p>';
			structureHtml += '<p><i>Back Cover Text:</i> ' + response.bookBackCoverText + '</p>';
			
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
						structureHtml += '<ul>';
						chapter.beats.forEach(function (beat) {
							structureHtml += '<li>' + beat.description + '</li>';
						});
						structureHtml += '</ul>';
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


function exportAsPdf(bookStructure) {
	const {jsPDF} = window.jspdf;
	const doc = new jsPDF({
		unit: 'in',
		format: [6, 9]
	});
	
	// Load a Unicode font
	doc.addFont('PlayfairDisplay-VariableFont_wght.ttf  ', 'Roboto', 'normal');
	doc.addFont('PlayfairDisplay-VariableFont_wght.ttf', 'Roboto', 'bold');
	
	// Set default font to Roboto
	doc.setFont('Roboto', 'normal');
	
	// Set font to a serif font
	// doc.setFont('times', 'normal');
	
	const lineHeight = 0.25; // Increased line height
	let yPosition = 0.75; // Increased top margin
	const pageHeight = 8.5;
	const pageWidth = 6;
	const margin = 0.75; // Increased side margins
	let pageNumber = 1;
	let currentFontSize = 12;
	let currentFontStyle = 'normal';
	
	function setFont(fontSize = 12, isBold = false) {
		currentFontSize = fontSize;
		currentFontStyle = isBold ? 'bold' : 'normal';
		doc.setFontSize(fontSize);
		// doc.setFont('times', currentFontStyle);
		doc.setFont('Roboto', currentFontStyle);
	}
	
	function addText(text, fontSize = 12, isBold = false, align = 'left') {
		setFont(fontSize, isBold);
		const splitText = doc.splitTextToSize(text, pageWidth - 2 * margin);
		splitText.forEach(line => {
			if (yPosition > pageHeight - margin) {
				addPageNumber();
				doc.addPage();
				yPosition = margin;
				pageNumber++;
				setFont(currentFontSize, currentFontStyle === 'bold');
			}
			
			// Check if the line starts with a label (e.g., "Events:", "Places:")
			const labelMatch = line.match(/^(\w+:)/);
			if (labelMatch) {
				const label = labelMatch[1];
				const restOfLine = line.substring(label.length);
				
				// Draw the label in bold
				doc.setFont(undefined, 'bold');
				doc.text(label, align === 'center' ? pageWidth / 2 : margin, yPosition, {align: align});
				
				// Draw the rest of the line in normal font
				doc.setFont(undefined, 'normal');
				doc.text(restOfLine, align === 'center' ? pageWidth / 2 : margin + doc.getTextWidth(label), yPosition);
			} else {
				// Draw the entire line normally if it's not a label
				doc.text(line, align === 'center' ? pageWidth / 2 : margin, yPosition, {align: align});
			}
			
			yPosition += lineHeight;
		});
		yPosition += 0.1; // Add a small gap after each text block
	}
	
	function addPageBreak() {
		addPageNumber();
		doc.addPage();
		yPosition = margin;
		pageNumber++;
		setFont(currentFontSize, currentFontStyle === 'bold');
	}
	
	function addPageNumber() {
		const currentFont = doc.getFont();
		const currentFontSize = doc.getFontSize();
		doc.setFontSize(10);
		doc.setFont(undefined, 'normal');
		doc.text(String(pageNumber), pageWidth - margin + 0.2, pageHeight - margin + 0.4, {align: 'right'});
		doc.setFontSize(currentFontSize);
		doc.setFont(currentFont.fontName, currentFont.fontStyle);
	}
	
	
	function addCenteredPage(text) {
		addPageBreak();
		setFont(18, true);
		const textLines = doc.splitTextToSize(text, pageWidth - 2 * margin);
		const textHeight = textLines.length * lineHeight;
		const startY = (pageHeight - textHeight) / 2;
		doc.text(textLines, pageWidth / 2, startY, {align: 'center'});
	}
	
	// Title
	addText(bookStructure.bookTitle, 18, true, 'center');
	
	// Blurb
	addText('Blurb:', 14, true);
	addText(bookStructure.bookBlurb);
	
	// Back Cover Text
	addText('Back Cover Text:', 14, true);
	addText(bookStructure.bookBackCoverText);
	
	bookStructure.acts.forEach((act, actIndex) => {
		addCenteredPage(`Act ${actIndex + 1}: ${act.name}`);
		act.chapters.forEach((chapter, chapterIndex) => {
			addPageBreak();
			
			// Chapter title
			addText(chapter.name, 14, true);
			addText(chapter.short_description);
			
			// Chapter details
			addText(`Events: ${Array.isArray(chapter.events) ? chapter.events.join(', ') : chapter.events}`);
			addText(`People: ${Array.isArray(chapter.people) ? chapter.people.join(', ') : chapter.people}`);
			addText(`Places: ${Array.isArray(chapter.places) ? chapter.places.join(', ') : chapter.places}`);
			addText(`Previous: ${chapter.from_prev_chapter}`);
			addText(`Next: ${chapter.to_next_chapter}`);
			
			// Beats
			if (chapter.beats && chapter.beats.length > 0) {
				addText('Beats:', 12, true);
				chapter.beats.forEach((beat, beatIndex) => {
					addText(`${beatIndex + 1}. ${beat.description}`);
				});
			}
		});
	});
	
	addPageNumber(); // Add page number to the last page
	doc.save('book_structure.pdf');
}

//----------------------------------------------------
//----------------- Global Variables -----------------

let isDragging = false;
let lastMouseY = 0;
let scrollTimeout = null;


//----------------------------------------------------
//----------------- Event Listeners ------------------

$(document).ready(function () {
	
	// Fetch initial data
	$.ajax({
		url: 'action.php',
		method: 'POST',
		data: {action: 'fetch_initial_data', book: bookParam},
		dataType: 'json',
		success: function (data) {
			window.colorOptions = data.colorOptions;
			window.chaptersDirName = data.chaptersDirName;
			window.users = data.users;
			window.currentUser = data.currentUser;
			window.defaultRow = data.defaultRow;
			window.rows = data.rows;
			
			$("#bookTitle").text(data.bookData.title);
			$("#bookBlurb").text(data.bookData.blurb);
			$("#bookBackCoverText").text(data.bookData.back_cover_text);
			
			// Set the current user in the HTML
			$('#currentUser').text(window.currentUser);
			
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
	
	$('#toggleArchivedBtn').on('click', function (e) {
		e.preventDefault();
		const isShowingArchived = $(this).html() === '<i class="bi bi-archive"></i>';
		$(this).html(isShowingArchived ? '<i class="bi bi-archive-fill"></i>' : '<i class="bi bi-archive"></i>');
		loadStories(isShowingArchived);
	});
	
	$('#exportPdfBtn').on('click', function (e) {
		e.preventDefault();
		
		$.post('action.php', {
			action: 'get_book_structure',
			book: bookParam
		}, function (response) {
			if (response.success) {
				console.log(response);
				exportAsPdf(response);
			} else {
				alert('Failed to load book structure: ' + response.message);
			}
		}, 'json');
	});
	
	
	$('#showAllHistoryBtn').on('click', function (e) {
		e.preventDefault();
		showAllHistoryModal();
	});
	
	$("#showBeatModal").on('click', function (e) {
		e.preventDefault();
		showBeatModal(e, $('#chapterFilename').val());
	});
	
	$('#saveBeatsBtn').on('click', function (e) {
		e.preventDefault();
		saveBeats();
	});
	
	$("#createBeats").on('click', function (e) {
		e.preventDefault();
		createBeats();
	});
	
	$('#generateAllBeatsBtn').on('click', function (e) {
		e.preventDefault();
		generateAllBeats();
	});
	
	$('#showBookStructureBtn').on('click', function (e) {
		e.preventDefault();
		showBookStructureModal();
	});
	
	$('#showHistoryModal').on('click', function (e) {
		e.preventDefault();
		showHistoryModal(e, $('#chapterFilename').val());
	});
	
	$('#deleteChapterBtn').on('click', function (e) {
		e.preventDefault();
		$('#deleteConfirmationModal').modal({backdrop: 'static', keyboard: true}).modal('show');
	});
	
	// Attach click event to confirm delete button in the confirmation modal
	$('#confirmDeleteBtn').on('click', function (e) {
		e.preventDefault();
		deleteChapter();
	});
	
	$('#chapterForm').on('submit', function (e) {
		e.preventDefault();
		saveChapter();
	});
	
	$("#showCommentModal").on('click', function (e) {
		e.preventDefault();
		showCommentModal(e, $('#chapterFilename').val());
	});
	
	$('#commentForm').on('submit', function (e) {
		e.preventDefault();
		saveComment();
	});
	
	$('#addChapterBtn').on('click', function (e) {
		e.preventDefault();
		addChapter();
	});
	
	$('#chapterModal').on('shown.bs.modal', function () {
		$('#chapterName').focus();
	});
	
	$('#commentModal').on('shown.bs.modal', function () {
		$('#commentText').focus();
	});
	
	
	// Add User Modal
	$('#generateUser').on('click', function () {
		const userName = $('#userName').val().replace(/\s+/g, '').replace(/[^\w\-]/g, '');
		const userPassword = $('#userPassword').val();
		
		if (userName && userPassword) {
			$.post('action.php', {
				action: 'generate_user',
				book: bookParam,
				username: userName,
				password: userPassword
			}, function (response) {
				$('#userJsonOutput').text(response);
			});
		}
	});
	
	// Copy to clipboard
	$('#copyUserJson').on('click', function () {
		const textToCopy = $('#userJsonOutput').text();
		navigator.clipboard.writeText(textToCopy).then(function () {
			alert('Copied to clipboard!');
		}, function (err) {
			console.error('Could not copy text: ', err);
		});
	});
	
	// Attach mousemove event to track mouse position
	document.addEventListener('drag', function (event) {
		if (lastMouseY === event.clientY) return;
		lastMouseY = event.clientY;
		
		autoScroll();
	});
	
});
