var loader = "<div class='loader'></div>"; // Declares a loader div

$(function(){
	ajaxCopyContents();
	ajaxViewExtra();
	ajaxRename();
	ajaxDelete();
});

/**
 * @brief Simple functionalities
 */
function ajaxCopyContents() {
	$('.copy-contents').off();
	$('.copy-contents').zclip({
		path: '/document_manager/swf/ZeroClipboard.swf',
		copy: function(){
			return $(this).attr('href');
		}
	});
}

/**
 * @brief Simple functionalities
 */
function ajaxViewExtra() {
	$('.wrapper:not(.detailed) .file-extra').hide();
	$('body').on('click', '.wrapper:not(.detailed) .view-extra', function(event){
		event.preventDefault();
		$(this).closest('.wrapper').find('.file-extra').slideDown(250, function(){
			$(this).closest('.wrapper').addClass('detailed');
		});
	});
	$('body').on('click', '.wrapper.detailed .view-extra', function(event){
		event.preventDefault();
		$(this).closest('.wrapper').find('.file-extra').slideUp(250, function(){
			$(this).closest('.wrapper').removeClass('detailed');
		});
	});
}

/**
 * @brief	Ajax Delete function
 * @details Binds to a click event on delete links, loads a yes/no dialog to first wrapper div. On yes, calls delete action on server and removes deleted DOM element.
 */
function ajaxDelete() {
	$('body').on('click', '.wrapper .ajax-delete', function(event) {
		event.preventDefault();

		if($(this).hasClass('confirm')){
			var title = $(this).attr('title');
			var msg;
			if ( typeof title !== 'undefined' && title !== false){
				msg = title;
			}
			var r = confirm(msg);
			if (r == false){
				return false;
			}
		}

		deleteFunction($(this));
	});
}

/**
 * @brief	Ajax Rename function
 * @details Binds to a click event on rename links, prompts user for new file name. On valid name, calls rename action on server and replaces selected DOM element.
 */
function ajaxRename() {
	$('body').on('click', '.wrapper .ajax-rename', function(event) {
		event.preventDefault();
		renameFunction($(this));
	});
}


function deleteFunction(element) {
	var url = element.attr('href');

	var targetDiv = element.closest('.wrapper');

	targetDiv.append(loader);
	$.get(url, function(data) {
		if (data.json.error) {
			alert(data.json.error);
			targetDiv.find('.loader').remove();
		} else {
			targetDiv.empty().animate({
				height: 0
			}, 1500, function() {
				$(this).remove();
			});
		};
	});
}

function renameFunction(element) {
	var type, name;
	name = element.attr('filename');
	var newName = prompt(element.attr('title'), name);
	if (newName) {
		var targetDiv = element.closest('.wrapper');

		$.ajax({
			type: 'POST',
			url: element.attr('href'),
			data: {
				file: name,
				newFile: newName
			},
			beforeSend: function() {
				targetDiv.height(targetDiv.height());
				targetDiv.append(loader);
			},
			success: function(data, textStatus, xhr) {
				if(data.error){
					alert(data.error);

					if(data.remove){
						targetDiv.empty().animate({
							height: 0
						}, 1500, function() {
							$(this).remove();
						});
					}else{
						targetDiv.find('.loader').remove();
					}
				}else{
					targetDiv.css({
						'height': 'auto'
					}).html(data);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				targetDiv.css({
					'height': 'auto'
				}).find('.loader').remove();
				alert(errorThrown + '__' + textStatus);
			}
		});
	}
}
