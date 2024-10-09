let savedTheme = localStorage.getItem('theme') || 'light';
let savedLlm = localStorage.getItem('llm') || 'anthropic/claude-3-haiku:beta';


function applyTheme(theme) {
	if (theme === 'dark') {
		$('body').addClass('dark-mode');
		$('#modeIcon').removeClass('bi-sun').addClass('bi-moon');
		$('#modeToggleBtn').attr('aria-label', 'Switch to Light Mode');
	} else {
		$('body').removeClass('dark-mode');
		$('#modeIcon').removeClass('bi-moon').addClass('bi-sun');
		$('#modeToggleBtn').attr('aria-label', 'Switch to Dark Mode');
	}
}

$(document).ready(function () {
	
	applyTheme(savedTheme);
	
	$("#llmSelect").on('change', function () {
		localStorage.setItem('llm', $(this).val());
		savedLlm = $(this).val();
	});
	
	// change $llmSelect to savedLlm
	console.log('set llmSelect to ' + savedLlm);
	var dropdown = document.getElementById('llmSelect');
	var options = dropdown.getElementsByTagName('option');

	
	for (var i = 0; i < options.length; i++) {
		if (options[i].value === savedLlm) {
				dropdown.selectedIndex = i;
		}
	}
	
	// Manage z-index for multiple modals
	$('.modal').on('show.bs.modal', function () {
		const zIndex = 1040 + (10 * $('.modal:visible').length);
		$(this).css('z-index', zIndex);
		setTimeout(function () {
			$('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
		}, 0);
	});
	
	$('.modal').on('hidden.bs.modal', function () {
		if ($('.modal:visible').length) {
			// Adjust the backdrop z-index when closing a modal
			$('body').addClass('modal-open');
		}
	});
	
	
});


