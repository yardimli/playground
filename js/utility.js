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

function exportAsPdfDebug(bookStructure) {
	console.log(bookStructure);
	const {jsPDF} = window.jspdf;
	const doc = new jsPDF({
		unit: 'in',
		format: [6, 9]
	});
	
	// Load a Unicode font
	doc.addFont('./fonts/NotoSans-Regular.ttf', 'NotoSans', 'normal');
	doc.addFont('./fonts/NotoSans-Bold.ttf', 'NotoSans', 'bold');
	doc.addFont('./fonts/NotoSans-Italic.ttf', 'NotoSans', 'italic');
	
	// Set default font to Roboto
	doc.setFont('NotoSans', 'normal');
	
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
		doc.setFont('NotoSans', currentFontStyle);
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
			const labelMatch = line.match(/^([\p{L}\p{N}_]+:)/u);
			// const labelMatch = line.match(/^(\w+:)/);
			if (labelMatch) {
				const label = labelMatch[1];
				const restOfLine = line.substring(label.length);
				
				// Draw the label in bold
				doc.setFont('NotoSans', 'bold');
				doc.text(label, align === 'center' ? pageWidth / 2 : margin, yPosition, {align: align});
				
				// Draw the rest of the line in normal font
				doc.setFont('NotoSans', 'normal');
				doc.text(restOfLine, align === 'center' ? pageWidth / 2 : margin + doc.getTextWidth(label), yPosition);
			} else {
				// Draw the entire line normally if it's not a label
				doc.text(line, align === 'center' ? pageWidth / 2 : margin, yPosition, {align: align});
			}
			
			yPosition += lineHeight;
		});
		yPosition += 0.2; // Add a small gap after each text block
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
		doc.setFont('NotoSans', 'normal');
		doc.text(String(pageNumber), pageWidth - margin + 0.2, pageHeight - margin + 0.4, {align: 'right'});
		doc.setFontSize(currentFontSize);
		doc.setFont(currentFont.fontName, currentFont.fontStyle);
	}
	
	
	function addCenteredPage(text, fontSize = 18, isBold = true) {
		addPageBreak();
		setFont(fontSize, isBold);
		const textLines = doc.splitTextToSize(text, pageWidth - 2 * margin);
		const textHeight = textLines.length * lineHeight;
		const startY = (pageHeight - textHeight) / 2;
		doc.text(textLines, pageWidth / 2, startY, {align: 'center'});
	}
	
	// Title
	addCenteredPage(bookStructure.bookTitle, 18, true);
	
	// Blurb
	addCenteredPage(bookStructure.bookBlurb,14,true);
	addPageBreak();
	
	// Back Cover Text
	addText(bookStructure.backCoverText, 14, false);
	addPageBreak();
	
	bookStructure.acts.forEach((act, actIndex) => {
		if (bookStructure.language === 'Turkish') {
			act.name = act.name.replace('Act', 'Perde');
		}
		
		addCenteredPage(`${act.name}`); //Act ${actIndex + 1}:
		act.chapters.forEach((chapter, chapterIndex) => {
			addPageBreak();
			
			if (bookStructure.language === 'Turkish') {
				chapter.name = chapter.name.replace('Chapter', 'Bölüm');
			}
				
				// Chapter title
			addText(chapter.name, 14, true);
			addText(chapter.short_description);
			
			if (bookStructure.language === 'Turkish') {
				addText(`Olaylar:  ${Array.isArray(chapter.events) ? chapter.events.join(', ') : chapter.events}`);
				addText(`Kişiler:  ${Array.isArray(chapter.people) ? chapter.people.join(', ') : chapter.people}`);
				addText(`Mekanlar:  ${Array.isArray(chapter.places) ? chapter.places.join(', ') : chapter.places}`);
				addText(`Önceki:  ${chapter.from_prev_chapter}`);
				addText(`Sonraki:  ${chapter.to_next_chapter}`);
			} else {
				
				// Chapter details
				addText(`Events: ${Array.isArray(chapter.events) ? chapter.events.join(', ') : chapter.events}`);
				addText(`People: ${Array.isArray(chapter.people) ? chapter.people.join(', ') : chapter.people}`);
				addText(`Places: ${Array.isArray(chapter.places) ? chapter.places.join(', ') : chapter.places}`);
				addText(`Previous: ${chapter.from_prev_chapter}`);
				addText(`Next: ${chapter.to_next_chapter}`);
			}
			
			// Beats
			if (chapter.beats && chapter.beats.length > 0) {
				if (bookStructure.language === 'Turkish') {
					addText('İçerik:', 12, true);
				} else {
					addText('Beats:', 12, true);
				}
				chapter.beats.forEach((beat, beatIndex) => {
					if (beat.beat_text) {
						addText(beat.beat_text);
						// addText('____________________');
						addText('');
					} else
					{
						addText(`${beatIndex + 1}. ${beat.description}`);
					}
				});
			}
		});
	});
	
	addPageNumber(); // Add page number to the last page
	doc.save('book_structure.pdf');
}


function exportAsPdf(bookStructure) {
	console.log(bookStructure);
	const {jsPDF} = window.jspdf;
	const doc = new jsPDF({
		unit: 'in',
		format: [6, 9]
	});
	
	// Load a Unicode font
	doc.addFont('./fonts/NotoSans-Regular.ttf', 'NotoSans', 'normal');
	doc.addFont('./fonts/NotoSans-Bold.ttf', 'NotoSans', 'bold');
	doc.addFont('./fonts/NotoSans-Italic.ttf', 'NotoSans', 'italic');
	
	// Set default font to Roboto
	doc.setFont('NotoSans', 'normal');
	
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
		doc.setFont('NotoSans', currentFontStyle);
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
			
			doc.text(line, align === 'center' ? pageWidth / 2 : margin, yPosition, {align: align});
			
			yPosition += lineHeight;
		});
		yPosition += 0.2; // Add a small gap after each text block
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
		doc.setFont('NotoSans', 'normal');
		doc.text(String(pageNumber), pageWidth - margin + 0.2, pageHeight - margin + 0.4, {align: 'right'});
		doc.setFontSize(currentFontSize);
		doc.setFont(currentFont.fontName, currentFont.fontStyle);
	}
	
	
	function addCenteredPage(text, fontSize = 18, isBold = true) {
		addPageBreak();
		setFont(fontSize, isBold);
		const textLines = doc.splitTextToSize(text, pageWidth - 2 * margin);
		const textHeight = textLines.length * lineHeight;
		const startY = (pageHeight - textHeight) / 2;
		doc.text(textLines, pageWidth / 2, startY, {align: 'center'});
	}
	
	// Title
	addCenteredPage(bookStructure.bookTitle, 18, true);
	
	// Blurb
	addCenteredPage(bookStructure.bookBlurb,14,true);
	addPageBreak();
	
	// Back Cover Text
	addText(bookStructure.backCoverText, 14, false);
	addPageBreak();
	
	bookStructure.acts.forEach((act, actIndex) => {
		if (bookStructure.language === 'Turkish') {
			act.name = act.name.replace('Act', 'Perde');
		}
		
		addCenteredPage(`${act.name}`); //Act ${actIndex + 1}:
		act.chapters.forEach((chapter, chapterIndex) => {
			addPageBreak();
			
			if (bookStructure.language === 'Turkish') {
				chapter.name = chapter.name.replace('Chapter', 'Bölüm');
			}
			
			// Chapter title
			addText(chapter.name, 14, true);
			
			// Beats
			if (chapter.beats && chapter.beats.length > 0) {

				chapter.beats.forEach((beat, beatIndex) => {
					if (beat.beat_text) {
						addText(beat.beat_text);
						// addText('____________________');
						addText('');
					}
				});
			}
		});
	});
	
	addPageNumber(); // Add page number to the last page
	doc.save('book_structure.pdf');
}
