const urlParams = new URLSearchParams(window.location.search);
const bookParam = urlParams.get('book');

function loadStories(showArchived = false) {
	//empty the rows
	$('.kanban-row-ul').empty();
	
	$.post('action-book.php', {action: 'load_stories', book: bookParam, showArchived: showArchived}, function (data) {
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

function updateChapterRow(chapterFilename, newRow, newOrder) {
	$.post('action-book.php', {
		action: 'update_chapter_row',
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
	
	// Fetch initial data
	$.ajax({
		url: 'action-book.php',
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
		
		$.post('action-book.php', {
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
	
	$('#showBookStructureBtn').on('click', function (e) {
		e.preventDefault();
		showBookStructureModal();
	});
	
	$('#showHistoryModal').on('click', function (e) {
		e.preventDefault();
		showHistoryModal(e, $('#chapterFilename').val());
	});
	
	$('#generateUser').on('click', function () {
		const userName = $('#userName').val().replace(/\s+/g, '').replace(/[^\w\-]/g, '');
		const userPassword = $('#userPassword').val();
		
		if (userName && userPassword) {
			$.post('action-other-functions.php', {
				action: 'generate_user',
				book: bookParam,
				username: userName,
				password: userPassword
			}, function (response) {
				$('#userJsonOutput').text(response);
			});
		}
	});
	
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
