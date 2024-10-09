function recreateBeats(selectedChapter, beatsPerChapter = 3, writingStyle = 'Minimalist', narrativeStyle = 'Third Person - The narrator has a godlike perspective') {
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
			writing_style: writingStyle,
			narrative_style: narrativeStyle,
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
					let chapterIndex = selectedChapterIndex;
					
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
                          rows="3">${beat.description}</textarea>
            </div>
            <div id="beatTextArea_${chapterIndex}_${beatIndex}" class="mt-3">
                <label for="beatText_${chapterIndex}_${beatIndex}"
                       class="form-label">${__e('Beat Text')}</label>
                <textarea id="beatText_${chapterIndex}_${beatIndex}" class="form-control beat-text-textarea"
                          rows="10"></textarea>
								<div id="beatTextResult_${chapterIndex}_${beatIndex}"></div>
            </div>
            <div id="beatSummaryArea_${chapterIndex}_${beatIndex}" class="mt-3">
                <label for="beatSummary_${chapterIndex}_${beatIndex}"
                       class="form-label">${__e('Beat Summary')}</label>
                <textarea id="beatSummary_${chapterIndex}_${beatIndex}" class="form-control beat-summary-textarea"
                          rows="3"></textarea>
								<div id="beatSummaryResult_${chapterIndex}_${beatIndex}"></div>
            </div>
            <div id="loreBookArea_${chapterIndex}_${beatIndex}" class="mt-3">
                <label for="loreBook_${chapterIndex}_${beatIndex}"
                       class="form-label">${__e('Lore Book')}</label>
                <textarea id="loreBook_${chapterIndex}_${beatIndex}" class="form-control lore-book-textarea"
                          rows="6"></textarea>
								<div id="loreResult_${chapterIndex}_${beatIndex}"></div>
            </div>
        </div>
    `;
					$('#beatsList').append(beatHtml);
					
				});
				
				$("#alertModalContent").html(__e('All chapter Beat Descriptions generated successfully.') + "<br>" + __e('Please review the beats and click "Save Beats" to save the changes.') + "<br>" + __e('You will need to save the beats before proceeding to write the beat contents.'));
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

//------------------------------------------------------------
function writeBeatDescription(beatDescription, beatIndex, chapterIndex, chapterFilename, showOverlay = true, save_results = false, writingStyle = 'Minimalist', narrativeStyle = 'Third Person - The narrator has a godlike perspective') {
	return new Promise((resolve, reject) => {
		if (showOverlay) {
			$('#fullScreenOverlay').removeClass('d-none');
		}
		
		$("#writeBeatDescriptionBtn_" + chapterIndex + '_' + beatIndex).prop('disabled', true);
		$("#beatDescriptionResult_" + chapterIndex + '_' + beatIndex).html(__e('Writing beat description...'));
		
		$.ajax({
			url: `/book/write-beat-description/${bookSlug}/${chapterFilename}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				writing_style: writingStyle,
				narrative_style: narrativeStyle,
				beat_index: beatIndex,
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
					$('#beatDescription_' + chapterIndex + '_' + beatIndex).val(response.prompt);
					$('#beatDescriptionResult_' + chapterIndex + '_' + beatIndex).html(__e('Beat description generated successfully!'));
					$('#writeBeatDescriptionBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', false);
					resolve(response.prompt);
				} else {
					$('#beatDescriptionResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to write beat description:') + response.message);
					reject(__e('Failed to write beat description:') + response.message);
				}
			},
			error: function () {
				$('#fullScreenOverlay').addClass('d-none');
				$('#beatDescriptionResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to write beat description:'));
				reject(__e('Failed to write beat description:'));
			}
		});
	});
}

//------------------------------------------------------------
function writeBeatText(beatDescription, beatIndex, chapterIndex, chapterFilename, showOverlay = true, save_results = false, writingStyle = 'Minimalist', narrativeStyle = 'Third Person - The narrator has a godlike perspective') {
	return new Promise((resolve, reject) => {
		if (showOverlay) {
			$('#fullScreenOverlay').removeClass('d-none');
		}
		
		$("#writeBeatTextBtn_" + chapterIndex + '_' + beatIndex).prop('disabled', true);
		$("#beatTextResult_" + chapterIndex + '_' + beatIndex).html(__e('Writing beat text...'));
		
		$.ajax({
			url: `/book/write-beat-text/${bookSlug}/${chapterFilename}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				writing_style: writingStyle,
				narrative_style: narrativeStyle,
				beat_index: beatIndex,
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
					$('#beatTextResult_' + chapterIndex + '_' + beatIndex).html(__e('Beat text generated successfully!'));
					$('#writeBeatTextBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', false);
					resolve(response.prompt);
				} else {
					$('#beatTextResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to write beat text: ') + response.message);
					reject(__e('Failed to write beat text: ') + response.message);
				}
			},
			error: function () {
				$('#fullScreenOverlay').addClass('d-none');
				$('#beatTextResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to write beat text.'));
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
		$('#beatSummaryResult_' + chapterIndex + '_' + beatIndex).html(__e('Writing beat summary...'));
		
		$.ajax({
			url: `/book/write-beat-summary/${bookSlug}/${chapterFilename}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				beat_index: beatIndex,
				current_beat_description: beatDescription,
				current_beat_text: beatText,
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
					$('#beatSummaryResult_' + chapterIndex + '_' + beatIndex).html(__e('Beat summary generated successfully!'));
					$('#writeBeatSummaryBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', false);
					resolve(response.prompt);
				} else {
					$('#beatSummaryResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to write summary: ') + response.message);
					reject(__e('Failed to write summary: ') + response.message);
				}
			},
			error: function () {
				$('#fullScreenOverlay').addClass('d-none');
				$('#beatSummaryResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to write beat summary.'));
				reject(__e('Failed to write beat summary.'));
			}
		});
	});
}

function updateLoreBook(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, showOverlay = true, save_results = false) {
	return new Promise((resolve, reject) => {
		if (showOverlay) {
			$('#fullScreenOverlay').removeClass('d-none');
		}
		$('#updateLoreBookBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', true);
		$('#loreResult_' + chapterIndex + '_' + beatIndex).html(__e('Updating Lore Book...'));
		
		$.ajax({
			url: `/book/update-lore-book/${bookSlug}/${chapterFilename}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				beat_index: beatIndex,
				current_beat_description: beatDescription,
				current_beat_text: beatText,
				save_results: save_results,
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				$('#fullScreenOverlay').addClass('d-none');
				if (response.success) {
					$('#loreBook_' + chapterIndex + '_' + beatIndex).val(response.prompt);
					$('#loreResult_' + chapterIndex + '_' + beatIndex).html(__e('Lore book updated successfully!'));
					$('#updateLoreBookBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', false);
					resolve(response.prompt);
				} else {
					$('#loreResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to update lore book: ') + response.message);
					reject(__e('Failed to update lore book: ') + response.message);
				}
			},
			error: function () {
				$('#fullScreenOverlay').addClass('d-none');
				$('#loreResult_' + chapterIndex + '_' + beatIndex).html(__e('Failed to update lore book.'));
				reject(__e('Failed to update lore book.'));
			}
		});
	});
}

//------------------------------------------------------------
function saveBeat(beatText, beatSummary, loreBook, beatDescription, beatIndex, chapterIndex, chapterFilename) {
	$.ajax({
		url: `/book/save-single-beat/${bookSlug}/${chapterFilename}`,
		method: 'POST',
		data: {
			llm: savedLlm,
			beat_index: beatIndex,
			beat_description: beatDescription,
			beat_text: beatText,
			beat_summary: beatSummary,
			lore_book: loreBook,
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
				$('#writeAllBeatsLog').append('<br>' + __e('Chapter ${chapterIndex}, Beat ${beatIndex} already has text. Skipping...', {
					chapterIndex: chapterIndex,
					beatIndex: beatIndex + 1
				}) + '<br>');
				$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
				processedBeats++;
				let progress = Math.round((processedBeats / totalBeats) * 100);
				progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
				processNextBeat();
			} else {
				
				$('#writeAllBeatsLog').append('<br><br><em>' + __e('Writing chapter ${chapterIndex}, beat ${beatIndex}', {
					chapterIndex: chapterIndex,
					beatIndex: (beatIndex + 1)
				}) + '</em>');
				
				$('#writeAllBeatsLog').append('<br><em>' + __e('Beat Description:') + '</em> ' + beatDescription);
				$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
				
				writeBeatText(beatDescription, beatIndex, chapterIndex, chapterFilename, false, true, $('#writingStyle').val(), $('#narrativeStyle').val())
					.then(() => {
						let beatText = $(`#beatText_${beatIndex}`).val();
						return writeBeatSummary(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, false, true);
					})
					.then(() => {
						let beatText = $(`#beatText_${chapterIndex}_${beatIndex}`).val();
						return updateLoreBook(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, false, true);
					})
					.then(() => {
						//add the newly written summary to the log
						$('#writeAllBeatsLog').append('<br><em>' + __e('Summary for chapter ${chapterIndex}, beat ${beatIndex}:', {
							chapterIndex: chapterIndex,
							beatIndex: (beatIndex + 1)
						}) + '</em>');
						$('#writeAllBeatsLog').append('<br>' + $(`#beatSummary_${chapterIndex}_${beatIndex}`).val());
						
						$('#writeAllBeatsLog').append('<br><em>' + __e('Lore Book for chapter ${chapterIndex}, beat ${beatIndex}:', {
							chapterIndex: chapterIndex,
							beatIndex: (beatIndex + 1)
						}) + '</em>');
						$('#writeAllBeatsLog').append('<br>' + $(`#loreBook_${chapterIndex}_${beatIndex}`).val());
						
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
	
	if (selectedChapter !== '') {
		$('#writeAllBeatsBtn').hide();
		$("#saveBeatsBtn").hide();
		$('#recreateBeats').show();
		$('#beatsPerChapter').show();
		$('#beatsPerChapterLabel').show();
	} else {
		$('#writeAllBeatsBtn').show();
		$("#saveBeatsBtn").hide();
		$('#recreateBeats').hide();
		$('#beatsPerChapter').hide();
		$('#beatsPerChapterLabel').hide();
	}
	
	//check if the beat-description-textarea is empty
	let emptyBeatDescriptions = true;
	$('.beat-description-textarea').each(function (index, element) {
		if ($(element).val().trim() !== '') {
			emptyBeatDescriptions = false;
		}
	});
	
	if (emptyBeatDescriptions) {
		//show the alert modal
		$("#alertModalContent").html(__e('This chapter has no beats written yet.') + "<br>" + __e('Click "Recreate Beats" to generate beat descriptions.') + "<span style=\"font-size: 18px;\">" + __e('Number of beats per chapter:') + "</span><br><br>\n" +
			"\t\t\t\t\t\t<select id=\"beatsPerChapter_modal\" class=\"form-select mx-auto mb-1\">\n" +
			"\t\t\t\t\t\t\t<option value=\"2\" selected>2</option>\n" +
			"\t\t\t\t\t\t\t<option value=\"3\">3</option>\n" +
			"\t\t\t\t\t\t\t<option value=\"4\">4</option>\n" +
			"\t\t\t\t\t\t\t<option value=\"5\">5</option>\n" +
			"\t\t\t\t\t\t\t<option value=\"6\">6</option>\n" +
			"\t\t\t\t\t\t\t<option value=\"7\">7</option>\n" +
			"\t\t\t\t\t\t\t<option value=\"8\">8</option>\n" +
			"\t\t\t\t\t\t\t<option value=\"9\">9</option>\n" +
			"\t\t\t\t\t\t\t<option value=\"10\">10</option>\n" +
			"\t\t\t\t\t\t</select>" + "<br><button type=\"button\" class=\"btn btn-success mt-1 mb-1 w-100\" id=\"recreateBeats_modal\"><i\n" +
			"\t\t\t\t\t\t\t\tclass=\"bi bi-pencil\"></i>" + __e('Recreate Beats') + "</button>");
		$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
		$("#recreateBeats_modal").off('click').on('click', function () {
			$("#alertModal").modal('hide');
			recreateBeats(selectedChapter + '.json', parseInt($('#beatsPerChapter_modal').val()), $('#writingStyle').val(), $('#narrativeStyle').val());
		});
		
	}
	
	
	$('.addEmptyBeatBtn').off('click').on('click', function () {
		let chapterIndex = $(this).data('chapter-index');
		let chapterFilename = $(this).data('chapter-filename');
		let beatIndex = $(this).data('beat-index');
		let position = $(this).data('position');
		
		let newBeat = {
			description: '',
			beat_text: '',
			beat_summary: '',
			lore_book: ''
		};
		
		$.ajax({
			url: `/book/add-empty-beat/${bookSlug}/${chapterFilename}`,
			method: 'POST',
			data: {
				beat_index: beatIndex,
				position: position,
				new_beat: JSON.stringify(newBeat)
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function (response) {
				if (response.success) {
					location.reload(); // Refresh the page
				} else {
					alert('Failed to add empty beat: ' + response.message);
				}
			},
			error: function () {
				alert('An error occurred while adding the empty beat.');
			}
		});
	});
	
	$('#writeAllBeatsBtn').on('click', function () {
		writeAllBeats();
	});
	
	$('.closeAndRefreshButton').on('click', function () {
		location.reload();
	});
	
	$('.saveBeatBtn').off('click').on('click', function () {
		let beatIndex = Number($(this).attr('data-beat-index'));
		let chapterIndex = Number($(this).attr('data-chapter-index'));
		let chapterFilename = $(this).attr('data-chapter-filename');
		
		let beatText = $('#beatText_' + chapterIndex + '_' + beatIndex).val();
		let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
		let beatSummary = $('#beatSummary_' + chapterIndex + '_' + beatIndex).val();
		let loreBook = $('#loreBook_' + chapterIndex + '_' + beatIndex).val();
		
		saveBeat(beatText, beatSummary, loreBook, beatDescription, beatIndex, chapterIndex, chapterFilename);
	});
	
	$('.writeBeatDescriptionBtn').off('click').on('click', function () {
		let beatIndex = Number($(this).attr('data-beat-index'));
		let chapterIndex = Number($(this).attr('data-chapter-index'));
		let chapterFilename = $(this).attr('data-chapter-filename');
		
		let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
		
		writeBeatDescription(beatDescription, beatIndex, chapterIndex, chapterFilename, true, false, $('#writingStyle').val(), $('#narrativeStyle').val());
	});
	
	$('.writeBeatTextBtn').off('click').on('click', function () {
		let beatIndex = Number($(this).attr('data-beat-index'));
		let chapterIndex = Number($(this).attr('data-chapter-index'));
		let chapterFilename = $(this).attr('data-chapter-filename');
		
		let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
		
		writeBeatText(beatDescription, beatIndex, chapterIndex, chapterFilename, true, false, $('#writingStyle').val(), $('#narrativeStyle').val());
	});
	
	$('.writeBeatSummaryBtn').off('click').on('click', function () {
		let beatIndex = Number($(this).attr('data-beat-index'));
		let chapterIndex = Number($(this).attr('data-chapter-index'));
		let chapterFilename = $(this).attr('data-chapter-filename');
		
		let beatText = $('#beatText_' + chapterIndex + '_' + beatIndex).val();
		let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
		writeBeatSummary(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, true, false);
	});
	
	$('.updateLoreBookBtn').off('click').on('click', function () {
		let beatIndex = Number($(this).attr('data-beat-index'));
		let chapterIndex = Number($(this).attr('data-chapter-index'));
		let chapterFilename = $(this).attr('data-chapter-filename');
		
		let beatText = $('#beatText_' + chapterIndex + '_' + beatIndex).val();
		let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
		updateLoreBook(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, true, false);
	});
	
	$("#recreateBeats").on('click', function (e) {
		e.preventDefault();
		recreateBeats(selectedChapter + '.json', parseInt($('#beatsPerChapter').val()), $('#writingStyle').val(), $('#narrativeStyle').val());
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
