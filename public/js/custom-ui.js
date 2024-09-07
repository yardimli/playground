let savedTheme = localStorage.getItem('theme') || 'light';
let savedLlm = localStorage.getItem('llm') || 'anthropic/claude-3-haiku:beta';


function __e(text, variables = {}) {
	// console.log('text: ' + text);
	// Get the translated text or use the original if not found
	let translatedText = translations[text] || ('???' + text);
	
	// Replace variables in the translated text
	for (const [key, value] of Object.entries(variables)) {
		translatedText = translatedText.replace(`\${${key}}`, value);
	}
	
	return translatedText;
}

function applyTheme(theme) {
	if (theme === 'dark') {
		$('body').addClass('dark-mode');
		$('#modeIcon').removeClass('bi-sun').addClass('bi-moon');
		$('#modeToggleBtn').attr('aria-label', 'Switch to Light Mode');
	} else {
		$('body').removeClass('dark-mode');
		$('#modeIcon').removeClass('bi-moon').addClass('bi-sun');
		$('#modeToggleBtn').attr('aria-label', 'Switch to Dark Mode');
	}
}

function exportAsPdf(bookStructure) {
	console.log(bookStructure);
	const {jsPDF} = window.jspdf;
	const doc = new jsPDF({
		unit: 'in',
		format: [6, 9]
	});
	
	// Load a Unicode font
	doc.addFont('/fonts/NotoSans-Regular.ttf', 'NotoSans', 'normal');
	doc.addFont('/fonts/NotoSans-Bold.ttf', 'NotoSans', 'bold');
	doc.addFont('/fonts/NotoSans-Italic.ttf', 'NotoSans', 'italic');
	
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
	addCenteredPage(bookStructure.title, 18, true);
	
	// Blurb
	addCenteredPage(bookStructure.blurb, 14, true);
	addPageBreak();
	
	// Back Cover Text
	addText(bookStructure.back_cover_text, 14, false);
	addPageBreak();
	
	bookStructure.acts.forEach((act, actIndex) => {
		if (bookStructure.language === 'Turkish') {
			act.title = act.title.replace('Act', 'Perde');
		}
		
		addCenteredPage(`${act.title}`); //Act ${actIndex + 1}:
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
	let simpleFilename = bookStructure.title.replace(/[^a-z0-9]/gi, '_').toLowerCase();
	doc.save(simpleFilename + '.pdf');
}

async function exportAsDocx(bookStructure) {
	console.log(bookStructure);
	
	const {Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType, PageBreak} = docx;
	
	let doc_children = [];
	
	function addText(text, size = 24, bold = false, alignment = AlignmentType.LEFT) {
		doc_children.push(new Paragraph({
			alignment: alignment,
			spacing: {
				line: 1.5 * 240
			},
			children: [
				new TextRun({
					text: text,
					bold: bold,
					size: size
				})
			]
		}));
	}
	
	function addPageBreak() {
		doc_children.push(new Paragraph({
			children: [new PageBreak()]
		}));
	}
	
	function addCenteredPage(text, size = 36, bold = true) {
		addText('');
		addText('');
		addText('');
		addText('');
		addText('');
		addText('');
		addText('');
		addText('');
		addText(text, size, bold, AlignmentType.CENTER);
	}
	
	// Title
	addCenteredPage(bookStructure.title);
	addPageBreak();
	
	// Blurb
	addText('');
	addText('');
	addText('');
	addText('');
	addText(bookStructure.blurb, 28, false, AlignmentType.JUSTIFIED);
	addPageBreak();
	
	// Back Cover Text
	addText(bookStructure.back_cover_text, 28, false, AlignmentType.JUSTIFIED);
	addPageBreak();
	
	bookStructure.acts.forEach((act, actIndex) => {
		if (bookStructure.language === 'Turkish') {
			act.title = act.title.replace('Act', 'Perde');
		}
		
		addCenteredPage(`${act.title}`);
		addPageBreak();
		
		act.chapters.forEach((chapter, chapterIndex) => {
			if (bookStructure.language === 'Turkish') {
				chapter.name = chapter.name.replace('Chapter', 'Bölüm');
			}
			
			// Chapter title
			addText('');
			addText(chapter.name, 32, true, AlignmentType.CENTER);
			addText('');
			addText('');
			
			// Beats
			if (chapter.beats && chapter.beats.length > 0) {
				chapter.beats.forEach((beat, beatIndex) => {
					if (beat.beat_text) {
						addText(beat.beat_text, 24, false, AlignmentType.JUSTIFIED);
						addText('');
					}
				});
			}
			
			addPageBreak();
			
			
		});
	});
	
	// Generate and save the document
	
	const doc = new Document({
		sections: [{
			properties: {
				page: {
					size: {
						width: 6 * 1440, // 6 inches in twips (1 inch = 1440 twips)
						height: 9 * 1440, // 9 inches in twips
					},
				},
			},
			children: doc_children
		}]
	});
	
	const blob = await Packer.toBlob(doc);
	const url = URL.createObjectURL(blob);
	const link = document.createElement('a');
	link.href = url;
	let simpleFilename = bookStructure.title.replace(/[^a-z0-9]/gi, '_').toLowerCase();
	link.download = simpleFilename + '.docx';
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
	URL.revokeObjectURL(url);
}

$(document).ready(function () {
	
	$('#exportPdfBtn').on('click', function (e) {
		e.preventDefault();
		exportAsPdf(bookData);
	});
	
	$('#exportTxtBtn').on('click', function (e) {
		e.preventDefault();
		exportAsDocx(bookData);
	});
	
	applyTheme(savedTheme);
	
	$("#ham-menu").on('click', function () {
		$('#ham-menu').toggleClass('is-active');
		$('#ham-menu-items').toggleClass('is-active');
	});
	
	//if the is-active class is added and the user clicks outside the menu, remove the class
	$(document).on('click', function (e) {
		if (!$(e.target).closest('#ham-menu-items').length && !$(e.target).closest('#ham-menu').length) {
			$('#ham-menu').removeClass('is-active');
			$('#ham-menu-items').removeClass('is-active');
		}
	});
	
	$("#llmSelect").on('change', function () {
		localStorage.setItem('llm', $(this).val());
		savedLlm = $(this).val();
	});
	
	// change $llmSelect to savedLlm
	console.log('set llmSelect to ' + savedLlm);
	var dropdown = document.getElementById('llmSelect');
	var options = dropdown.getElementsByTagName('option');

	
	for (var i = 0; i < options.length; i++) {
		if (options[i].value === savedLlm) {
				dropdown.selectedIndex = i;
		}
	}
	
	$('#modeToggleBtn').on('click', function () {
		const currentTheme = $('body').hasClass('dark-mode') ? 'dark' : 'light';
		const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
		localStorage.setItem('theme', newTheme);
		applyTheme(newTheme);
	});
	
	
	// Manage z-index for multiple modals
	$('.modal').on('show.bs.modal', function () {
		const zIndex = 1040 + (10 * $('.modal:visible').length);
		$(this).css('z-index', zIndex);
		setTimeout(function () {
			$('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
		}, 0);
	});
	
	$('.modal').on('hidden.bs.modal', function () {
		if ($('.modal:visible').length) {
			// Adjust the backdrop z-index when closing a modal
			$('body').addClass('modal-open');
		}
	});
	
	
});


