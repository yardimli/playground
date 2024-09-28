var current_temperature = '0.5';
var current_gpt = '3.5';

function generateContent(field) {
	disableButtonsAndShowSpinner(field);
	
	const fieldValue = document.getElementById(field + '_input').value;

//			if (fieldValue.trim() === '') return;
	
	let file_to_load = '';
	
	if (field === 'about_me') {
		file_to_load = '/generate-content?nsfw='+nsfw_flag+'&field=' + encodeURIComponent(field) + '&value=' + encodeURIComponent(fieldValue);
	} else {
		file_to_load = '/generate-content?nsfw='+nsfw_flag+'&field=' + encodeURIComponent(field) + '&value=' + encodeURIComponent(fieldValue) + '&title=' + encodeURIComponent(document.getElementById('title_input').value) + '&protagonist=' + encodeURIComponent(document.getElementById('protagonist_input').value) + '&location=' + encodeURIComponent(document.getElementById('location_input').value) + '&antagonist=' + encodeURIComponent(document.getElementById('antagonist_input').value) + '&love_interest=' + encodeURIComponent(document.getElementById('love_interest_input').value) + '&confidant=' + encodeURIComponent(document.getElementById('confidant_input').value) + '&comic_relief=' + encodeURIComponent(document.getElementById('comic_relief_input').value) + '&story_language=' + encodeURIComponent(document.getElementById('story_language').value);
		
	}
	
	const eventSource = new EventSource(file_to_load);
	
	let inputField = document.getElementById(field + '_input');
	// inputField.value = 'Generating...';
	inputField.value = inputField.value + ' ';
	
	eventSource.onmessage = function (e) {
//		console.log(e.data);
		if (e.data == "[DONE]" || e.data.indexOf('"finish_reason": "stop"') > -1 || e.data.indexOf('"finish_reason": "length"') > -1) {
			console.log('[DONE]');
			enableButtonsAndHideSpinner(field);
			eventSource.close();
			inputField.value = inputField.value.trim();
			
			//check if the first character is a quote
			if (field === 'about_me' || field === 'story_image') {
			
			} else {
				
				if (inputField.value.charAt(0) == '"') {
					//remove qutoes from the beginning and end
					inputField.value = inputField.value.replace(/^"(.*)"$/, '$1');
				} else {
					if (!nsfw_flag) {
						//split the inputField.value from column and remove the first part if it exists
						let split = inputField.value.split(':');
						if (split.length > 1) {
							inputField.value = split[1];
						}
					}
				}
			}
			inputField.value = inputField.value.trim();
		} else {
			const txt = JSON.parse(e.data).choices[0].delta.content;
			if (txt !== undefined) {
				inputField.value += txt.replace(/(?:\r\n|\r|\n)/g, ' ');
				setTimeout(function () {
					updateCharCount(field);
					
					inputField.style.height = 'auto';
					inputField.style.height = (inputField.scrollHeight + 5) + 'px';
					
				}, 100);
			}
		}
	};
	
	eventSource.onerror = function (e) {
		console.log(e);
		enableButtonsAndHideSpinner(field);
//		inputField.value = '';
		eventSource.close();
	};
}

function disableButtonsAndShowSpinner(field) {
	$('#generate_' + field + '_button').prop('disabled', true);
	$('#' + field + '_spinner').css('display', 'inline-block');
}

function enableButtonsAndHideSpinner(field) {
	$('#generate_' + field + '_button').prop('disabled', false);
	$('#' + field + '_spinner').hide();
}

function updateCharCount(field) {
	const input = document.getElementById(field + '_input');
	const charCount = document.getElementById(field + '_char_count');
	
	const fields = ['title', 'protagonist', 'location', 'antagonist', 'love_interest', 'confidant', 'comic_relief', 'about_me', 'story_image', 'story_so_far', 'story_conflict', 'story_outline'];
	const field_limits = [99, 512, 512, 512, 512, 512, 512, 500, 1024, 512, 512, 512];
	
	const limit = field_limits[fields.indexOf(field)];
	
	const textLength = input.value.length;
	
	charCount.textContent = textLength + '/' + limit;
	
	if (textLength > limit) {
		$("#" + field + "_char_count").addClass("text-warning");
		disableSaveButton();
	} else {
		$("#" + field + "_char_count").removeClass("text-warning");
		checkAllCharCounts();
	}
}

function checkAllCharCounts() {
	const fields = ['title', 'protagonist', 'location', 'antagonist', 'love_interest', 'confidant', 'comic_relief', 'about_me', 'story_image', 'story_so_far', 'story_conflict', 'story_outline'];
	const field_limits = [99, 512, 512, 512, 512, 512, 512, 500, 1024, 512, 512, 512];
	
	for (const field of fields) {
		const input = document.getElementById(field + '_input');
		if (input) {
			const textLength = input.value.length;
			//get limit for this field
			const limit = field_limits[fields.indexOf(field)];
			
			if (textLength > limit) {
				disableSaveButton();
				return;
			}
		}
	}
	
	enableSaveButton();
}

function disableSaveButton() {
//	document.getElementById('generate_save_button').disabled = true;
	document.getElementById('generate_save_button_2').disabled = true;
}

function enableSaveButton() {
//	document.getElementById('generate_save_button').disabled = false;
	document.getElementById('generate_save_button_2').disabled = false;
}

$(document).ready(function () {
	
	$(".input-for-generate").on("input", function () {
		let field = $(this).attr('id');
		field = field.replace('_input', '');
		updateCharCount(field);
		
		this.style.height = 'auto';
		this.style.height = (this.scrollHeight + 5) + 'px';
	});
	
	
});

