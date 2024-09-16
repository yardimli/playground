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

//------------------------------------------------------------
function saveBeat(beatText, beatSummary, beatDescription, beatIndex, chapterIndex, chapterFilename) {
	$.ajax({
		url: `/book/save-single-beat/${bookSlug}/${chapterFilename}`,
		method: 'POST',
		data: {
			llm: savedLlm,
			beatIndex: beatIndex,
			beatDescription: beatDescription,
			beatText: beatText,
			beatSummary: beatSummary
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
				$('#writeAllBeatsLog').append('<br>' + __e('Chapter ${chapterIndex}, Beat ${beatIndex} already has text. Skipping...', {chapterIndex : chapterIndex + 1, beatIndex: beatIndex + 1}) + '<br>');
				$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
				processedBeats++;
				let progress = Math.round((processedBeats / totalBeats) * 100);
				progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
				processNextBeat();
			} else {
				
				$('#writeAllBeatsLog').append('<br><br><em>' + __e('Writing chapter ${chapterIndex}, beat ${beatIndex}', {chapterIndex : chapterIndex + 1, beatIndex: (beatIndex + 1)}) + '</em>');
				
				$('#writeAllBeatsLog').append('<br><em>' + __e('Beat Description:') + '</em> ' + beatDescription);
				$('#writeAllBeatsLog').scrollTop(log[0].scrollHeight);
				
				writeBeatText(beatDescription, beatIndex, chapterIndex, chapterFilename, false, true)
					.then(() => {
						let beatText = $(`#beatText_${beatIndex}`).val();
						return writeBeatSummary(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, false, true);
					})
					.then(() => {
						//add the newly written summary to the log
						$('#writeAllBeatsLog').append('<br><em>' + __e('Summary for chapter ${chapterIndex}, beat ${beatIndex}:', {chapterIndex : chapterIndex + 1, beatIndex: (beatIndex + 1)}) + '</em>');
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
		
		saveBeat(beatText, beatSummary, beatDescription, beatIndex, chapterIndex, chapterFilename);
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
	
	
});
