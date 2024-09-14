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
				
				writeBeatText(beatDescription, beatIndex, false, true)
					.then(() => {
						const beatText = $(`#beatText_${beatIndex}`).val();
						return writeBeatSummary(beatText, beatDescription, beatIndex, false, true);
					})
					.then(() => {
						//add the newly written summary to the log
						$('#writeAllBeatsLog').append('<br><em>' + __e('Summary for beat ${beatIndex}:', {beatIndex: (beatIndex + 1)}) + '</em>');
						$('#writeAllBeatsLog').append('<br>' + $(`#beatSummary_${beatIndex}`).val());
						
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

function loadBeats() {
	const beatsList = $('#beatsList');
	beatsList.empty(); // Clear existing beats
	
	let beat_description_label = __e('Beat Description');
	let beat_text_label = __e('Beat Text');
	let beat_summary_label = __e('Beat Summary');
	let save_beat_label = __e('Save Beat');
	let write_beat_text_label = __e('Write Beat Text');
	let write_beat_summary_label = __e('Write Summary');
	let beat_label = __e('Beat');
	
	//check if currentChapter.beats is an array
	
	if (Array.isArray(currentChapter.beats)) {
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
									<div id="beatSummaryArea_${index}" class="mt-3">
										<label for="beatSummary_${index}" class="form-label">${beat_summary_label}</label>
										<textarea id="beatSummary_${index}" class="form-control beat-summary-textarea" rows="3">${beat.beat_summary ?? ''}</textarea>
									</div>
									
									<div class="" data-index="${index}">
										<button class="saveBeatBtn btn btn-success mt-3 me-2">${save_beat_label}</button>
										<button class="writeBeatTextBtn btn btn-primary mt-3 me-2">${write_beat_text_label}</button>
										<button class="writeBeatSummaryBtn btn btn-primary mt-3 me-2">${write_beat_summary_label}</button>
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
			let beatSummary = $('#beatSummary_' + beatIndex).val();
			saveBeat(beatText, beatSummary, beatDescription, beatIndex);
		});
		
		$('.writeBeatTextBtn').off('click').on('click', function () {
			let beatIndex = Number($(this).parent().attr('data-index'));
			let beatDescription = $('#beatDescription_' + beatIndex).val();
			writeBeatText(beatDescription, beatIndex, true, false);
		});
		
		$('.writeBeatSummaryBtn').off('click').on('click', function () {
			let beatIndex = Number($(this).parent().attr('data-index'));
			let beatText = $('#beatText_' + beatIndex).val();
			let beatDescription = $('#beatDescription_' + beatIndex).val();
			writeBeatSummary(beatText, beatDescription, beatIndex, true, false);
		});
	}
}

function recreateBeats( beatsPerChapter = 3 ) {
	$('#fullScreenOverlay').removeClass('d-none');
	$("#recreateBeats").prop('disabled', true);
	
	let previousChapterBeats = '';
	if (previousChapter !== null) {
		for (let i = 0; i < previousChapter.beats.length; i++) {
			previousChapterBeats += previousChapter.beats[i]?.beat_summary || previousChapter.beats[i]?.description || '';
			previousChapterBeats += "\n";
		}
	}
	
	// Now proceed with creating beats
	$.ajax({
		url: `/book/write-beats/${bookSlug}/${chapterFilename}`,
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
				const beatsList = $('#beatsList');
				beatsList.empty();
				response.beats.forEach((beat, index) => {
					
					let beat_description_label = __e('Beat Description');
					let beat_text_label = __e('Beat Text');
					let beat_summary_label = __e('Beat Summary');
					let save_beat_label = __e('Save Beat');
					let write_beat_text_label = __e('Write Beat Text');
					let write_beat_summary_label = __e('Write Summary');
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
									<div id="beatSummaryArea_${index}" class="mt-3">
										<label for="beatSummary_${index}" class="form-label">${beat_summary_label}</label>
										<textarea id="beatSummary_${index}" class="form-control beat-summary-textarea" rows="3"></textarea>
									</div>
									
									<div class="" data-index="${index}">
										<button class="saveBeatBtn btn btn-success mt-3 me-2">${save_beat_label}</button>
										<button class="writeBeatTextBtn btn btn-primary mt-3 me-2">${write_beat_text_label}</button>
										<button class="writeBeatSummaryBtn btn btn-primary mt-3 me-2">${write_beat_summary_label}</button>
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
					let beatSummary = $('#beatSummary_' + beatIndex).val();
					saveBeat(beatText, beatSummary, beatDescription, beatIndex);
				});
				
				$('.writeBeatTextBtn').off('click').on('click', function () {
					let beatIndex = Number($(this).parent().attr('data-index'));
					let beatDescription = $('#beatDescription_' + beatIndex).val();
					writeBeatText(beatDescription, beatIndex, true, false);
				});
				
				$('.writeBeatSummaryBtn').off('click').on('click', function () {
					let beatIndex = Number($(this).parent().attr('data-index'));
					let beatText = $('#beatText_' + beatIndex).val();
					let beatDescription = $('#beatDescription_' + beatIndex).val();
					writeBeatSummary(beatText, beatDescription, beatIndex, true, false);
				});
				
				$("#recreateBeats").prop('disabled', false);
				$("#writeAllBeatsBtn").prop('disabled', true);
				$("#writeAllBeatsBtn").html(__e('Click "Save Beats" before proceeding to write all beat contents.'));
				$("#alertModalContent").html(__e('All chapter Beat Descriptions generated successfully. Don\'t forget to save!'));
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				
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

function writeBeatText(beatDescription, beatIndex, showOverlay = true, save_results = false) {
	return new Promise((resolve, reject) => {
		if (showOverlay) {
			$('#fullScreenOverlay').removeClass('d-none');
		}
		$("#writeBeatTextBtn_" + beatIndex).prop('disabled', true);
		$("#beatDetailModalResult_" + beatIndex).html(__e('Writing beat text...'));
		
		
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
					$('#beatText_' + beatIndex).val(response.prompt);
					$('#beatDetailModalResult_' + beatIndex).html(__e('Beat text generated successfully!'));
					$('#writeBeatTextBtn_' + beatIndex).prop('disabled', false);
					resolve();
				} else {
					$('#beatDetailModalResult_' + beatIndex).html(__e('Failed to write beat text: ') + response.message);
					reject(__e('Failed to write beat text: ') + response.message);
					
				}
			},
			error: function () {
				$('#fullScreenOverlay').addClass('d-none');
				$('#beatDetailModalResult_' + beatIndex).html(__e('Failed to write beat text.'));
				reject(__e('Failed to write beat text.'));
			}
		});
	});
}

function writeBeatSummary(beatText, beatDescription, beatIndex, showOverlay = true, save_results = false) {
	return new Promise((resolve, reject) => {
		$('#writeBeatSummaryBtn_' + beatIndex).prop('disabled', true);
		if (showOverlay) {
			$('#fullScreenOverlay').removeClass('d-none');
		}
		$('#beatDetailModalResult_' + beatIndex).html(__e('Writing beat summary...'));
		
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
					$('#beatSummary_' + beatIndex).val(response.prompt);
					$('#beatDetailModalResult_' + beatIndex).html(__e('Beat summary generated successfully!'));
					$('#writeBeatSummaryBtn_' + beatIndex).prop('disabled', false);
					resolve();
				} else {
					$('#beatDetailModalResult_' + beatIndex).html(__e('Failed to write summary: ') + response.message);
					reject(__e('Failed to write summary: ') + response.message);
				}
			},
			error: function () {
				$('#fullScreenOverlay').addClass('d-none');
				$('#beatDetailModalResult_' + beatIndex).html(__e('Failed to write beat summary.'));
				reject(__e('Failed to write beat summary.'));
			}
		});
	});
}

function saveBeat(beatText, beatSummary, beatDescription, beatIndex) {
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
				$("#beatDetailModalResult_" + beatIndex).html(__e('Beat saved successfully!'));
			} else {
				$("#beatDetailModalResult_" + beatIndex).html(__e('Failed to save beat: ') + response.message);
			}
		}
	});
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
			let beatSummary = $(element).find('.beat-summary-textarea').val();
			beats.push({description: beatDescription, beat_text: beatText, beat_summary: beatSummary});
		});
		
		$.ajax({
			url: `/book/save-beats/${bookSlug}/${chapterFilename}`,
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
					},2500);
					
				} else {
					$("#alertModalContent").html(__e('Failed to save beats: ') + response.message);
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			}
		});
	});
	
	$("#recreateBeats").on('click', function (e) {
		e.preventDefault();
		recreateBeats(parseInt($('#beatsPerChapter').val()));
	});
	
	$('.closeAndRefreshButton').on('click', function () {
		location.reload();
	});
	
});
