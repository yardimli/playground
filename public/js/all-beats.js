function recreateBeats(selectedChapter, beatsPerChapter = 3) {
	$('#fullScreenOverlay').removeClass('d-none');
	$("#recreateBeats").prop('disabled', true);
	
	// Clear existing beats
	$('#beatsList').empty();
	
	// Now proceed with creating beats
	$.ajax({
		url: `/book/write-beats/${bookSlug}/${selectedChapter}`,
		method: 'POST',
		data: {
			llm: savedLlm,
			beats_per_chapter: beatsPerChapter,
			save_results: false,
		},
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		dataType: 'json',
		success: function (response) {
			$('#fullScreenOverlay').addClass('d-none');
			if (response.success) {
				response.beats.forEach((beat, beatIndex) => {
					addEmptyBeat(selectedChapterIndex, beatIndex, beat.description);
				});
				
				$("#alertModalContent").html(__e('All chapter Beat Descriptions generated successfully. Don\'t forget to save!'));
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				$("#saveBeatsBtn").show();
				$('#recreateBeats').prop('disabled', false);
				
			} else {
				$('#fullScreenOverlay').addClass('d-none');
				$("#alertModalContent").html(__e('Failed to create beats: ') + response.message);
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				
				$("#recreateBeats").prop('disabled', false);
			}
		},
		error: function () {
			$('#fullScreenOverlay').addClass('d-none');
			$("#alertModalContent").html(__e('An error occurred while creating beats.'));
			$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
		}
	});
}

function addEmptyBeat(chapterIndex, beatIndex, description) {
	const beatHtml = `
        <div class="mb-3 beat-outer-container" data-chapter-index="${chapterIndex}"
             data-chapter-filename="${selectedChapter}"
             data-beat-index="${beatIndex}">
            <h6>Beat ${beatIndex + 1}</h6>
            <div id="beatDescriptionContainer_${chapterIndex}_${beatIndex}">
                <label for="beatDescription_${chapterIndex}_${beatIndex}"
                       class="form-label">${__e('Beat Description')}</label>
                <textarea id="beatDescription_${chapterIndex}_${beatIndex}"
                          class="form-control beat-description-textarea"
                          rows="3">${description}</textarea>
            </div>
            <div id="beatTextArea_${chapterIndex}_${beatIndex}" class="mt-3">
                <label for="beatText_${chapterIndex}_${beatIndex}"
                       class="form-label">${__e('Beat Text')}</label>
                <textarea id="beatText_${chapterIndex}_${beatIndex}" class="form-control beat-text-textarea"
                          rows="10"></textarea>
            </div>
            <div id="beatSummaryArea_${chapterIndex}_${beatIndex}" class="mt-3">
                <label for="beatSummary_${chapterIndex}_${beatIndex}"
                       class="form-label">${__e('Beat Summary')}</label>
                <textarea id="beatSummary_${chapterIndex}_${beatIndex}" class="form-control beat-summary-textarea"
                          rows="3"></textarea>
            </div>
            <div id="beatLoreBookArea_${chapterIndex}_${beatIndex}" class="mt-3">
                <label for="beatLoreBook_${chapterIndex}_${beatIndex}"
                       class="form-label">${__e('Beat Lore Book')}</label>
                <textarea id="beatLoreBook_${chapterIndex}_${beatIndex}" class="form-control beat-lore-book-textarea"
                          rows="6"></textarea>
            </div>
            <div class="" data-chapter-index="${chapterIndex}"
                 data-chapter-filename="${selectedChapter}" data-beat-index="${beatIndex}">
                <button id="writeBeatTextBtn_${chapterIndex}_${beatIndex}"
                    class="writeBeatTextBtn btn btn-primary mt-3 me-2">${__e('Write Beat Text')}</button>
                <button id="writeBeatSummaryBtn_${chapterIndex}_${beatIndex}"
                    class="writeBeatSummaryBtn btn btn-primary mt-3 me-2">${__e('Write Summary')}</button>
                <button id="updateBeatLoreBookBtn_${chapterIndex}_${beatIndex}"
                    class="updateBeatLoreBookBtn btn btn-primary mt-3 me-2">${__e('Update Beat Lore Book')}</button>
                <button class="saveBeatBtn btn btn-success mt-3 me-2">${__e('Save Beat')}</button>
                <div class="me-auto d-inline-block" id="beatDetailModalResult_${chapterIndex}_${beatIndex}"></div>
            </div>
        </div>
    `;
	$('#beatsList').append(beatHtml);
}


//------------------------------------------------------------
function writeBeatText(beatDescription, beatIndex, chapterIndex, chapterFilename, showOverlay = true, save_results = false) {
	return new Promise((resolve, reject) => {
		if (showOverlay) {
			$('#fullScreenOverlay').removeClass('d-none');
		}
		
		$("#writeBeatTextBtn_" + chapterIndex + '_' + beatIndex).prop('disabled', true);
		$("#beatDetailModalResult_" + chapterIndex + '_' + beatIndex).html(__e('Writing beat text...'));
		
		$.ajax({
			url: `/book/write-beat-text/${bookSlug}/${chapterFilename}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				beatIndex: beatIndex,
				current_beat: beatDescription,
				save_results: save_results,
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				$('#fullScreenOverlay').addClass('d-none');
				if (response.success) {
					$('#beatText_' + chapterIndex + '_' + beatIndex).val(response.prompt);
					$('#beatDetailModalResult_' + chapterIndex + '_' + beatIndex).html(__e('Beat text generated successfully!'));
					$('#writeBeatTextBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', false);
					resolve(response.prompt);
				} else {
					$('#beatDetailModalResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to write beat text: ') + response.message);
					reject(__e('Failed to write beat text: ') + response.message);
				}
			},
			error: function () {
				$('#fullScreenOverlay').addClass('d-none');
				$('#beatDetailModalResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to write beat text.'));
				reject(__e('Failed to write beat text.'));
			}
		});
	});
}

//------------------------------------------------------------
function writeBeatSummary(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, showOverlay = true, save_results = false) {
	return new Promise((resolve, reject) => {
		if (showOverlay) {
			$('#fullScreenOverlay').removeClass('d-none');
		}
		$('#writeBeatSummaryBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', true);
		$('#beatDetailModalResult_' + chapterIndex + '_' + beatIndex).html(__e('Writing beat summary...'));
		
		$.ajax({
			url: `/book/write-beat-summary/${bookSlug}/${chapterFilename}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				beatIndex: beatIndex,
				currentBeatDescription: beatDescription,
				currentBeatText: beatText,
				save_results: save_results,
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				$('#fullScreenOverlay').addClass('d-none');
				if (response.success) {
					$('#beatSummary_' + chapterIndex + '_' + beatIndex).val(response.prompt);
					$('#beatDetailModalResult_' + chapterIndex + '_' + beatIndex).html(__e('Beat summary generated successfully!'));
					$('#writeBeatSummaryBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', false);
					resolve(response.prompt);
				} else {
					$('#beatDetailModalResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to write summary: ') + response.message);
					reject(__e('Failed to write summary: ') + response.message);
				}
			},
			error: function () {
				$('#fullScreenOverlay').addClass('d-none');
				$('#beatDetailModalResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to write beat summary.'));
				reject(__e('Failed to write beat summary.'));
			}
		});
	});
}

function updateBeatLoreBook(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, showOverlay = true, save_results = false) {
	return new Promise((resolve, reject) => {
		if (showOverlay) {
			$('#fullScreenOverlay').removeClass('d-none');
		}
		$('#updateBeatLoreBookBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', true);
		$('#beatDetailModalResult_' + chapterIndex + '_' + beatIndex).html(__e('Updating Lore Book...'));
		
		$.ajax({
			url: `/book/update-beat-lore-book/${bookSlug}/${chapterFilename}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				beatIndex: beatIndex,
				currentBeatDescription: beatDescription,
				currentBeatText: beatText,
				save_results: save_results,
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				$('#fullScreenOverlay').addClass('d-none');
				if (response.success) {
					$('#beatLoreBook_' + chapterIndex + '_' + beatIndex).val(response.prompt);
					$('#beatDetailModalResult_' + chapterIndex + '_' + beatIndex).html(__e('Beat lore book updated successfully!'));
					$('#updateBeatLoreBookBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', false);
					resolve(response.prompt);
				} else {
					$('#beatDetailModalResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to update lore book: ') + response.message);
					reject(__e('Failed to update lore book: ') + response.message);
				}
			},
			error: function () {
				$('#fullScreenOverlay').addClass('d-none');
				$('#beatDetailModalResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to update lore book.'));
				reject(__e('Failed to update lore book.'));
			}
		});
	});
}

//------------------------------------------------------------
function saveBeat(beatText, beatSummary, beatLoreBook, beatDescription, beatIndex, chapterIndex, chapterFilename) {
	$.ajax({
		url: `/book/save-single-beat/${bookSlug}/${chapterFilename}`,
		method: 'POST',
		data: {
			llm: savedLlm,
			beatIndex: beatIndex,
			beatDescription: beatDescription,
			beatText: beatText,
			beatSummary: beatSummary,
			beatLoreBook: beatLoreBook,
		},
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		dataType: 'json',
		success: function (response) {
			if (response.success) {
				$("#beatDetailModalResult_" + chapterIndex + "_" + beatIndex).html(__e('Beat saved successfully!'));
			} else {
				$("#beatDetailModalResult_" + chapterIndex + "_" + beatIndex).html(__e('Failed to save beat: ') + response.message);
			}
		}
	});
}

//------------------------------------------------------------
function writeAllBeats() {
	let modal = $('#writeAllBeatsModal');
	let progressBar = modal.find('.progress-bar');
	let log = $('#writeAllBeatsLog');
	$("#beatSpinner").removeClass('d-none');
	
	modal.modal({backdrop: 'static', keyboard: true}).modal('show');
	
	$('#writeAllBeatsLog').empty();
	progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
	
	$('#writeAllBeatsLog').append('<br>' + __e('This process will write the texts and the summaries for all beats in this chapter. The summaries are used to create the next beat. Please wait... '));
	$('#writeAllBeatsLog').append('<br><br>' + __e('If the progress bar is stuck for a long time, please refresh the page and try again.') + '<br><br>');
	
	let beats = $('.beat-outer-container');
	let totalBeats = beats.length;
	let processedBeats = 0;
	
	function processNextBeat() {
		if (processedBeats < totalBeats) {
			
			let beatContainer = beats[processedBeats];
			let beatIndex = Number($(beatContainer).attr('data-beat-index'));
			let chapterIndex = Number($(beatContainer).attr('data-chapter-index'));
			let chapterFilename = $(beatContainer).attr('data-chapter-filename');
			let beatDescription = $(`#beatDescription_${chapterIndex}_${beatIndex}`).val();
			let beatText = $(`#beatText_${chapterIndex}_${beatIndex}`).val();
			if (beatText.trim() !== '') {
				$('#writeAllBeatsLog').append('<br>' + __e('Chapter ${chapterIndex}, Beat ${beatIndex} already has text. Skipping...', {chapterIndex : chapterIndex, beatIndex: beatIndex + 1}) + '<br>');
				$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
				processedBeats++;
				let progress = Math.round((processedBeats / totalBeats) * 100);
				progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
				processNextBeat();
			} else {
				
				$('#writeAllBeatsLog').append('<br><br><em>' + __e('Writing chapter ${chapterIndex}, beat ${beatIndex}', {chapterIndex : chapterIndex, beatIndex: (beatIndex + 1)}) + '</em>');
				
				$('#writeAllBeatsLog').append('<br><em>' + __e('Beat Description:') + '</em> ' + beatDescription);
				$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
				
				writeBeatText(beatDescription, beatIndex, chapterIndex, chapterFilename, false, true)
					.then(() => {
						let beatText = $(`#beatText_${beatIndex}`).val();
						return writeBeatSummary(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, false, true);
					})
					.then(() => {
						//add the newly written summary to the log
						$('#writeAllBeatsLog').append('<br><em>' + __e('Summary for chapter ${chapterIndex}, beat ${beatIndex}:', {chapterIndex : chapterIndex, beatIndex: (beatIndex + 1)}) + '</em>');
						$('#writeAllBeatsLog').append('<br>' + $(`#beatSummary_${chapterIndex}_${beatIndex}`).val());
						
						processedBeats++;
						let progress = Math.round((processedBeats / totalBeats) * 100);
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
						let progress = Math.round((processedBeats / totalBeats) * 100);
						progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
						processNextBeat();
					});
			}
		} else {
			$("#beatSpinner").addClass('d-none');
			$('#writeAllBeatsLog').append('<br><br><span style="font-weight: bold;">' + __e('All beats processed!') + '</span><br><br>');
			//$('#writeAllBeatsLog').append('<br><br><span style="font-weight: bold;">' + __e('After reviewing the beats, click the "Save Beats" button to save the changes.') + '</span><br><br>');
			
			$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
			setTimeout(function () {
				$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
			}, 200);
		}
	}
	
	processNextBeat();
}

$(document).ready(function () {
	
	if (selectedChapter!=='') {
		$('#writeAllBeatsBtn').hide();
		$("#saveBeatsBtn").hide();
		$('#recreateBeats').show();
		$('#beatsPerChapter').show();
	} else
	{
		$('#writeAllBeatsBtn').show();
		$("#saveBeatsBtn").hide();
		$('#recreateBeats').hide();
		$('#beatsPerChapter').hide();
	}
	
	$('#writeAllBeatsBtn').on('click', function () {
		writeAllBeats();
	});
	
	$('.closeAndRefreshButton').on('click', function () {
		location.reload();
	});
	
	$('.saveBeatBtn').off('click').on('click', function () {
		let beatIndex = Number($(this).parent().attr('data-beat-index'));
		let chapterIndex = Number($(this).parent().attr('data-chapter-index'));
		let chapterFilename = $(this).parent().attr('data-chapter-filename');
		
		let beatText = $('#beatText_' + chapterIndex + '_' + beatIndex).val();
		let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
		let beatSummary = $('#beatSummary_' + chapterIndex + '_' + beatIndex).val();
		let beatLoreBook = $('#beatLoreBook_' + chapterIndex + '_' + beatIndex).val();
		
		saveBeat(beatText, beatSummary, beatLoreBook, beatDescription, beatIndex, chapterIndex, chapterFilename);
	});
	
	$('.writeBeatTextBtn').off('click').on('click', function () {
		let beatIndex = Number($(this).parent().attr('data-beat-index'));
		let chapterIndex = Number($(this).parent().attr('data-chapter-index'));
		let chapterFilename = $(this).parent().attr('data-chapter-filename');
		
		let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
		
		writeBeatText(beatDescription, beatIndex, chapterIndex, chapterFilename, true, false);
	});
	
	$('.writeBeatSummaryBtn').off('click').on('click', function () {
		let beatIndex = Number($(this).parent().attr('data-beat-index'));
		let chapterIndex = Number($(this).parent().attr('data-chapter-index'));
		let chapterFilename = $(this).parent().attr('data-chapter-filename');
		
		let beatText = $('#beatText_' + chapterIndex + '_' + beatIndex).val();
		let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
		writeBeatSummary(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, true, false);
	});
	
	$('.updateBeatLoreBookBtn').off('click').on('click', function () {
		let beatIndex = Number($(this).parent().attr('data-beat-index'));
		let chapterIndex = Number($(this).parent().attr('data-chapter-index'));
		let chapterFilename = $(this).parent().attr('data-chapter-filename');
		
		let beatText = $('#beatText_' + chapterIndex + '_' + beatIndex).val();
		let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
		updateBeatLoreBook(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, true, false);
	});
	
	$("#recreateBeats").on('click', function (e) {
		e.preventDefault();
		recreateBeats(selectedChapter + '.json', parseInt($('#beatsPerChapter').val()));
	});
	
	$('#saveBeatsBtn').on('click', function (e) {
		e.preventDefault();
		
		let beats = [];
		
		$('#beatsList').find('.beat-outer-container').each(function (index, element) {
			let beatDescription = $(element).find('.beat-description-textarea').val();
			let beatText = $(element).find('.beat-text-textarea').val();
			let beatSummary = $(element).find('.beat-summary-textarea').val();
			beats.push({description: beatDescription, beat_text: beatText, beat_summary: beatSummary});
		});
		
		$.ajax({
			url: `/book/save-beats/${bookSlug}/${selectedChapter}.json`,
			method: 'POST',
			data: {
				llm: savedLlm,
				beats: JSON.stringify(beats)
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					$("#alertModalContent").html(__e('Beats saved successfully!'));
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					setTimeout(function () {
						location.reload();
					}, 2500);
					
				} else {
					$("#alertModalContent").html(__e('Failed to save beats: ') + response.message);
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			}
		});
	});
	
});
