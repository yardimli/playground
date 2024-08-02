function deleteUploadFile(event, uploadFilename, chapterFilename) {
	event.stopPropagation();
	if (confirm('Are you sure you want to delete this file?')) {
		$.post('action-other-functions.php', {
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
	$.post('action-other-functions.php', {action: 'fetch_all_history', book: bookParam}, function (data) {
		const histories = JSON.parse(data);
		const allHistoryList = $('#allHistoryList');
		allHistoryList.empty(); // Clear existing history
		histories.forEach(entry => {
			allHistoryList.append(createAllHistoryHtml(entry));
		});
		$('#allHistoryModal').modal({backdrop: 'static', keyboard: true}).modal('show');
	});
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

function showBookStructureModal() {
	$.post('action-book.php', {
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
