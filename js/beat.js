var beatsData = null;

function generateAllBeats() {
	const modal = $('#generateAllBeatsModal');
	const progressBar = modal.find('.progress-bar');
	const log = $('#generateAllBeatsLog');
	modal.modal({backdrop: 'static', keyboard: true}).modal('show');
	log.empty();
	progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
	
	$.post('action-book.php', {action: 'get_all_chapters', book: bookParam}, function (response) {
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
						url: 'action-beats.php',
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
									url: 'action-beats.php',
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

function loadBeats(chapterFilename) {
	$.ajax({
			url: `${chaptersDirName}/${chapterFilename}`,
			method: 'GET',
			dataType: 'json'
		})
		.done(function (chapter) {
			const beatsList = $('#beatsList');
			beatsList.empty(); // Clear existing beats
			
			if (chapter.beats) {
				beatsData = chapter.beats;
				
				chapter.beats.forEach((beat, index) => {
					const beatHtml = `
                <div class="mb-3">
                    <h5>Beat ${index + 1}</h5>
                    <p>${beat.description}</p>
                    <button class="btn btn-primary btn-sm view-beat-btn" data-beat-index="${index}">
                        View Details ${beat.beat_text ? '<span class="ms-2 text-info">✓</span>' : ''}
                    </button>
                </div>
            `;
					beatsList.append(beatHtml);
				});
				
				$('.view-beat-btn').off('click').on('click', function () {
					const beatIndex = $(this).data('beat-index');
					showBeatDetailModal(beatIndex, chapterFilename);
				});
			}
		})
		.fail(function (error) {
			console.error('Error loading beats:', error);
		});
}

function showBeatModal(event, chapterFilename) {
	if (event) {
		event.stopPropagation();
	}
	$('#chapterFilename').val(chapterFilename);
	loadBeats(chapterFilename);
	$('#beatModal').modal({backdrop: 'static', keyboard: true}).modal('show');
}

function recreateBeats(chapterFilename) {
	let spinner = $('#beat-spinner');
	spinner.removeClass('d-none');
	$("#recreateBeats").prop('disabled', true);
	
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
			
			// First, fetch all chapters to find the previous chapter
			$.post('action-book.php', {action: 'get_all_chapters', book: bookParam}, function (response) {
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
					url: 'action-beats.php',
					method: 'POST',
					data: {
						action: 'write_beats',
						book: bookParam,
						chapterFilename: chapterFilename,
						chapterName: chapter.name,
						chapterText: chapter.short_description,
						chapterEvents: chapter_events,
						chapterPeople: chapter_people,
						chapterPlaces: chapter_places,
						chapterFromPrevChapter: prevChapterBeats || chapter.from_prev_chapter,
						chapterToNextChapter: chapter.to_next_chapter
					},
					dataType: 'json',
					success: function (response) {
						spinner.addClass('d-none');
						if (response.success) {
							const beatsList = $('#beatsList');
							beatsList.empty();
							beatsData = [];
							response.beats.forEach((beat, index) => {
								beatsData.push({description : beat.description, beat_text : ''});
								
									const beatHtml = `
                <div class="mb-3">
                    <h5>Beat ${index + 1}</h5>
                    <p>${beat.description}</p>
                    <button class="btn btn-primary btn-sm view-beat-btn" data-beat-index="${index}">
                        View Details ${beat.beat_text ? '<span class="ms-2 text-info">✓</span>' : ''}
                    </button>
                </div>
                  `;
									beatsList.append(beatHtml);
							});
							$('.view-beat-btn').off('click').on('click', function () {
								const beatIndex = $(this).data('beat-index');
								showBeatDetailModal(beatIndex, chapterFilename);
							});
							$('#saveBeatsBtn').removeClass('d-none');
							$("#recreateBeats").prop('disabled', false);
							
						} else {
							alert('Failed to create beats: ' + response.message);
							$("#recreateBeats").prop('disabled', false);
						}
					},
					error: function () {
						spinner.addClass('d-none');
						alert('An error occurred while creating beats.');
					}
				});
			});
		})
		.catch(error => console.error('Error loading chapter:', error));
}

function showBeatDetailModal(beatIndex, chapterFilename) {
	console.log(beatIndex, chapterFilename, beatsData[beatIndex]);
	
	$('#beatDescription').val(beatsData[beatIndex].description);
	$('#beatText').val(beatsData[beatIndex].beat_text ?? '');
	$('#beatDetailModalLabel').text(`Beat ${beatIndex + 1}`);
	
	$('#saveBeatBtn').off('click').on('click', function () {
		saveBeat(beatIndex, chapterFilename);
	});
	
	$('#writeBeatTextBtn').off('click').on('click', function () {
		writeBeatText(beatIndex, chapterFilename);
	});
	
	$('#beatDetailModal').modal({backdrop: 'static', keyboard: true}).modal('show');
}

function writeBeatText(beatIndex, chapterFilename) {
	$.post('action-beats.php', {
		action: 'write_beat_text',
		book: bookParam,
		chapterFilename: chapterFilename,
		beatIndex: beatIndex,
		currentBeatDescription: $('#beatDescription').val()
	}, function (response) {
		if (response.success) {
			$('#beatText').val(response.prompt);
		} else {
			alert('Failed to get beat prompt: ' + response.message);
		}
	}, 'json');
}

function saveBeat(beatIndex, chapterFilename) {
	const beatDescription = $('#beatDescription').val();
	const beatText = $('#beatText').val();
	$.post('action-beats.php', {
		action: 'save_beat_text',
		book: bookParam,
		chapterFilename: chapterFilename,
		beatIndex: beatIndex,
		beatDescription: beatDescription,
		beatText: beatText
	}, function (response) {
		if (response.success) {
			loadBeats(chapterFilename);
			alert('Beat saved successfully!');
		} else {
			alert('Failed to save beat: ' + response.message);
		}
	}, 'json');
}

$(document).ready(function () {
	
	$('#saveBeatsBtn').on('click', function (e) {
		e.preventDefault();
		
		let beats = [];
		$('#beatsList').find('h5').each(function (index, element) {
			beats.push({description: $(element).next().text(), beat_text: ''});
		});
		
		$.post('action-beats.php', {
			action: 'save_beats',
			book: bookParam,
			chapterFilename: $('#chapterFilename').val(),
			beats: JSON.stringify(beats)
		}, function (response) {
			if (response.success) {
				alert('Beats saved successfully!');
			} else {
				alert('Failed to save beats: ' + response.message);
			}
		}, 'json');
	});
	
	$("#recreateBeats").on('click', function (e) {
		e.preventDefault();
		recreateBeats($('#chapterFilename').val());
	});
	
	$('#generateAllBeatsBtn').on('click', function (e) {
		e.preventDefault();
		generateAllBeats();
	});
	
	$('#beatDetailModal').on('shown.bs.modal', function () {
		$('#beatText').focus();
	});
	
	
});
