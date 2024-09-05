
function showChapterBeatsModal() {
	
	let structureHtml = '<h2>' + bookData.title + '</h2>';
	structureHtml += '<p><i>' + __e('Blurb') + '</i>: ' + bookData.blurb + '</p>';
	structureHtml += '<p><i>' + __e('Back Cover Text') + '</i>: ' + bookData.back_cover_text + '</p>';
	
	
	structureHtml += '<h4>' + currentChapter.name + '</h4>';
	structureHtml += '<p>' + currentChapter.short_description + '</p>';
	structureHtml += '<ul>';
	structureHtml += '<li><i>' + __e('Events') + '</i>: ' + currentChapter.events + '</li>';
	structureHtml += '<li><i>' + __e('People') + '</i>: ' + currentChapter.people + '</li>';
	structureHtml += '<li><i>' + __e('Places') + '</i>: ' + currentChapter.places + '</li>';
	structureHtml += '<li><i>' + __e('From Previous Chapter') + '</i>:' + currentChapter.from_previous_chapter + '</li>';
	structureHtml += '<li><i>' + __e('To Next Chapter') + '</i>: ' + currentChapter.to_next_chapter + '</li>';
	structureHtml += '</ul>';
	if (currentChapter.beats && currentChapter.beats.length > 0) {
		structureHtml += '<h5>' + __e('Beats:') + '</h5>';
		structureHtml += '<br>';
		currentChapter.beats.forEach(function (beat) {
			if (beat.beat_text) {
				structureHtml += '' + beat.beat_text.replace(/\n/g, "<br>") + '<br><br><br>';
			} else {
				structureHtml += '' + beat.description + '<br><br><br>';
			}
		});
		structureHtml += '</ul>';
	}
	
	$('#chapterBeatsContent').html(structureHtml);
	$('#chapterBeatsModal').modal({backdrop: 'static', keyboard: true}).modal('show');
}

function writeAllBeats() {
	const modal = $('#writeAllBeatsModal');
	const progressBar = modal.find('.progress-bar');
	const log = $('#writeAllBeatsLog');
	$("#beatSpinner").removeClass('d-none');
	
	modal.modal({backdrop: 'static', keyboard: true}).modal('show');
	
	$('#writeAllBeatsLog').empty();
	progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
	
	$('#writeAllBeatsLog').append('<br>' + __e('This process will write the texts and the summaries for all beats in this chapter. The summaries are used to create the next beat. Please wait... '));
	$('#writeAllBeatsLog').append('<br><br>' + __e('If the progress bar is stuck for a long time, please refresh the page and try again.') + '<br><br>');
	
	const beats = $('.beat-outer-container');
	const totalBeats = beats.length;
	let processedBeats = 0;
	
	function processNextBeat() {
		if (processedBeats < totalBeats) {
			
			const beatContainer = beats[processedBeats];
			const beatIndex = $(beatContainer).data('beat-index');
			const beatDescription = $(`#beatDescription_${beatIndex}`).val();
			const beatText = $(`#beatText_${beatIndex}`).val();
			if (beatText.trim() !== '') {
				$('#writeAllBeatsLog').append('<br>' + __e('Beat ${beatIndex} already has text. Skipping...', {beatIndex: beatIndex + 1}) + '<br>');
				$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
				processedBeats++;
				const progress = Math.round((processedBeats / totalBeats) * 100);
				progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
				processNextBeat();
			} else {
				
				$('#writeAllBeatsLog').append('<br><br><em>' + __e('Writing beat ${beatIndex}', {beatIndex: (beatIndex + 1)}) + '</em>');
				$('#writeAllBeatsLog').append('<br><em>' + __e('Beat Description:') + '</em> ' + beatDescription);
				$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
				
				writeBeatText(beatDescription, beatIndex)
					.then(() => {
						const beatText = $(`#beatText_${beatIndex}`).val();
						return writeBeatTextSummary(beatText, beatDescription, beatIndex);
					})
					.then(() => {
						//add the newly written summary to the log
						$('#writeAllBeatsLog').append('<br><em>' + __e('Summary for beat ${beatIndex}:', {beatIndex: (beatIndex + 1)}) + '</em>');
						$('#writeAllBeatsLog').append('<br>' + $(`#beatTextSummary_${beatIndex}`).val());
						
						processedBeats++;
						const progress = Math.round((processedBeats / totalBeats) * 100);
						progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
						processNextBeat();
					})
					.catch(error => {
						$('#writeAllBeatsLog').append('<br>' + __e('Error processing beat ${beatIndex}: ${error}', {
							beatIndex: (beatIndex + 1),
							error: error
						}));
						$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
						
						processedBeats++;
						const progress = Math.round((processedBeats / totalBeats) * 100);
						progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
						processNextBeat();
					});
			}
		} else {
			$("#beatSpinner").addClass('d-none');
			$('#writeAllBeatsLog').append('<br>' + __e('All beats processed!'));
			$('#writeAllBeatsLog').append('<br><br><span style="font-weight: bold;">' + __e('After reviewing the beats, click the "Save Beats" button to save the changes.') + '</span><br><br>');
			
			$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
			$('#saveBeatsBtn').removeClass('d-none');
			setTimeout(function () {
				$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
			}, 200);
		}
	}
	
	processNextBeat();
}

function loadBeats() {
	const beatsList = $('#beatsList');
	beatsList.empty(); // Clear existing beats
	
	let beat_description_label = __e('Beat Description');
	let beat_text_label = __e('Beat Text');
	let beat_summary_label = __e('Beat Text Summary');
	let save_beat_label = __e('Save Beat');
	let write_beat_text_label = __e('Write Beat Text');
	let write_beat_text_summary_label = __e('Write Summary');
	let beat_label = __e('Beat');
	
	currentChapter.beats.forEach((beat, index) => {
		const beatHtml = `
                <div class="mb-3 beat-outer-container" data-beat-index="${index}">
                  <h5>${beat_label} ${index + 1}</h5>
                  <div id="beatDescriptionContainer_${index}">
										<label for="beatDescription_${index}" class="form-label">${beat_description_label}</label>
										<textarea id="beatDescription_${index}" class="form-control beat-description-textarea" rows="3">${beat.description ?? ''}</textarea>
									</div>
									<div id="beatTextArea_${index}" class="mt-3">
										<label for="beatText_${index}" class="form-label">${beat_text_label}</label>
										<textarea id="beatText_${index}" class="form-control beat-text-textarea" rows="10">${beat.beat_text ?? ''}</textarea>
									</div>
									<div id="beatTextSummaryArea_${index}" class="mt-3">
										<label for="beatTextSummary_${index}" class="form-label">${beat_summary_label}</label>
										<textarea id="beatTextSummary_${index}" class="form-control beat-summary-textarea" rows="3">${beat.beat_text_summary ?? ''}</textarea>
									</div>
									
									<div class="" data-index="${index}">
										<button class="saveBeatBtn btn btn-success mt-3 me-2">${save_beat_label}</button>
										<button class="writeBeatTextBtn btn btn-primary mt-3 me-2">${write_beat_text_label}</button>
										<button class="writeBeatTextSummaryBtn btn btn-primary mt-3 me-2">${write_beat_text_summary_label}</button>
										<div class="me-auto d-inline-block" id="beatDetailModalResult_${index}"></div>
									</div>
                </div>
            `;
		beatsList.append(beatHtml);
	});
	
	$('.saveBeatBtn').off('click').on('click', function () {
		let beatIndex = Number($(this).parent().attr('data-index'));
		let beatText = $('#beatText_' + beatIndex).val();
		let beatDescription = $('#beatDescription_' + beatIndex).val();
		let beatTextSummary = $('#beatTextSummary_' + beatIndex).val();
		saveBeat(beatText, beatTextSummary, beatDescription, beatIndex);
	});
	
	$('.writeBeatTextBtn').off('click').on('click', function () {
		let beatIndex = Number($(this).parent().attr('data-index'));
		let beatDescription = $('#beatDescription_' + beatIndex).val();
		writeBeatText(beatDescription, beatIndex);
	});
	
	$('.writeBeatTextSummaryBtn').off('click').on('click', function () {
		let beatIndex = Number($(this).parent().attr('data-index'));
		let beatText = $('#beatText_' + beatIndex).val();
		let beatDescription = $('#beatDescription_' + beatIndex).val();
		writeBeatTextSummary(beatText, beatDescription, beatIndex);
	});
}

function recreateBeats() {
	let spinner = $('#beat-spinner');
	spinner.removeClass('d-none');
	$("#recreateBeats").prop('disabled', true);
	
	let previousChapterBeats = '';
	if (previousChapter !== null) {
		for (let i = 0; i < previousChapter.beats.length; i++) {
			previousChapterBeats += previousChapter.beats[i]?.beat_text_summary || previousChapter.beats[i]?.description || '';
			previousChapterBeats += "\n";
		}
	}
	
	// Now proceed with creating beats
	$.ajax({
		url: 'action-beats.php',
		method: 'POST',
		data: {
			action: 'write_beats',
			llm: savedLlm,
			book: bookSlug,
			chapterFilename: chapterFilename,
			chapterName: currentChapter.name,
			chapterText: currentChapter.short_description,
			chapterEvents: currentChapter.events,
			chapterPeople: currentChapter.people,
			chapterPlaces: currentChapter.places,
			chapterFromPreviousChapter: previousChapterBeats || currentChapter.from_previous_chapter,
			chapterToNextChapter: nextChapter.description || currentChapter.to_next_chapter
		},
		dataType: 'json',
		success: function (response) {
			spinner.addClass('d-none');
			if (response.success) {
				const beatsList = $('#beatsList');
				beatsList.empty();
				response.beats.forEach((beat, index) => {
					
					let beat_description_label = __e('Beat Description');
					let beat_text_label = __e('Beat Text');
					let beat_summary_label = __e('Beat Text Summary');
					let save_beat_label = __e('Save Beat');
					let write_beat_text_label = __e('Write Beat Text');
					let write_beat_text_summary_label = __e('Write Summary');
					let beat_label = __e('Beat');
					
					const beatHtml = `
                <div class="mb-3 beat-outer-container" data-beat-index="${index}">
                  <h5>${beat_label} ${index + 1}</h5>
                  <div id="beatDescriptionContainer_${index}">
										<label for="beatDescription_${index}" class="form-label">${beat_description_label}</label>
										<textarea id="beatDescription_${index}" class="form-control beat-description-textarea" rows="3">${beat.description ?? ''}</textarea>
									</div>
									<div id="beatTextArea_${index}" class="mt-3">
										<label for="beatText_${index}" class="form-label">${beat_text_label}</label>
										<textarea id="beatText_${index}" class="form-control beat-text-textarea" rows="10"></textarea>
									</div>
									<div id="beatTextSummaryArea_${index}" class="mt-3">
										<label for="beatTextSummary_${index}" class="form-label">${beat_summary_label}</label>
										<textarea id="beatTextSummary_${index}" class="form-control beat-summary-textarea" rows="3"></textarea>
									</div>
									
									<div class="" data-index="${index}">
										<button class="saveBeatBtn btn btn-success mt-3 me-2">${save_beat_label}</button>
										<button class="writeBeatTextBtn btn btn-primary mt-3 me-2">${write_beat_text_label}</button>
										<button class="writeBeatTextSummaryBtn btn btn-primary mt-3 me-2">${write_beat_text_summary_label}</button>
										<div class="me-auto d-inline-block" id="beatDetailModalResult_${index}"></div>
									</div>
                </div>
            `;
					beatsList.append(beatHtml);
				});
				
				$('.saveBeatBtn').off('click').on('click', function () {
					let beatIndex = Number($(this).parent().attr('data-index'));
					let beatText = $('#beatText_' + beatIndex).val();
					let beatDescription = $('#beatDescription_' + beatIndex).val();
					let beatTextSummary = $('#beatTextSummary_' + beatIndex).val();
					saveBeat(beatText, beatTextSummary, beatDescription, beatIndex);
				});
				
				$('.writeBeatTextBtn').off('click').on('click', function () {
					let beatIndex = Number($(this).parent().attr('data-index'));
					let beatDescription = $('#beatDescription_' + beatIndex).val();
					writeBeatText(beatDescription, beatIndex);
				});
				
				$('.writeBeatTextSummaryBtn').off('click').on('click', function () {
					let beatIndex = Number($(this).parent().attr('data-index'));
					let beatText = $('#beatText_' + beatIndex).val();
					let beatDescription = $('#beatDescription_' + beatIndex).val();
					writeBeatTextSummary(beatText, beatDescription, beatIndex);
				});
				
				$('#saveBeatsBtn').removeClass('d-none');
				$("#recreateBeats").prop('disabled', false);
				
			} else {
				alert(__e('Failed to create beats: ') + response.message);
				$("#recreateBeats").prop('disabled', false);
			}
		},
		error: function () {
			spinner.addClass('d-none');
			alert(__e('An error occurred while creating beats.'));
		}
	});
	
}

function writeBeatText(beatDescription, beatIndex) {
	return new Promise((resolve, reject) => {
		$("#writeBeatTextBtn_" + beatIndex).prop('disabled', true);
		$("#beatDetailModalResult_" + beatIndex).html(__e('Writing beat text...'));
		
		let prevBeatsSummaries = '';
		let lastBeatBeforeCurrent = '';
		
		if (beatIndex > 0) {
			for (let i = 0; i < beatIndex; i++) {
				if (i === beatIndex - 1) {
					lastBeatBeforeCurrent = $("#beatText_" + i).val() || '';
				} else {
					let currentBeatSummary = $("#beatTextSummary_" + i).val() || $("#beatDescription" + i).val() || '';
					prevBeatsSummaries += currentBeatSummary + '\n';
				}
			}
		} else {
			// Loop through previous chapter beats
			if (previousChapter !== null) {
				for (let i = 0; i < previousChapter.beats.length; i++) {
					if (i === previousChapter.beats.length - 1) {
						lastBeatBeforeCurrent = previousChapter.beats[i]?.beat_text || '';
					} else {
						let currentBeatSummary = previousChapter.beats[i]?.beat_text_summary || previousChapter.beats[i]?.description || '';
						prevBeatsSummaries += currentBeatSummary + '\n';
					}
				}
			}
		}
		
		let nextBeat = '';
		console.log(beatIndex, (beatIndex + 1), currentChapter.beats.length - 1);
		if (beatIndex < currentChapter.beats.length - 1) {
			nextBeat = $("#beatDescription_" + (beatIndex + 1)).val() || '';
		} else {
			if (nextChapter !== null) {
				nextBeat = nextChapter.beats[0]?.description || '';
			}
		}
		
		$.post('action-beats.php', {
			action: 'write_beat_text',
			llm: savedLlm,
			chapterFilename: chapterFilename,
			beatIndex: beatIndex,
			
			book: bookSlug,
			book_title: bookData.title,
			book_blurb: bookData.blurb,
			back_cover_text: bookData.back_cover_text,
			language: bookData.language,
			act: currentChapter.row,
			chapter_title: currentChapter.name,
			chapter_description: currentChapter.short_description,
			chapter_events: currentChapter.events,
			chapter_people: currentChapter.people,
			chapter_places: currentChapter.places,
			previous_beat_summaries: prevBeatsSummaries,
			last_beat: lastBeatBeforeCurrent,
			current_beat: beatDescription,
			next_beat: nextBeat,
			
		}, function (response) {
			if (response.success) {
				$('#beatText_' + beatIndex).val(response.prompt);
				$('#beatDetailModalResult_' + beatIndex).html(__e('Beat text generated successfully!'));
				$('#writeBeatTextBtn_' + beatIndex).prop('disabled', false);
				resolve();
			} else {
				$('#beatDetailModalResult_' + beatIndex).html(__e('Failed to write beat text: ') + response.message);
				reject(__e('Failed to write beat text: ') + response.message);
				
			}
		}, 'json');
	});
}

function writeBeatTextSummary(beatText, beatDescription, beatIndex) {
	return new Promise((resolve, reject) => {
		$('#writeBeatTextSummaryBtn_' + beatIndex).prop('disabled', true);
		$('#beatDetailModalResult_' + beatIndex).html(__e('Writing beat text summary...'));
		
		$.post('action-beats.php', {
			action: 'write_beat_text_summary',
			llm: savedLlm,
			
			book: bookSlug,
			chapterFilename: chapterFilename,
			
			book_title: bookData.title,
			book_blurb: bookData.blurb,
			back_cover_text: bookData.back_cover_text,
			language: bookData.language,
			act: currentChapter.row,
			chapter_title: currentChapter.name,
			chapter_description: currentChapter.short_description,
			chapter_events: currentChapter.events,
			chapter_people: currentChapter.people,
			chapter_places: currentChapter.places,
			
			currentBeatDescription: beatDescription,
			currentBeatText: beatText
		}, function (response) {
			if (response.success) {
				$('#beatTextSummary_' + beatIndex).val(response.prompt);
				$('#beatDetailModalResult_' + beatIndex).html(__e('Beat text summary generated successfully!'));
				$('#writeBeatTextSummaryBtn_' + beatIndex).prop('disabled', false);
				resolve();
			} else {
				$('#beatDetailModalResult_' + beatIndex).html(__e('Failed to write summary: ') + response.message);
				reject(__e('Failed to write summary: ') + response.message);
			}
		}, 'json');
	});
}

function saveBeat(beatText, beatTextSummary, beatDescription, beatIndex) {
	$.post('action-beats.php', {
		action: 'save_beat_text',
		llm: savedLlm,
		book: bookSlug,
		chapterFilename: chapterFilename,
		beatIndex: beatIndex,
		beatDescription: beatDescription,
		beatText: beatText,
		beatTextSummary: beatTextSummary
	}, function (response) {
		if (response.success) {
			$("#beatDetailModalResult_" + beatIndex).html(__e('Beat saved successfully!'));
		} else {
			$("#beatDetailModalResult_" + beatIndex).html(__e('Failed to save beat: ') + response.message);
		}
	}, 'json');
}

$(document).ready(function () {
	
	loadBeats();
	
	
	$('#showchapterBeatsBtn').on('click', function (e) {
		e.preventDefault();
		showChapterBeatsModal();
	});
	
	$('#writeAllBeatsBtn').on('click', function (e) {
		e.preventDefault();
		writeAllBeats();
	});
	
	
	$('#saveBeatsBtn').on('click', function (e) {
		e.preventDefault();
		
		let beats = [];
		
		$('#beatsList').find('.beat-outer-container').each(function (index, element) {
			let beatDescription = $(element).find('.beat-description-textarea').val();
			let beatText = $(element).find('.beat-text-textarea').val();
			let beatTextSummary = $(element).find('.beat-summary-textarea').val();
			beats.push({description: beatDescription, beat_text: beatText, beat_text_summary: beatTextSummary});
		});
		
		$.post('action-beats.php', {
			action: 'save_beats',
			llm: savedLlm,
			book: bookSlug,
			chapterFilename: chapterFilename,
			beats: JSON.stringify(beats)
		}, function (response) {
			if (response.success) {
				alert(__e('Beats saved successfully!'));
				//reload the page
				location.reload();
			} else {
				alert(__e('Failed to save beats: ') + response.message);
			}
		}, 'json');
	});
	
	$("#recreateBeats").on('click', function (e) {
		e.preventDefault();
		//show confirmation dialog
		if (confirm(__e('Are you sure you want to recreate the beats? This will overwrite any existing beats.'))) {
			recreateBeats();
		}
	});
	
});
