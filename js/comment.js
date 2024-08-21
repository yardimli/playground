function createCommentHtml(comment) {
	const commentTime = formatRelativeTime(comment.timestamp);
	const editDeleteButtons = comment.user === currentUser ? `
        <button class="btn btn-sm btn-warning" onclick="editComment(event, '${comment.id}', '${comment.chapterFilename}')">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteComment(event, '${comment.id}', '${comment.chapterFilename}')">Delete</button>
    ` : '';
	return `
        <div class="comment mb-2" data-id="${comment.id}">
            <p>${comment.text}</p>
            <p><strong>${comment.user}</strong> <span title="${moment.utc(comment.timestamp).local().format('LLLL')}">${commentTime}</span></p>
            ${editDeleteButtons}
        </div>`;
}

function showCommentModal(event, chapterFilename) {
	event.stopPropagation();
	$('#commentChapterFilename').val(chapterFilename);
	$('#commentId').val('');
	$('#commentText').val('');
	$('#commentModalLabel').text('Add Comment');
	$('#commentModal').modal({backdrop: 'static', keyboard: true}).modal('show');
}

function editComment(event, commentId, chapterFilename) {
	event.stopPropagation();
	const comment = $(`.comment[data-id="${commentId}"]`);
	const commentText = comment.find('p:first').text();
	$('#commentChapterFilename').val(chapterFilename);
	$('#commentId').val(commentId);
	$('#commentText').val(commentText);
	$('#commentModalLabel').text('Edit Comment');
	$('#commentModal').modal({backdrop: 'static', keyboard: true}).modal('show');
}

function deleteComment(event, commentId, chapterFilename) {
	event.stopPropagation();
	if (confirm('Are you sure you want to delete this comment?')) {
		$.post('action-other-functions.php', {
			action: 'delete_comment',
			book: bookParam,
			id: commentId,
			chapterFilename: chapterFilename
		}, function (response) {
			if (response.success) {
				$(`.comment[data-id="${commentId}"]`).remove();
			}
		}, 'json');
	}
}

function saveComment() {
	const commentData = {
		action: 'save_comment',
		book: bookParam,
		chapterFilename: $('#commentChapterFilename').val(),
		id: $('#commentId').val(),
		text: $('#commentText').val(),
	};
	$.post('action-other-functions.php', commentData, function (response) {
		$('#commentModal').modal('hide');
		$('#commentForm')[0].reset();
		const comment = JSON.parse(response);
		if (comment.success) {
			comment.chapterFilename = commentData.chapterFilename; // Add chapterFilename to the comment
			const commentsList = $('#commentsList');
			$('.comments-section').show();
			if (comment.isNew) {
				commentsList.append(createCommentHtml(comment));
			} else {
				const commentElement = commentsList.find(`.comment[data-id="${comment.id}"]`);
				commentElement.replaceWith(createCommentHtml(comment));
			}
		} else
		{
			alert('Failed to save comment. ' + comment.message);
		}
	});
}

$(document).ready(function () {
	$("#showCommentModal").on('click', function (e) {
		e.preventDefault();
		showCommentModal(e, $('#chapterFilename').val());
	});
	
	$('#commentForm').on('submit', function (e) {
		e.preventDefault();
		saveComment();
	});
	
	$('#commentModal').on('shown.bs.modal', function () {
		$('#commentText').focus();
	});
	
});
