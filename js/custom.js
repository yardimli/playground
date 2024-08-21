const urlParams = new URLSearchParams(window.location.search);
const bookParam = urlParams.get('book');
const chapterParam = urlParams.get('chapter') ?? '';
let allBookChapters = [];
let bookData = {};

function loadStories() {
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

function updateChapterRow(chapterFilename, newRow, newOrder) {
	$.post('action-book.php', {
		action: 'update_chapter_row',
		llm:savedLlm,
		book: bookParam,
		chapterFilename: chapterFilename,
		row: newRow,
		order: newOrder
	}, function (response) {
		const chapter = JSON.parse(response);
	});
}


//----------------------------------------------------
//----------------- Global Variables -----------------

let isDragging = false;
let lastMouseY = 0;
let scrollTimeout = null;


//----------------------------------------------------
//----------------- Event Listeners ------------------

$(document).ready(function () {
	
	$('#exportPdfBtn').on('click', function (e) {
		e.preventDefault();
		
		$.post('action-book.php', {
			action: 'get_book_structure',
			llm: savedLlm,
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
	
	$('#showHistoryModal').on('click', function (e) {
		e.preventDefault();
		showHistoryModal(e, $('#chapterFilename').val());
	});
	
	
	
	// Attach mousemove event to track mouse position
	document.addEventListener('drag', function (event) {
		if (lastMouseY === event.clientY) return;
		lastMouseY = event.clientY;
		
		autoScroll();
	});
	
});
