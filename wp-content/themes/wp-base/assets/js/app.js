// Global variables
var consoleLog = 0;
var rootStyles = getComputedStyle(document.documentElement);
var screenWidth = document.body.screenWidth;
var screenHeight = document.documentElement.screenHeight;
var screenSize = {'xs'  : { 'up' : 575.98, 'down' : 576 },
				  'sm'  : { 'up' : 767.98, 'down' : 768 },
				  'md'  : { 'up' : 991.98, 'down' : 992 },
				  'lg'  : { 'up' : 1199.98, 'down' : 1200 },
				  'xl'  : { 'up' : 1359.98, 'down' : 1366 },
				  'xxl' : { 'up' : 1399.98, 'down' : 1400 }};

// On resize
window.onresize = function() {
	screenWidth = document.body.screenWidth;
	screenHeight = document.documentElement.screenHeight;
	/*
	// Responsive check
	if (screenWidth < screenSize.lg.up) {
		... more than ...
	}
	*/
	// Menu offcamvas click (Responsive)
	if (screenWidth > screenSize.md.down) {
		// Check offcanvas menu
		const offcanvasEl = document.getElementById('offcanvasRight')
		const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);

		if (bsOffcanvas) {
			bsOffcanvas.hide();
		}
	}
};

// On load
window.onload = function() {
    /*
	// Responsive check
	if (screenWidth < screenSize.lg.up) {
		... more than ...
	}
	*/
};

// On ready
window.addEventListener("DOMContentLoaded", function() { 
	/*
	// Responsive check
	if (screenWidth < screenSize.lg.up) {
		... more than ...
	}
	*/
	
	// Enable tooltip
	const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
	const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

	// Default hashtag click
	document.querySelectorAll('a[href="#"]:not([data-bs-toggle])').forEach((elem) => {
		// Click on
		elem.addEventListener("click", function(event) {
			event.preventDefault();
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	});

	// Custom hashtag click
	document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach((elem) => {
		// Click on
		elem.addEventListener("click", function(event) {
			event.preventDefault();
			const targetElement = document.getElementById(elem.getAttribute("href").substring(1));
			const scrollTop = targetElement.hasAttribute("data-offset-y") ? targetElement.offsetTop + parseFloat(targetElement.getAttribute("data-offset-y")) : targetElement.offsetTop;
			window.scrollTo({ top: scrollTop, behavior: 'smooth' });
		});
	});

	// Check forms validation
	if (document.querySelectorAll('.needs-validation').length > 0) {
		// Loop over each form and apply the validation logic
		document.querySelectorAll('.needs-validation').forEach(form => {
			form.addEventListener('submit', event => {
				if (!form.checkValidity()) {
					event.preventDefault();
					event.stopPropagation();
				}
				form.classList.add('was-validated');
			}, false);
		});
	}
	
	// Menu offcamvas click
	document.querySelectorAll('.offcanvas-body .is-button, .offcanvas-header a').forEach(button => {
		button.addEventListener('click', () => {
			// Check offcanvas menu
			const offcanvasEl = document.getElementById('offcanvasRight')
			const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);

			if (bsOffcanvas) {
				bsOffcanvas.hide();
			}
		});
	});
});

// On scroll
window.onscroll = function() {
	/*
	// Responsive check
	if (screenWidth < screenSize.lg.up) {
		... more than ...
	}
	*/
};