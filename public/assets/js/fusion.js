var current_temperature = '0.5';
var current_gpt = '3.5';
var auto_message_counter = 0;
var ai_ping_pong = 2;

let gpts = [
	{
		value: '3.5',
		title: "<b>GPT-3.5</b> is an advanced language prediction model by OpenAI. It generates contextually suitable text without producing any explicit content."
	},
	{
		value: 'SW',
		title: "<b>StoryWriter</b> A 13B parameter network good for story writing. It is an uncensored model, and is more likely to generate explicit content."
	},
	{
		value: '4.0',
		title: "<b>GPT-4.0</b> is the improved version of GPT-3.5 with better language prediction capabilities. It too does not generate offensive or explicit content."
	},
	{
		value: 'MI',
		title: "<b>Mistral</b> a new model that is less restricted that GPT-3.5 and GPT-4.0 but not as free as StoryWriter."
	}
];

let temperatures = [
	{
		value: '0.1',
		title: "<b>Least Creative, Best at Following Directions.</b> Provides accurate and reliable text generation that closely follows your instructions."
	},
	{
		value: '0.5',
		title: "<b>Moderately Creative, Good at Following Directions.</b> A flexible choice that combines creativity with adherence to your guidelines."
	},
	{
		value: '0.7',
		title: "<b>Highly Creative, Moderate at Following Directions.</b> Creative text generation that may sometimes deviate from your instructions."
	},
	{
		value: '1.0',
		title: "<b>Most Creative, Least Good at Following Directions.</b> Prioritizing creativity and unpredictability, but may not strictly adhere to provided instructions."
	}
];

async function sendMessage(message, temperature, send_type, get_suggestion_on_completion) {
	//			return response()->json([
	// 				'success' => true,
	// 				'message' => 'You have enough credits to continue.',
	// 			]);
	//call /check_balance to check if user has enough balance
	//if not, show modal to buy more credits
	//if yes, call /send-message
	
	if (current_story_nsfw) {
		if (send_type === 'send3' || send_type === 'send4') {
			send_type = 'send_mi';
		}
		if (send_type === 'suggest3' || send_type === 'suggest4') {
			send_type = 'suggest_mi';
		}
		if (send_type === 'rewrite3' || send_type === 'rewrite4') {
			send_type = 'rewrite_mi';
		}
	}
	
	$.get('/check_balance/' + send_type, function (response) {
		// Update the chat content with the new data
		if (response.success) {
			sendMessage2(message, temperature, send_type, get_suggestion_on_completion);
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

async function sendMessage2(message, temperature, send_type, get_suggestion_on_completion) {
	//prevent form from submitting
	
	if (current_guid === '') {
		alert('To send a message, please start a new chat. Or select a previous chat that belongs to you from the list on the left.');
		return;
	}
	
	message = message.trim();
	
	if (message === '') {
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
					if (message === '') {
						console.log('message is empty');
						message = 'continue writing the story.';
					}
					// addToChat('AIUser', 'Say something first.');
					// return;
				}
			} else {
				if (message === '') {
					console.log('message is empty');
					message = 'continue writing the story.';
				}
				// addToChat('AIUser', 'Say something first.');
				// return;
			}
		} else {
			if (message === '') {
				console.log('message is empty');
				message = 'continue writing the story.';
			}
			// addToChat('AIUser', 'Say something first.');
			// return;
		}
	}
	
	if (message !== '' && message !== 'USE_LAST_MESSAGE') {
		addToChat('User', message);
	}
	addToChat('AIUser', 'Thinking...');
	document.getElementById('message').value = '';
	let first_response = true;
	let response_div;
	
	setTimeout(function () {
		var scrollHeight = $("#chat").height();
		$(".chat-conversation-content").animate({scrollTop: scrollHeight}, 300);
		$('#scrollBtn').html('<i class="bi bi-arrow-bar-up"></i>');
	}, 100);
	
	
	let file_to_load = '/send-message?message=' + encodeURI(message) + '&temperature=' + temperature + '&send_type=' + send_type + '&guid=' + current_guid;
	const eventSource = new EventSource(file_to_load);
	
	eventSource.onmessage = function (e) {
		console.log(e.data);
		if (e.data == "[DONE]" || e.data.indexOf('"finish_reason": "stop"') > -1 || e.data.indexOf('"finish_reason": "length"') > -1) {
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
			if (get_suggestion_on_completion) {
				getSuggestion(true);
			}
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

function getSuggestion(press_send = false) {
	auto_message_counter++;
	if (auto_message_counter > ai_ping_pong) {
		press_send = false;
	}
	
	setTimeout(function () {
		var scrollHeight = $("#chat").height();
		$(".chat-conversation-content").animate({scrollTop: scrollHeight}, 300);
		$('#scrollBtn').html('<i class="bi bi-arrow-bar-up"></i>');
	}, 100);
	
	
	if (current_guid === '') {
		alert('To send a message, please start a new chat. Or select a previous chat that belongs to you from the list on the left.');
		return;
	}
	
	$("#message").val('');
	
	let send_type = 'one_suggestion_3';
	if (current_gpt === '4.0') {
		send_type = 'one_suggestion_4';
	}
	if (current_gpt === 'SW') {
		send_type = 'one_suggestion_sw';
	}
	if (current_gpt === 'MI') {
		send_type = 'one_suggestion_mi';
	}
	
	$.get('/check_balance/' + send_type, function (response) {
		// Update the chat content with the new data
		if (response.success) {
			
			
			let file_to_load = '/send-message?message=' + encodeURI('Help me out here.') + '&temperature=' + current_temperature + '&send_type=' + send_type + '&guid=' + current_guid;
			let eventSource_suggest = new EventSource(file_to_load);
			
			eventSource_suggest.onmessage = function (e) {
//		console.log(e.data);
				if (e.data == "[DONE]" || e.data.indexOf('"finish_reason": "stop"') > -1 || e.data.indexOf('"finish_reason": "length"') > -1) {
					console.log('[DONE]');
					eventSource_suggest.close();
					
					if (press_send) {
						let send_type = 'send3';
						if (current_gpt === '4.0') {
							send_type = 'send4';
						}
						if (current_gpt === 'SW') {
							send_type = 'send_mi';
						}
						if (current_gpt === 'MI') {
							send_type = 'send_mi';
						}
						
						sendMessage($("#message").val(), current_temperature, send_type, press_send);
					}
					
				} else {
					
					let txt = JSON.parse(e.data).choices[0].delta.content;
					//console.log(txt);
					if (txt !== undefined) {
						//append txt.replace(/(?:\r\n|\r|\n)/g, '<br>'); to textarea with id message
						document.getElementById('message').value += txt; // txt.replace(/(?:\r\n|\r|\n)/g, '<br>'); -- dont replace with <br> here as it feeds into textarea
						
						var offset = document.getElementById('message').offsetHeight - document.getElementById('message').clientHeight;
						
						document.getElementById('message').style.height = 'auto';
						document.getElementById('message').style.height = document.getElementById('message').scrollHeight + offset + 'px';
						
					}
				}
			};
			
			eventSource_suggest.onerror = function (e) {
				console.log(e);
				eventSource_suggest.close();
			};
			
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

function addToChat(sender, message) {
	const chat = document.getElementById('chat');
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
										<div class="${sender === 'User' ? 'user_message bg-light text-secondary' : 'bg-primary text-white'} p-2 px-3 rounded-2 message_paragraph">
											${message}
										</div>
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
	chat.scrollTop = chat.scrollHeight;
}

function updateChatContent(chat_history) {
	let chat_content = "";
	
	// Loop through the chat_history and generate the HTML content
	chat_history.forEach(function (message) {
		let message_edit_html = '';
		if (message.user_id === current_user_id || current_user_id === 1) {
			//console.log(message.user_id, current_user_id);
			message_edit_html = '';
			if (user_has_purchased || !user_has_purchased) {
				message_edit_html = `
				<div class="btn btn-secondary-soft btn-sm me-1 delete-message-button" onClick="deleteMessage(this)"
				     data-id="${message.id}"
				     style="padding-top: 4px; line-height: 1; height: 20px;">
					<i class="fa-solid fa-remove text-info"></i>
				</div>`;
			}
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

$(document).ready(function () {
	
	if (current_story_nsfw) {
		current_gpt = 'MI';
	}
	
	$("#show-ai-setup-modal").on('click', function (e) {
		if (current_story_nsfw) {
			$("#aiEroticSetupModal").modal('show');
		} else {
			$("#aiSetupModal").modal('show');
		}
	});
	
	$("#ai-setup-continue").on('click', function (e) {
		$("#aiSetupModal").modal('hide');
		if (ai_ping_pong > 0 && story_belongs_to_user === 1) {
			console.log('lets get a suggestion');
			getSuggestion(true);
		}
	});
	
	$("#ai-erotic-setup-continue").on('click', function (e) {
		$("#aiEroticSetupModal").modal('hide');
		if (ai_ping_pong > 0 && story_belongs_to_user === 1) {
			console.log('lets get a suggestion');
			getSuggestion(true);
		}
	});
	
	setTimeout(function () {
		$.get(chat_refresh, function (response) {
			// Update the chat content with the new data
			updateChatContent(response);
		});
	}, 1000);
	
	
	$(".temperature-btn").click(function () {
		$(this).addClass('active').siblings().removeClass('active');
		let index = parseInt($(this).attr('data-index'));
		current_temperature = temperatures[index].value;
		$('#temperature-hint').html(temperatures[index].title);
	});
	
	$(".gpt-btn").click(function () {
		$(this).addClass('active').siblings().removeClass('active');
		let index = parseInt($(this).attr('data-index'));
		current_gpt = gpts[index].value;
		$('#gpt-hint').html(gpts[index].title);
	});
	
	$(".ai-ping-pong-btn").click(function () {
		$(this).addClass('active').siblings().removeClass('active');
		ai_ping_pong = parseInt($(this).attr('data-index'));
	});
	
	handleOptionClick();
	
	$('#updateEditedMessage').on('click', function () {
		const messageId = $('#editMessageModal').data('messageId');
		const updatedMessage = $('#editMessageTextArea').val();
		
		// Call the route to update the message here
		updateMessage(messageId, updatedMessage);
		
		// Close the modal
		$('#editMessageModal').modal('hide');
	});
	
	// Scroll button click event
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
		let btn_tooltip = bootstrap.Tooltip.getInstance($(this));
		btn_tooltip.hide();
		//$('#writeStoryDropdown').dropdown('toggle');
		var send_type = 'send3';
		if (current_gpt === '4.0') {
			send_type = 'send4';
		}
		if (current_gpt === 'SW') {
			send_type = 'send_mi';
		}
		if (current_gpt === 'MI') {
			send_type = 'send_mi';
		}
		sendMessage($("#message").val(), current_temperature, send_type, false);
	});
	
	// $('#suggest').on('click', function (e) {
	// 	e.preventDefault();
	// 	$('#writeStoryDropdown').dropdown('toggle');
	// 	var send_type = 'suggest3';
	// 	if (current_gpt === '4.0') {
	// 		send_type = 'suggest4';
	// 	}
	// 	if (current_gpt === 'SW') {
	// 		send_type = 'suggest_sw';
	// 	}
	// 	if (current_gpt === 'MI') {
	// 		send_type = 'suggest_mi';
	// 	}
	// 	sendMessage('Help me out here.', current_temperature, send_type);
	// });
	
	$("#get-one-suggestion").on('click', function (e) {
		e.preventDefault();
		
		let btn_tooltip = bootstrap.Tooltip.getInstance($(this));
		btn_tooltip.hide();
		
		getSuggestion(false);
		
	});
	
	$('#rewrite').on('click', function (e) {
		e.preventDefault();
		//$('#writeStoryDropdown').dropdown('toggle');
		var send_type = 'rewrite3';
		if (current_gpt === '4.0') {
			send_type = 'rewrite4';
		}
		if (current_gpt === 'SW') {
			send_type = 'rewrite_sw';
		}
		if (current_gpt === 'MI') {
			send_type = 'rewrite_mi';
		}
		sendMessage('REWRITE: ' + $("#message").val(), current_temperature, send_type, false);
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
