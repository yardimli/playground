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
	
	const beatsButton = `<button class="btn btn-sm btn-primary beat-btn" onclick="showBeatModal(event, '${chapter.chapterFilename}')">Beats</button>`;
	
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
				${beatsButton}
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

function deleteChapter() {
	const chapterFilename = $('#chapterFilename').val();
	$.post('action-book.php', {
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

function archiveChapter(event, chapterFilename) {
	event.stopPropagation();
	$.post('action-book.php', {
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
	$.post('action-book.php', {
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

$(document).ready(function () {
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
	
	$('#addChapterBtn').on('click', function (e) {
		e.preventDefault();
		addChapter();
	});
	
	$('#chapterModal').on('shown.bs.modal', function () {
		$('#chapterName').focus();
	});
	
	
});
