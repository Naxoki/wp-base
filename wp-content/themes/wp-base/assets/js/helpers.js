/**
 * Archivo: helpers.js
 * Objetivo: Funciones reutilizables de uso frecuente en proyectos WordPress
 *           basados en este theme. Pensadas para poder llamarse desde HTML
 *           (onclick, data-attributes) sin dependencias extra — solo
 *           Bootstrap 5 (ya vendorizado en resources/).
 *
 * Cada bloque está delimitado con marcadores "=== HELPER: nombre ===" /
 * "=== /HELPER ===" para que wp-tools/examples.php pueda leer este archivo
 * y mostrar el código real como snippet, sin duplicarlo a mano.
 */

// ----------------------------------------
// Helpers de modal (base interna)
// ----------------------------------------

// === HELPER: JSmodalBase ===
function JSmodalBase(title, bodyHtml, size, align) {
	size = size || 'medium';
	align = align || '';

	var sizeClasses = {
		small: 'modal-sm',
		medium: '',
		large: 'modal-lg',
		'extra-large': 'modal-xl',
	};
	var sizeClass = sizeClasses[size] || '';

	var alignParts = align.split(' ').filter(Boolean);
	var centered = alignParts.indexOf('center') !== -1 ? 'modal-dialog-centered' : '';
	var alignClasses = alignParts.map(function (a) { return 'modal-align-' + a; }).join(' ');

	var id = 'js-modal-' + Date.now();
	var modalHtml =
		'<div class="modal fade" id="' + id + '" tabindex="-1" aria-hidden="true">' +
			'<div class="modal-dialog ' + sizeClass + ' ' + centered + ' ' + alignClasses + '">' +
				'<div class="modal-content">' +
					'<div class="modal-header">' +
						'<h5 class="modal-title">' + title + '</h5>' +
						'<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>' +
					'</div>' +
					'<div class="modal-body">' + bodyHtml + '</div>' +
				'</div>' +
			'</div>' +
		'</div>';

	document.body.insertAdjacentHTML('beforeend', modalHtml);

	var modalEl = document.getElementById(id);
	var modal = new bootstrap.Modal(modalEl);

	modalEl.addEventListener('hidden.bs.modal', function () {
		modalEl.remove();
	});

	modal.show();
	return modal;
}
// === /HELPER ===

// ----------------------------------------
// Helpers de accion
// ----------------------------------------

// === HELPER: JSmodalAlert ===
/**
 * Muestra un texto simple en un modal.
 * Uso: JSmodalAlert('Título', 'Texto del mensaje', 'small', 'center')
 */
function JSmodalAlert(title, text, size, align) {
	return JSmodalBase(title, '<p class="mb-0">' + text + '</p>', size, align);
}
// === /HELPER ===

// === HELPER: JSmodalContent ===
/**
 * Muestra el contenido HTML de un elemento (por selector) dentro de un modal.
 * Uso: JSmodalContent('Título', '.mi-contenido-oculto', 'large')
 */
function JSmodalContent(title, selector, size, align) {
	var el = document.querySelector(selector);
	var html = el ? el.innerHTML : '';
	return JSmodalBase(title, html, size, align);
}
// === /HELPER ===

// === HELPER: JSvideoLaunch ===
/**
 * Abre un video (YouTube, Vimeo o Facebook) embebido en un modal.
 * Uso: JSvideoLaunch('Título', 'https://youtube.com/watch?v=xxxx', true, true, 'medium', 'center')
 */
function JSvideoLaunch(title, url, share, autoplay, size, align) {
	var embedUrl = JSgetVideoEmbedUrl(url, autoplay);
	var iframe = '<div class="ratio ratio-16x9"><iframe src="' + embedUrl + '" allow="autoplay; fullscreen" allowfullscreen></iframe></div>';
	var shareHtml = share
		? '<div class="mt-3"><label class="form-label small">Compartir</label><input type="text" class="form-control form-control-sm" readonly value="' + url + '" onclick="this.select()"></div>'
		: '';
	return JSmodalBase(title, iframe + shareHtml, size, align);
}

function JSgetVideoEmbedUrl(url, autoplay) {
	var auto = autoplay ? 1 : 0;

	var yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/);
	if (yt) {
		return 'https://www.youtube.com/embed/' + yt[1] + '?autoplay=' + auto;
	}

	var vimeo = url.match(/vimeo\.com\/(\d+)/);
	if (vimeo) {
		return 'https://player.vimeo.com/video/' + vimeo[1] + '?autoplay=' + auto;
	}

	if (url.indexOf('facebook.com') !== -1) {
		return 'https://www.facebook.com/plugins/video.php?href=' + encodeURIComponent(url) + '&autoplay=' + auto;
	}

	return url;
}
// === /HELPER ===

// === HELPER: JSwindowPopup ===
/**
 * Abre una ventana emergente del navegador a partir de elementos con la
 * clase "JSwindowPopup" y atributos data-win-*.
 * Uso: <button class="JSwindowPopup" data-win-url="https://..." data-win-size="640x480" data-win-align="center,center" data-win-scroll="yes">
 */
document.addEventListener('click', function (event) {
	var trigger = event.target.closest('.JSwindowPopup');
	if (!trigger) {
		return;
	}
	event.preventDefault();

	var url    = trigger.dataset.winUrl;
	var size   = (trigger.dataset.winSize || '640x480').split('x');
	var align  = (trigger.dataset.winAlign || 'center,center').split(',');
	var scroll = trigger.dataset.winScroll || 'yes';

	var width  = parseInt(size[0], 10) || 640;
	var height = parseInt(size[1], 10) || 480;
	var left   = JSresolveWindowAlign(align[0], window.screen.width, width);
	var top    = JSresolveWindowAlign(align[1] || align[0], window.screen.height, height);

	window.open(
		url,
		'_blank',
		'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',scrollbars=' + scroll
	);
});

function JSresolveWindowAlign(position, screenSize, elementSize) {
	if (position === 'center') {
		return Math.round((screenSize - elementSize) / 2);
	}
	if (position === 'left' || position === 'top') {
		return 0;
	}
	if (position === 'right' || position === 'bottom') {
		return screenSize - elementSize;
	}
	return 0;
}
// === /HELPER ===

// === HELPER: JSmapLaunch ===
/**
 * Abre un modal con enlaces a Google Maps y Waze (y opcionalmente un iframe
 * embebido) a partir de elementos con la clase "JSmapLaunch" y atributos
 * data-map-*.
 * Uso: <button class="JSmapLaunch" data-map-address="Calle 123" data-map-coords-1="-33.41,-70.58" data-map-coords-2="-33.41,-70.58" data-map-iframe="true">
 */
document.addEventListener('click', function (event) {
	var trigger = event.target.closest('.JSmapLaunch');
	if (!trigger) {
		return;
	}
	event.preventDefault();

	var address     = trigger.dataset.mapAddress || '';
	var coordsGoogle = trigger.dataset.mapCoords1 ? trigger.dataset.mapCoords1.split(',') : null;
	var coordsWaze   = trigger.dataset.mapCoords2 ? trigger.dataset.mapCoords2.split(',') : null;
	var showIframe   = trigger.dataset.mapIframe === 'true';
	var modalSize    = trigger.dataset.mapModalSize || 'medium';
	var modalAlign   = trigger.dataset.mapModalAlign || 'center';

	var googleUrl = coordsGoogle
		? 'https://www.google.com/maps/search/?api=1&query=' + coordsGoogle[0] + ',' + coordsGoogle[1]
		: 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(address);

	var wazeUrl = coordsWaze
		? 'https://waze.com/ul?ll=' + coordsWaze[0] + ',' + coordsWaze[1] + '&navigate=yes'
		: 'https://waze.com/ul?q=' + encodeURIComponent(address);

	var body =
		'<p>' + address + '</p>' +
		'<div class="d-flex gap-2 mb-3">' +
			'<a class="btn btn-primary" target="_blank" rel="noopener" href="' + googleUrl + '">Google Maps</a>' +
			'<a class="btn btn-outline-primary" target="_blank" rel="noopener" href="' + wazeUrl + '">Waze</a>' +
		'</div>';

	if (showIframe) {
		body += '<div class="ratio ratio-16x9"><iframe src="https://maps.google.com/maps?q=' + encodeURIComponent(address) + '&output=embed"></iframe></div>';
	}

	JSmodalBase('Ubicación', body, modalSize, modalAlign);
});
// === /HELPER ===

// === HELPER: JSdataTable ===
/**
 * Convierte una tabla HTML en una tabla ordenable, filtrable y paginada,
 * sin dependencias externas (equivalente liviano a DataTables).
 * Uso: <table class="JSdataTable" data-paging="true" data-searching="true" data-info="true" data-ordering="true">
 */
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.JSdataTable').forEach(JSinitDataTable);
});

function JSinitDataTable(table) {
	var paging    = table.dataset.paging !== 'false';
	var searching = table.dataset.searching !== 'false';
	var info      = table.dataset.info !== 'false';
	var ordering  = table.dataset.ordering !== 'false';
	var perPage   = parseInt(table.dataset.pageLength, 10) || 10;

	var wrapper = document.createElement('div');
	wrapper.className = 'js-datatable-wrapper';
	table.parentNode.insertBefore(wrapper, table);

	var searchInput = null;
	if (searching) {
		var searchWrap = document.createElement('div');
		searchWrap.className = 'mb-2 d-flex justify-content-end';
		searchWrap.innerHTML = '<input type="search" class="form-control form-control-sm w-auto" placeholder="Buscar...">';
		wrapper.appendChild(searchWrap);
		searchInput = searchWrap.querySelector('input');
	}

	wrapper.appendChild(table);

	var tbody   = table.querySelector('tbody');
	var allRows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
	var currentPage = 1;
	var sortState = { index: null, dir: 1 };

	if (ordering) {
		table.querySelectorAll('thead th').forEach(function (th, index) {
			th.style.cursor = 'pointer';
			th.addEventListener('click', function () {
				sortState.dir = (sortState.index === index) ? sortState.dir * -1 : 1;
				sortState.index = index;

				allRows.sort(function (a, b) {
					var valA = a.children[index].textContent.trim();
					var valB = b.children[index].textContent.trim();
					var numA = parseFloat(valA.replace(/[^0-9.-]/g, ''));
					var numB = parseFloat(valB.replace(/[^0-9.-]/g, ''));
					var isNumeric = !isNaN(numA) && !isNaN(numB) && valA !== '' && valB !== '';
					var comparison = isNumeric ? (numA - numB) : valA.localeCompare(valB);
					return comparison * sortState.dir;
				});

				currentPage = 1;
				render();
			});
		});
	}

	var infoEl = null;
	var paginationEl = null;

	if (info) {
		infoEl = document.createElement('div');
		infoEl.className = 'small text-muted mt-2';
		wrapper.appendChild(infoEl);
	}

	if (paging) {
		paginationEl = document.createElement('nav');
		paginationEl.setAttribute('aria-label', 'Paginación de tabla');
		paginationEl.innerHTML = '<ul class="pagination pagination-sm mt-2 mb-0"></ul>';
		wrapper.appendChild(paginationEl);
	}

	function getFilteredRows() {
		if (!searching || !searchInput || !searchInput.value) {
			return allRows;
		}
		var term = searchInput.value.toLowerCase();
		return allRows.filter(function (row) {
			return row.textContent.toLowerCase().indexOf(term) !== -1;
		});
	}

	function render() {
		var filtered = getFilteredRows();
		var totalPages = paging ? Math.max(1, Math.ceil(filtered.length / perPage)) : 1;
		currentPage = Math.min(currentPage, totalPages);

		var start = paging ? (currentPage - 1) * perPage : 0;
		var end   = paging ? start + perPage : filtered.length;
		var pageRows = filtered.slice(start, end);

		tbody.innerHTML = '';
		pageRows.forEach(function (row) { tbody.appendChild(row); });

		if (info) {
			infoEl.textContent = filtered.length === 0
				? 'No hay resultados'
				: 'Mostrando ' + (start + 1) + ' a ' + Math.min(end, filtered.length) + ' de ' + filtered.length + ' registros';
		}

		if (paging) {
			var list = paginationEl.querySelector('ul');
			list.innerHTML = '';
			for (var page = 1; page <= totalPages; page++) {
				var item = document.createElement('li');
				item.className = 'page-item' + (page === currentPage ? ' active' : '');
				item.innerHTML = '<a class="page-link" href="#">' + page + '</a>';
				item.addEventListener('click', (function (targetPage) {
					return function (event) {
						event.preventDefault();
						currentPage = targetPage;
						render();
					};
				})(page));
				list.appendChild(item);
			}
		}
	}

	if (searching) {
		searchInput.addEventListener('input', function () {
			currentPage = 1;
			render();
		});
	}

	render();
}
// === /HELPER ===
