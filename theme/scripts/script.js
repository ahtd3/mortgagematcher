(function($)
{
	// let IS_DEBUG_IP = false; $.get('/processes/ajax/is-debug-ip.php', '', function(data){ IS_DEBUG_IP = data.result; }, 'json' ); // debugging helper

	$(document).ready(function()
	{
		// slideshows
		Foxy.init('.slideshow .foxy', {animation : 'fade', arrows: false, bullets: false, timer: 6000 });
		Fennecs.init('.js-featured-products.fennecs', {animation : 'fade', arrows: true, bullets: false, timer: undefined });

		// galleries
		let licenceKey = "0000-0000-000-0000"; // We've got an actual license somewhere, but this stops it displaying a warning, so good enough

		/// Gallery module
		lightGallery($(".gallery").get(0), {plugins: [lgThumbnail], thumbnail: true, exThumbImage: 'data-thumb', licenseKey: licenceKey});

		/// Product images gallery or similar. comment out unused options
		//// option: everything triggers lightGallery
		// lightGallery($("product-images").get(0), {selector: '.image', licenseKey: licenceKey});
		//// option: clicking thumbnail switches main image, clicking main image triggers lightGallery
		setupCatalogueItemGallery();

		//forms
		setupForms();

		$(".popup").click(function(event)
		{
			event.preventDefault();

			// noinspection JSUnusedGlobalSymbols
			$.featherlight($(this).attr('href') + '?popup',
			{
				afterOpen: function()
				{
					setupForms('.featherlight');
				}
			});
		});

		//featherlight form popup
		$("body").on("click", ".js-open-form", function(event)
		{
			event.preventDefault();
			var targetId = $(this).attr('href');

			// fallback to something with .js-popup-form
			var $clone = targetId.charAt(0) === '#' && targetId.length > 1 ? $(targetId).clone() : $(".js-popup-form").clone();

			$.featherlight($clone, {variant: 'open-popup-form'});

			window.setTimeout(function()
			{
				var $form = $('.featherlight.open-popup-form form');
				setupForm($form);

				if (!$form.hasClass('js-ajax-form'))//Forms module forms are ajaxed by default
				{
					$form.submit(function(event)
					{
						event.preventDefault();
						ajaxForm($(this), event);
					});
				}
			}, 0);
		});

		//Lightweight alternative to jQuery UI's accordion
		$(".js-fader").hide();

		$(".js-activator").click(function(event)
		{
			event.preventDefault();

			// find the target:
			let targetId = '';

			// if this fader /is/ open, we leave the target blank and it will close
			if(!$(this).hasClass('open'))
			{
				// does the clicked element have an href (is it an a or similar)?
				if($(this).attr("href") !== undefined)
				{
					targetId = $(this).attr("href");
				}
				// no? are we passing the target in a data attribute?
				else if($(this).data('target') !== undefined)
				{
					targetId = $(this).data('target');
				}
				// no? find the next fader element if one exists and use that as the target.
				else
				{
					let $target = $(this).nextAll('.js-fader').first();
					if($target.length === 0)
					{
						// well then, take no action
						return false;
					}
					// else

					targetId = $target[0].id;
					if(targetId === '')
					{
						// give target a practically unique id for the rest of this page load
						targetId = Date.now().toString();
						$target[0].id = targetId;
					}

					targetId  = '#' + targetId;

					// lets not have to nextAll() etc if this is clicked again
					$(this).data('target', targetId);
				}
			}

			// destyle currently open activators (if any)
			$('.js-activator').removeClass('open');

			// (start to) close open faders (if any)
			$(".js-fader").slideUp(200);

			if(targetId !== '')
			{
				// (start to) open the requested fader
				$(targetId).slideDown(200);

				// style this activator
				$(this).addClass('open');
			}
		});

		//alternative to jQuery UI's tabs
		$('.js-tab-link').click(function(event)
		{
			event.preventDefault();

			$('.js-tab-link').removeClass('active');
			$(this).addClass('active');

			$('.js-tab').removeClass('active');
			$($(this).attr('href')).addClass('active');
		});

		//mobile nav
		$('.open-nav').click(function(event)
		{
			event.preventDefault();
			$(this).toggleClass('open');
			$(".main-navigation").toggleClass("open");
			$("body").toggleClass("open");
		});

		$('.main-navigation').find('li .open-sub').click(function()
		{
			$(this).closest('li').toggleClass('open');
		});

		//login popup
		if ($('.account-nav').length > 0 && $('.account-nav').hasClass('do-form'))
		{
			$('.account-nav > a').click(function(event)
			{
				event.preventDefault();
				$('.account-nav').toggleClass('open');
			});

			// close login form when you click away
			$('body').click(function(event)
			{
				$('.account-nav').removeClass('open');
			});

			//override close if you click inside the login form
			$('.account-nav').click(function(event)
			{
				event.stopPropagation();
			});
		}

		if ($('html').hasClass('Mortgage-Matcher'))
		{
			mortgageMatcherSetUp();
		}
	});

	function mortgageMatcherSetUp()
	{


		console.log('Mortgage Matcher magic here');



	}

		/**
	 * Sets up a Products module or similar gallery with the actions
	 *	- when a thumbnail is clicked make that image the main-image
	 *  - when the main image is clicked open lightGallery with the current image displayed
	 * @param string containerClass optional wrapper for if we have multipe galleries in the site with different behaviours
	 */
	function setupCatalogueItemGallery(containerClass)
	{
		if(containerClass === undefined)
		{
			containerClass = '';
		}
		else
		{
			// append a space so we don't need to perform checks or have leading spaces in the selectors below
			$containerClass += ' ';
		}

		// small images?
		let $smallImages = $(`${containerClass}.js-small-images`);

		// add click action to main image
		let licenceKey = "0000-0000-000-0000"; // We've got an actual license somewhere, but this stops it displaying a warning, so good enough
		$(`${containerClass}.js-main-image`).data('slide', 0).click(function(event)
		{
			event.preventDefault();

			let lightGalleryWrapper = this.parentElement;

			// define lighGalleryInstance so it can be safely referenced in the event listener
			let lightGalleryInstance;

			// lightGallery event listeners have to be defined on the element before initilization
			// @see https://www.lightgalleryjs.com/docs/events/
			lightGalleryWrapper.addEventListener('lgAfterClose', (event) => {
				// if we don't get rid of the lightGallery and behaviours on close clicking on a thumbnail will open the gallery again instead of / in addition to the above actions
				lightGalleryInstance.destroy(true);
			});

			// now initialize lightGallery and assign it to the variable
			if($smallImages.length)
			{
				lightGalleryInstance = lightGallery(lightGalleryWrapper, {selector: '.js-small-image', closeable: true, controls:true, enableTouch:true, plugins: [lgThumbnail], thumbnail: true, exThumbImage: 'data-thumb', licenseKey: licenceKey});
			}
			else
			{
				lightGalleryInstance = lightGallery(lightGalleryWrapper, {selector: '.js-main-image', closeable: true, controls:true, enableTouch:true, licenseKey: licenceKey});
			}

			// and open
			lightGalleryInstance.openGallery($(this).data('slide'));
		});

		if($smallImages.length)
		{
			// add click action to thumbnails
			$smallImages.click(function(event)
			{
				event.preventDefault();
				let origin = event.target;
				let $source = $(origin).closest('.js-small-image');

				// san check
				if(!$source.length === 0)
				{
					console.warn('setupCatalogueItemGallery() : wrapper with class .js-small-image not found.');
					return;
				}

				// assuming that .js-main-image is a sibling of the .small-images
				let $target = $(this).siblings('.js-main-image');

				// not assuming that .small-image is an immediate child of .small-images eg case of ul.js-small-images > li .js-small-image
				let $indexElement = $source;
				while(!$indexElement.parent().is('.js-small-images'))
				{
					$indexElement = $indexElement.parent();
				}

				// record which image to open the lightGallery to if main-image is clicked
				$target.data('slide', $indexElement.index());

				// replace .main-image content (img) with selected thumbnail
				$target.html($source.html());
			}).find('.js-small-image').first().click();
		}
	}

	/**
	* Submits a form using ajax. To be called as the function for submit()
	* @param $form 	the form to be ajaxed
	*/
	function ajaxForm($form, event)
	{
		if ($form.isValid())
		{
			$.ajax(
			{
				contentType: false,
				data: constructFormData($form),
				processData: false,
				type: "POST",
				url: $form.attr("action") + "?ajaxed=true",

				success: function(data)
				{
					if (data['success'] == true)
					{
						if(data['redirect'] != undefined)
						{
							document.location = data['redirect'];
						}
						else
						{
							$form.html(data['message']);
							// keep the submit bubbling up for eg google tracking which watches document node
							$form.parent().trigger(new $.Event(event))[0].scrollIntoView();;
						}
					}
					else
					{
						if ($form.find('.message').length > 0)
						{
							$form.find('.message').html(data['message'])[0];
						}
						else
						{
							$form.prepend('<p class="message">' + data['message'] + '</p>')[0];
						}

						$form.parent().scrollIntoView();
						setupCaptcha($form);//refresh captcha
					}
				}
			});
		}
	}

	/**
	 * This will grab all the form data, including file uploads which serialize() would miss
	 *
	 * @param {jQuery} $form  The form to get the data of
	 * @return {FormData}  A form data object, containing the data in the form
	 */
	let constructFormData = function($form)
	{
		let formData = new FormData();

		$form.find("input:not([type=radio], [type=checkbox], [type=submit]), input[type=radio]:checked, input[type=checkbox]:checked, select, textarea").each(function()
		{
			if($(this).is("input[type=file]"))
			{
				let $input = $(this);
				let files = $(this).get(0).files;

				if(files && files[0])
				{
					$.each(files, function(index, file)
					{
						formData.append($input.attr("name"), file);
					});
				}
			}
			else
			{
				formData.append($(this).attr("name"), $(this).val());
			}
		});

		return formData;
	};

	/**
	 * Sets up captcha for a form.
	 * Can also be used to refresh the captcha
	 *
	 * @param $form 	The form to set up captcha for
	 */
	function setupCaptcha($form)
	{
		var usingRecaptcha = ($('.g-recaptcha').length > 0);

		// captcha - should only be one of these in each form
		var $input = $form.find('input[name=auth]')
		var $wrapper = $input.closest('.security-wrapper');
		if($wrapper.length > 0)
		{
			if(usingRecaptcha)
			{
				$wrapper.remove();
			}
			else
			{
				// all these operations will happen on multiple items in the collection if necessary
				$wrapper.hide();

				var $hidden = $("<input />",
				{
					type: "hidden",
					name: $input.attr("name")
				});

				$.get("/resources/captcha/CaptchaSecurityImages.php?r=" + Math.random(), function(text)
				{
					$hidden.val(text);
					// this will actually have the effect of leaving only instance in the form
					$wrapper.html($hidden);
				});
			}
		}
	}

	/**
	 * Sets up a single form
	 * @param $form 	form to setup
	 */
	function setupForm($form)
	{
		setupCaptcha($form);

		// should only be at most one of these in each form
		$form.find('.has-toggle [type=password]').each(function()
		{
			var $toggle = $('<button type="button" class="toggle-password">Show</button>')
				.click(function(){
					var $this = $(this);
					$this.siblings('input').togglePassword().focus();
					$this.html($this.text() == 'Show' ? 'Hide' : 'Show');
				});
			$(this).after($toggle);
		});

		$form.find('input[type="file"]').change(function(event)
		{
			const file = event.target.files[0];

			if (file !== undefined)
			{
				$(this).closest(".js-file-wrapper").find('.js-uploaded').html(file.name);
			}
			else
			{
				var $uploaded = $(this).closest(".js-file-wrapper").find('.js-uploaded');
				$uploaded.html($uploaded.data().defaultText);
			}
		});

		if ($form.hasClass("js-ajax-form"))
		{
			$form.submit(function(event)
			{
				event.preventDefault();
				ajaxForm($(this), event);
			});
		}
	}

	/**
	 * set up all forms either within a container or in general
	 *
	 * @param String 	container 	Selector for a container. Only set up forms within this container
	 */
	function setupForms(container)
	{
		// iterate through forms
		var $scope = (container === undefined) ? jQuery('form') : jQuery(container).find('form');

		// iterate through forms
		$scope.each(function()
		{
			setupForm($(this));
		});
	}

	//noinspection JSUnusedGlobalSymbols,JSUnusedLocalSymbols
	/**
	 * output a formatted price, used in shopping carts
	 * calculate_shipping function declared in page to include db-generated values
	 * @todo replace with money.js
	 */
	function currencyFormat(id, val)
	{
		val = parseFloat(val);
		if(val <= 0 || isNaN(val))
		{
			$('#' + id).html('');
		}
		else
		{
			val = Math.floor(val * 100 + 0.50000000001);
			let cents = val % 100;
			if(cents < 10)
			{
				cents = "0" + cents;
			}
			val = Math.floor(val / 100).toString();
			//add thousands separator
			for(let i = 0; i < Math.floor((val.length - (1 + i)) / 3); i++)
			{
				val = val.substring(0, val.length - (4 * i + 3)) + ',' + val.substring(val.length - (4 * i + 3));
			}

			$('#' + id).html('$' + val + '.' + cents);
		}
	}

	// noinspection JSUnusedLocalSymbols
	/**
	 * Smoothly scrolls to a particular element on the page
	 * @param	{jQuery}	$element	A jQuery element to scroll to
	 */
	function scrollTo($element)
	{
		// noinspection JSUnresolvedVariable
		if($element.length > 0)
		{
			const scroll = $element.offset().top;
			const $scrollArea = $("html, body");

			if($scrollArea.css("scroll-behavior") === "smooth")
			{
				// noinspection JSValidateTypes
				$scrollArea.scrollTop(scroll);
			}
			else
			{
				$scrollArea.stop().animate(
				{
					scrollTop: scroll
				}, 1000);
			}
		}
	}

	// Handles switching between the login and register tabs
	//should this be moved to cart.js too?
	$(function()
	{
		let $loginSections = $(".login-group .main-section");
		let $headings = $loginSections.find("h1");

		$loginSections.addClass("activated");
		$headings.attr("tabindex", 1);

		let triggerSection = function($section)
		{
			$loginSections.removeClass("selected");
			$section.addClass("selected");
		};

		$loginSections.click(function()
		{
			triggerSection($(this));
		});

		$loginSections.keydown(function(event)
		{
			// The enter key
			if(event.keyCode === 13)
			{
				triggerSection($(this));
			}
		});
	});

	// Handles updating the price for priced options
	$(function()
	{
		let $price = $(".js-product-price");
		let $adjuster = $(".js-product-price-adjuster");

		$price.attr('role', 'alert');

		$adjuster.find("option").each(function()
		{
			let price = $(this).data("price");
			let label = $(this).text();
			label = label.replace(" - " + price, "");
			$(this).text(label);
		});

		$adjuster.change(function()
		{
			$price.text($(this).find("option:selected").data("price"));
		});
	});
})(jQuery);

$(function()
{
	$('.js-open-search').click(function(event)
	{
		$('.js-search').toggleClass('open');
	});
});