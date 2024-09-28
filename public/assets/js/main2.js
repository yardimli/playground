function generateContent(field) {
	disableButtonsAndShowSpinner(field);
	
	const fieldValue = document.getElementById(field + '_input').value;

//			if (fieldValue.trim() === '') return;
	
	let file_to_load = '';
	
	if (field === 'about_me') {
		file_to_load = '/generate-content?field=' + encodeURIComponent(field) + '&value=' + encodeURIComponent(fieldValue);
	} else {
		file_to_load = '/generate-content?field=' + encodeURIComponent(field) + '&value=' + encodeURIComponent(fieldValue) + '&title=' + encodeURIComponent(document.getElementById('title_input').value) + '&protagonist=' + encodeURIComponent(document.getElementById('protagonist_input').value) + '&location=' + encodeURIComponent(document.getElementById('location_input').value) + '&antagonist=' + encodeURIComponent(document.getElementById('antagonist_input').value) + '&love_interest=' + encodeURIComponent(document.getElementById('love_interest_input').value) + '&confidant=' + encodeURIComponent(document.getElementById('confidant_input').value) + '&comic_relief=' + encodeURIComponent(document.getElementById('comic_relief_input').value)+ '&story_language=' + encodeURIComponent(document.getElementById('story_language').value);
		
	}
	
	const eventSource = new EventSource(file_to_load);
	
	let inputField = document.getElementById(field + '_input');
	// inputField.value = 'Generating...';
	inputField.value = inputField.value + ' ';
	
	eventSource.onmessage = function (e) {
//		console.log(e.data);
		if (e.data == "[DONE]") {
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
					//split the inputField.value from column and remove the first part if it exists
					let split = inputField.value.split(':');
					if (split.length > 1) {
						inputField.value = split[1];
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
					inputField.style.height = (inputField.scrollHeight+5) + 'px';
					
				}, 100);
			}
		}
	};
	
	eventSource.onerror = function (e) {
		console.log(e);
		enableButtonsAndHideSpinner(field);
		inputField.value = '';
		eventSource.close();
	};
}

async function sendMessage(message, temperature, send_type) {
	//			return response()->json([
	// 				'success' => true,
	// 				'message' => 'You have enough credits to continue.',
	// 			]);
	//call /check_balance to check if user has enough balance
	//if not, show modal to buy more credits
	//if yes, call /send-message
	$.get('/check_balance/' + send_type, function (response) {
		// Update the chat content with the new data
		if (response.success) {
			sendMessage2(message, temperature, send_type);
		} else {
			//show modal to buy more credits
			addToChat('AIUser', response.message);
			
			setTimeout(function () {
				var scrollHeight = $("#chat").height();
				$(".chat-conversation-content").animate({scrollTop: scrollHeight}, 300);
				$('#scrollBtn').html('<i class="bi bi-arrow-bar-up"></i>');
			}, 100);
		}
	});
}

async function sendMessage2(message, temperature, send_type) {
	//prevent form from submitting
	
	if (current_guid === '') {
		alert('To send a message, please start a new chat. Or select a previous chat that belongs to you from the list on the left.');
		return;
	}
	
	message = message.trim();
	
	if (message === '') {
		console.log('message is empty');
		//check if last .message_paragraph also has class .user_message
		
		setTimeout(function () {
			var scrollHeight = $("#chat").height();
			$(".chat-conversation-content").animate({scrollTop: scrollHeight}, 300);
			$('#scrollBtn').html('<i class="bi bi-arrow-bar-up"></i>');
		}, 100);
		
		var lastChildMessageParagraph = $("#chat").find(".message_paragraph:last");
		// var lastChildWithClass = $("#chat").find(":has(.user_message):last");
		if (lastChildMessageParagraph.length) {
			if (lastChildMessageParagraph.text() !== '') {
				if (lastChildMessageParagraph.hasClass('user_message')) {
					message = 'USE_LAST_MESSAGE';
				} else {
					addToChat('AIUser', 'Say something first.');
					return;
				}
			} else {
				addToChat('AIUser', 'Say something first.');
				return;
			}
		} else {
			addToChat('AIUser', 'Say something first.');
			return;
		}
	}
	
	if (message !== '' && message !== 'USE_LAST_MESSAGE') {
		addToChat('User', message);
	}
	
	if (send_type === 'story_setup') {
	
	} else
	{
		addToChat('AIUser', 'Thinking...');
		document.getElementById('message').value = '';
		let first_response = true;
		let response_div;
		
		setTimeout(function () {
			var scrollHeight = $("#chat").height();
			$(".chat-conversation-content").animate({scrollTop: scrollHeight}, 300);
			$('#scrollBtn').html('<i class="bi bi-arrow-bar-up"></i>');
		}, 100);
		
		
		let file_to_load = '/send-message2?message=' + encodeURI(message) + '&temperature=' + temperature + '&send_type=' + send_type + '&guid=' + current_guid;
		const eventSource = new EventSource(file_to_load);
		
		eventSource.onmessage = function (e) {
//		console.log(e.data);
			if (e.data == "[DONE]") {
				console.log('[DONE]');
				//console.log(response_div.innerHTML);
				var csrfToken = $('meta[name="csrf-token"]').attr('content');
				
				$.get(chat_refresh, function (response) {
					// Update the chat content with the new data
					updateChatContent(response);
				});
				
				// chat_input.disabled = false;
				// chat_button.disabled = false;
				// chat_input.focus()
				eventSource.close();
			} else {
				
				if (first_response) {
					addToChat('AIUser', '');
					first_response = false;
					response_div = document.getElementById('chat').lastElementChild;
					response_div = response_div.getElementsByClassName('message_paragraph')[0];
				}
				
				let txt = JSON.parse(e.data).choices[0].delta.content;
				
				//console.log(txt);
				if (txt !== undefined) {
					response_div.innerHTML += txt.replace(/(?:\r\n|\r|\n)/g, '<br>');
					//scroll to bottom of div
					var scrollHeight = $("#chat").height();
					$(".chat-conversation-content").scrollTop(scrollHeight);
					$('#scrollBtn').html('<i class="bi bi-arrow-bar-up"></i>');
				}
			}
		};
		
		eventSource.onerror = function (e) {
			console.log(e);
			document.getElementById('message').value = '';
			eventSource.close();
		};
	}
}


function disableButtonsAndShowSpinner(field) {
	$('#generate_' + field + '_button').prop('disabled', true);
	$('#' + field + '_spinner').css('display', 'inline-block');
}

function enableButtonsAndHideSpinner(field) {
	$('#generate_' + field + '_button').prop('disabled', false);
	$('#' + field + '_spinner').hide();
}

var chat_conversation = document.getElementById('chat');

function handleOptionClick() {
	$('.option_button').off('click').on('click', function () {
		let element = document.getElementById('message');
		element.value = $(this).text();
		// sendMessage(null);
		
		var offset = element.offsetHeight - element.clientHeight;
		element.style.height = 'auto';
		element.style.height = element.scrollHeight + offset + 'px';
		
	});
	
	$('.chat-message-div').off('mouseenter').on('mouseenter', function () {
		$(this).closest('.chat-message-div').find('.delete-message-button').css('display', 'inline-block');
		$(this).closest('.chat-message-div').find('.edit-message-button').css('display', 'inline-block');
	}).off('mouseleave').on('mouseleave', function () {
		$(this).closest('.chat-message-div').find('.delete-message-button').css('display', 'none');
		$(this).closest('.chat-message-div').find('.edit-message-button').css('display', 'none');
	});
}

function addToChat(sender, message, extra_options, extra_type) {
	const chat = document.getElementById('chat');
	const chat_conversation_content = document.getElementById('chat-conversation-content');
	const chatMessage = document.createElement('div');
	
	//remove all div's that have the class 'thinking_message'
	const thinkingMessages = document.querySelectorAll('.thinking_message');
	thinkingMessages.forEach(message => message.remove());
	
	var is_thinking = false;
	if (message === 'Thinking...') {
		is_thinking = true;
		message = `<div class="typing d-flex align-items-center" style="min-height: 20px;">
                              <div class="dot"></div>
                              <div class="dot"></div>
                              <div class="dot"></div>
                            </div>`;
	}
	
	chatMessage.innerHTML = `
						<div class="d-flex mb-1 chat-message-div justify-content-${sender === 'User' ? 'start' : 'end'} ${is_thinking ? 'thinking_message' : ''}" data-id="${message.id}">
							${sender === 'User' ? '<div class="flex-shrink-0 avatar avatar-xs me-2"><img class="avatar-img rounded-circle" src="' + chat_avatar + '" alt=""></div>' : ''}
							<div class="flex-grow-1">
								<div class="w-100">
									<div class="d-flex flex-column align-items-${sender === 'User' ? 'start' : 'end'}">
										<div class="${sender === 'User' ? 'user_message bg-light text-secondary' : 'bg-primary text-white'} p-2 px-3 rounded-2 message_paragraph"></div>
										<div class="small my-2" style="height: 25px;">
										</div>
									</div>
								</div>
							</div>
							${sender !== 'User' ? '<div class="flex-shrink-0 avatar avatar-xs ms-2"><img class="avatar-img rounded-circle" src="' + chat_avatar + '" alt=""></div>' : ''}
						</div>
`;
	
	
	// chatMessage.innerHTML = `<strong>${sender}:</strong> ${message}`;
	chat.appendChild(chatMessage);
	if (is_thinking || sender === 'User') {
		$(".message_paragraph:last").html(message);
		$(".chat-conversation-content").animate({scrollTop: chat.scrollHeight}, 300);
		
	} else
	{
		let current_message_written = '';
		let i = 0;
		let typingAnimation = setInterval(function(){
			if (i < message.length) {
				let randomCount = Math.floor(Math.random() * 5) + 1; // Random between 1-3
				let chars = message.substring(i, i + randomCount);
				
				if (i + randomCount > message.length)
					chars = message.substring(i, message.length);
				
				current_message_written += chars;
				$(".message_paragraph:last").html(current_message_written);
				i += randomCount;
				
			} else {
				clearInterval(typingAnimation);
				if (extra_options.trim() !== '') {
					let extra_options_array = extra_options.trim().split('\n');
					let dropdown = $('<select class="form-control" data-extra_type="' + extra_type + '"></select>');
					dropdown.append($('<option></option>').val('').text('select a language'));
					
					$.each(extra_options_array, function (index, value) {
						dropdown.append($('<option></option>').val(value).text(value));
					});
					
					dropdown.on('change', function () {
						var event = new CustomEvent('extra_OptionSelected', {
							detail: {
								extra_value: this.value,
								extra_type: $(this).data('extra_type')
							}
						});
						document.dispatchEvent(event);
					});
					
					$(".message_paragraph:last").append(dropdown);
					$(".chat-conversation-content").animate({scrollTop: chat.scrollHeight}, 300);
				}
			}
		}, 50);  // adjust typing speed here
	}
}

function updateChatContent(chat_history) {
	let chat_content = "";
	
	// Loop through the chat_history and generate the HTML content
	chat_history.forEach(function (message) {
		let message_edit_html = '';
		if (message.user_id === current_user_id) {
			//console.log(message.user_id, current_user_id);
			message_edit_html = '';
			// message_edit_html = `
			// 	<div class="btn btn-secondary-soft btn-sm me-1 delete-message-button" onClick="deleteMessage(this)"
			// 	     data-id="${message.id}"
			// 	     style="padding-top: 4px; line-height: 1; height: 20px;">
			// 		<i class="fa-solid fa-remove text-info"></i>
			// 	</div>`;
			if (!message.options) {
				message_edit_html += `
			<div class="btn btn-secondary-soft btn-sm me-2 edit-message-button" onClick="editMessage(this)"
			     data-id="${message.id}"
			     style="padding-top: 4px; line-height: 1; height: 20px;">
				<i class="fa-solid fa-pencil text-info"></i>
			</div>`;
			}
		}
		
		let message_html = `
						<div class="d-flex mb-1 chat-message-div justify-content-${message.sender === 'User' ? 'start' : 'end'}" data-id="${message.id}">
							${message.sender === 'User' ? '<div class="flex-shrink-0 avatar avatar-xs me-2"><a href="/writer-profile/' + message.username + '"><img class="avatar-img rounded-circle" src="' + message.avatar + '" alt=""></a></div>' : ''}
							<div class="flex-grow-1">
								<div class="w-100">
									<div class="d-flex flex-column align-items-${message.sender === 'User' ? 'start' : 'end'}">
										<div class="${message.sender === 'User' ? 'user_message bg-light text-secondary' : 'bg-primary text-white'} p-2 px-3 rounded-2 message_paragraph"  data-id="${message.id}">
											${message.message}
										</div>
										<div class="small my-2" style="height: 25px;">${message.time_ago}
											${message_edit_html}
										</div>
									</div>
								</div>
							</div>
							${message.sender !== 'User' ? '<div class="flex-shrink-0 avatar avatar-xs ms-2"><img class="avatar-img rounded-circle" src="' + chat_avatar + '" alt=""></div>' : ''}

						</div>
`;
		
		// Append the message to the chat content
		chat_content += message_html;
	});
	
	$("#chat").html(chat_content);
	handleOptionClick();
}

function getCurrentTime() {
	const now = new Date();
	let hours = now.getHours();
	let minutes = now.getMinutes();
	const ampm = hours >= 12 ? 'pm' : 'am';
	hours = hours % 12;
	hours = hours ? hours : 12; // the hour '0' should be '12'
	minutes = minutes < 10 ? '0' + minutes : minutes;
	return hours + ':' + minutes + ampm;
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
	document.getElementById('generate_save_button').disabled = true;
	document.getElementById('generate_save_button_2').disabled = true;
}

function enableSaveButton() {
	document.getElementById('generate_save_button').disabled = false;
	document.getElementById('generate_save_button_2').disabled = false;
}

function deleteMessage(btn) {
	var messageId = $(btn).data('id');
	console.log(messageId);
	
	// Store the messageId in the delete button for later reference
	$('#confirmDelete').data('messageId', messageId);
	
	// Show the confirmation modal
	$('#confirmDeleteModal').modal('show');
}

function editMessage(btn) {
	var messageId = $(btn).data('id');
//	console.log(messageId);
	var messageText = $(".message_paragraph[data-id='" + messageId + "']").html();
//	console.log(messageText);
	
	//convert <br> <br/> <br /> to new lines
	messageText = messageText.replace(/<br\s*[\/]?>/gi, '\n');
	//remove html tags
	messageText = messageText.replace(/<\/?[^>]+(>|$)/g, "");
	
	//remove duplicate new lines
	messageText = messageText.replace(/\n{3,}/g, '\n\n');
	//, trim tabs and spaces from the message
	messageText = messageText.trim();
	
	
	$('#editMessageModal').data('messageId', messageId);
	$('#editMessageTextArea').val(messageText);
	$('#editMessageModal').modal('show');
}

function updateMessage(messageId, updatedMessage) {
	const url = `/update-message/${messageId}`;
	const data = {
		_token: $('meta[name="csrf-token"]').attr('content'),
		message: updatedMessage,
	};
	
	$.post(url, data, function (response) {
			if (response.status === 'success') {
				// Update the message in the chat
				//replace new lines with <br>
				updatedMessage = updatedMessage.replace(/\n/g, "<br />\n");
				$(`div[data-id="${messageId}"]`).find('.message_paragraph').html(updatedMessage);
			} else {
				alert('Error: Could not update the message.');
			}
		})
		.fail(function () {
			alert('Error: Could not update the message.');
		});
}


var current_temperature = '0.5';
var current_gpt = '3.5';

$(document).ready(function () {
	
	$(".temperature-btn").click(function () {
		let temperatures = [
			{
				value: '0.1',
				title: "<b>Least Creative, Best at Following Directions.</b> <br> Provides accurate and reliable text generation that closely follows your instructions."
			},
			{
				value: '0.5',
				title: "<b>Moderately Creative, Good at Following Directions.</b> <br> A balanced option, giving you creative control while still generating text that mostly aligns with your instructions."
			},
			{
				value: '0.7',
				title: "<b>Highly Creative, Moderate at Following Directions.</b> <br>Creative text generation that may sometimes deviate from your instructions."
			},
			{
				value: '1.0',
				title: "<b>Most Creative, Least Good at Following Directions.</b> <br> Prioritizing creativity and unpredictability, but may not strictly adhere to provided instructions."
			}
		];
		
		let index = parseInt($(this).attr('data-index')) + 1;
		if (index >= temperatures.length) {
			index = 0;
		}
		
		current_temperature = temperatures[index].value;
		
		$(this).attr('data-index', index);
		$(this).attr('data-value', temperatures[index].value);
		$(this).html(temperatures[index].value);

//		$(this).attr('title', temperatures[index].title);
		$(this).attr('data-bs-original-title', temperatures[index].title);
		
		let btn_tooltip = bootstrap.Tooltip.getInstance($(this));
		btn_tooltip.show();
		
		$(this).on('mouseleave', function () {
			// Dispose the current tooltip instance
			btn_tooltip.dispose();
			
			// Create a new instance to make sure the updated tooltip is displayed on subsequent hover events
			btn_tooltip = new bootstrap.Tooltip($(this));
		});
		
	});
	
	$(".gpt-btn").click(function () {
		let gpts = [
			{
				value: '3.5',
				title: "<b>GPT-3.5</b> <br> A highly capable AI text generator for crafting engaging stories, offering remarkable creative writing capacities at an affordable price."
			},
			{
				value: '4.0',
				title: "<b>GPT-4.0</b> <br> The more advanced cousin of GPT 3.5, providing exceptional prowess in writing intricate stories, but with a steeper price tag for its enhanced capabilities."
			}
		];
		
		let index = parseInt($(this).attr('data-index')) + 1;
		if (index >= gpts.length) {
			index = 0;
		}
		
		current_gpt = gpts[index].value;
		
		$(this).attr('data-index', index);
		$(this).attr('data-value', gpts[index].value);
		$(this).html(gpts[index].value);
		$(this).attr('data-bs-original-title', gpts[index].title);
		let btn_tooltip = bootstrap.Tooltip.getInstance($(this));
		btn_tooltip.show();
		$(this).on('mouseleave', function () {
			btn_tooltip.dispose();
			btn_tooltip = new bootstrap.Tooltip($(this));
		});
		
	});
	
	handleOptionClick();
	
	if (current_page === 'chat_writer') {
		setTimeout(function () {
			$.get(chat_refresh, function (response) {
				// Update the chat content with the new data
				updateChatContent(response);
			});
		}, 1000);
	}
	
	$('#updateEditedMessage').on('click', function () {
		const messageId = $('#editMessageModal').data('messageId');
		const updatedMessage = $('#editMessageTextArea').val();
		
		// Call the route to update the message here
		updateMessage(messageId, updatedMessage);
		
		// Close the modal
		$('#editMessageModal').modal('hide');
	});
	
	$('#scrollBtn').on('click', function () {
//console.log(OverlayScrollbars(document.getElementById('chat')).scroll().max.y);
		
		
		var scrollHeight = $("#chat").height();
		//console.log(scrollHeight);
		// Check if the chat is already scrolled to the end
		if ($(this).html() === '<i class="bi bi-arrow-bar-up"></i>') {
			// Scroll to the beginning
			$(".chat-conversation-content").animate({scrollTop: 0}, 300);
			$('#scrollBtn').html('<i class="bi bi-arrow-bar-down"></i>');
		} else {
			// Scroll to the end
			$(".chat-conversation-content").animate({scrollTop: scrollHeight}, 300);
			$('#scrollBtn').html('<i class="bi bi-arrow-bar-up"></i>');
		}
	});
	
	$('#send').on('click', function (e) {
		e.preventDefault();
		$('#writeStoryDropdown').dropdown('toggle');
		var send_type = 'send3';
		if (current_gpt === '4.0') {
			send_type = 'send4';
		}
		sendMessage($("#message").val(), current_temperature, send_type);
	});
	
	$('#suggest').on('click', function (e) {
		e.preventDefault();
		$('#writeStoryDropdown').dropdown('toggle');
		var send_type = 'suggest3';
		if (current_gpt === '4.0') {
			send_type = 'suggest4';
		}
		sendMessage('Help me out here.', current_temperature, send_type);
	});
	
	$('#rewrite').on('click', function (e) {
		e.preventDefault();
		$('#writeStoryDropdown').dropdown('toggle');
		var send_type = 'rewrite3';
		if (current_gpt === '4.0') {
			send_type = 'rewrite4';
		}
		sendMessage('REWRITE: ' + $("#message").val(), current_temperature, send_type);
	});
	
	$(".input-for-generate").on("input", function () {
		let field = $(this).attr('id');
		field = field.replace('_input', '');
		updateCharCount(field);
		
		this.style.height = 'auto';
		this.style.height = (this.scrollHeight + 5) + 'px';
	});
	
	$('#confirmDelete').on('click', function () {
		var messageId = $(this).data('messageId');
		
		// Get the CSRF token from the meta tag
		var csrfToken = $('meta[name="csrf-token"]').attr('content');
		
		// Send a DELETE request to delete the message
		$.ajax({
			url: '/chat/delete-message/' + messageId,
			type: 'DELETE',
			headers: {
				'X-CSRF-TOKEN': csrfToken
			},
			success: function (response) {
				if (response.status === 'success') {
					// alert(response.message);
					
					// Remove the message from the DOM
					$('div[data-id="' + messageId + '"]').remove();
					
					$.get(chat_refresh, function (response) {
						// Update the chat content with the new data
						updateChatContent(response);
					});
					
					// Hide the confirmation modal
					$('#confirmDeleteModal').modal('hide');
				} else {
					alert('Error: Unable to delete message.');
				}
			},
			error: function (xhr, textStatus, errorThrown) {
				alert('Error: Unable to delete message.');
			}
		});
	});
	
});

