var ShopgateMobileHeaderLt = function() {
	
	var headerButton = null,
		scriptParams = null;
	
	/**
	 * Loads settings, sets the cookie and registers the onDomReady-Event
	 */
	this.create = function() {
		scriptParams = {
			// name of the GET parameter that indicates deactivation of the redirect
			backReferenceParam: 'shopgate_redirect',
			
			// name of the cookie that indicates deactivation of the redirect
			cookieName: '{$jsCookieName}',
			
			// time (in days) after which the cookie expires
			cookieLife: parseInt('{$jsCookieLife}'),
			
			// true <==> mobile header is displayed below page content; false <==> mobile header is displayed above page content
			displayBelowContent: ('{$jsDisplayBelowContent}' == 'false') ? false : true,
			
			// html id of the mobile header container
			mobileHeaderId: '{$jsMobileHeaderId}',
			
			// html id of the button container
			buttonWrapperId: '{$jsButtonWrapperId}',
			
			// url to the image for the "switched on" button
			btnImageSrcOn: '{$jsButtonOnImageSource}',
			
			// url to the image for the "switched off" button
			btnImageSrcOff: '{$jsButtonOffImageSource}',
			
			// name of the class for the "switched on" button
			btnClassNameOn: '{$jsButtonOnCssClass}',
			
			// name of the class for the "switched off" button
			btnClassNameOff: '{$jsButtonOffCssClass}',
			
			// a JQuery style selector for the parent element the header is attached to
			headerParentSelector: '{$jsHeaderParentSelector}',
			
			// description to be displayed to the left of the button
			buttonDescription: '{$jsButtonDescription}'
		};	
		
		// set the cookie
		var expiryDate = new Date();
		expiryDate.setTime(expiryDate.getTime() + (scriptParams['cookieLife'] * 24 * 60 * 60 * 1000));
		document.cookie = scriptParams['cookieName'] + '=1; expires=' + expiryDate.toGMTString();
				
		// create new DOM event (workaround for IE) and register event handler
		addDomReadyEvent();
		window.onDomReady(initShopgateMobileHeader);
	};
	
	/**
	 * Places the mobile header into the DOM structure
	 */
	function initShopgateMobileHeader() {
		headerButton = new ShopgateMobileHeaderButton(scriptParams);
		if (scriptParams.displayBelowContent == false) {
			var firstChild = document.querySelector(scriptParams.headerParentSelector).firstChild;
			document.querySelector(scriptParams.headerParentSelector).insertBefore(headerButton.create(), firstChild);
		} else {
			document.querySelector(scriptParams.headerParentSelector).appendChild(headerButton.create());
		}
	}
	
	/**
	 * Creates an object of the GET values
	 * 
	 * @return Object|bool In case of success an object with GET parameters as it's attributes.False otherwise.
	*/
	function decodeSearchPath() {
		var params = {};
	   
		if (location.search.length > 0) {
			var get_param_str = location.search.substring(1, location.search.length),
				get_params = get_param_str.split("&");
			
			for (i = 0; i < get_params.length; i++) {
				var key_value = get_params[i].split("=");
				if(key_value.length == 2) {
					var key = key_value[0],
						value = key_value[1];
					params[key] = value;
				}
			}
	    	
	    	return params;  
	   } else {
			return false;
	   }  
	}		
	/**
	 * The button to activate / deactivate redirection
	 * 
	 * @class
	 * @param Object scriptParams the settings object
	 */
	ShopgateMobileHeaderButton = function(scriptParams) {
		var params = scriptParams;
		
		/**
		 * Puts the button into the wrapping DIV element
		 * 
		 * @returns Object DOM object containing the wrapping panel and the button inside it
		 */
		this.create = function() {
			var textElement = document.createTextNode(params.buttonDescription), wrapper = createButtonWrapper();
			
			wrapper.appendChild(textElement);
			wrapper.appendChild(createButton());
				
			return wrapper;
		}
		
		/**
		 * Creates the button wrapping DIV panel
		 * 
		 * @returns Object DOM object containing the wrapping DIV panel
		 */		
		function createButtonWrapper() {
			var wrapper = document.createElement('div');
			
			wrapper.id = params.buttonWrapperId;
			
			// set styles
			wrapper.style.lineHeight = '30px';
			wrapper.style.fontSize = '30px';
			wrapper.style.fontFamily = '"Helvetica Neue", Helvetica, Arial, sans-serif';
			wrapper.style.fontWeight = 'bold';
			wrapper.style.textAlign = 'left';
			wrapper.style.backgroundColor = '#FFF';
			wrapper.style.border = '1px solid #ADADAD';
			wrapper.style.clear = 'both';
			wrapper.style.color = '#222';
			wrapper.style.margin = '20px auto';
			wrapper.style.marginBottom = '40px';
			wrapper.style.padding = '15px';
			wrapper.style.position = 'relative';
			wrapper.style.width = '560px';
			wrapper.style.borderRadius = '8px';
			wrapper.style.WebkitBorderRadius = '8px';
			wrapper.style.MozBorderRadius = '8px';
			
			return wrapper;
		}
		
		/**
		 * Creates the button
		 * 
		 * @returns Object DOM object containing the button
		 */
		function createButton() {
			var btnImage = document.createElement('img');
			btnImage.src = params.btnImageSrcOff;
			btnImage.className = params.btnClassNameOff;
			
			// set styles
			btnImage.style.background = 'none';
			btnImage.style.margin = '0px';
			btnImage.style.padding = '0px';
			btnImage.style.border = 'medium none';
			btnImage.style.verticalAlign = 'baseline';
			btnImage.style.height = '45px';
			btnImage.style.position = 'absolute';
			btnImage.style.right = '15px';
			btnImage.style.top = '7px';
			
			// create the onclick event and handler for switching from off to on
			btnImage.onclick = function(event) {
				if (this.className == params.btnClassNameOn) {
					this.className = params.btnClassNameOff;
					this.src = params.btnImageSrcOff;	
				} else if (this.className == params.btnClassNameOff) {
					this.className = params.btnClassNameOn;
					this.src = params.btnImageSrcOn;
					
					// button set to on, so we delete the cookie, wait a short time and then call redirect()
					document.cookie = scriptParams['cookieName'] +'=; expires=Thu, 01-01-1970 01:00:00 CET';
					window.setTimeout(function() { redirect(); }, 500);
				}
			}
			
			return btnImage;
		}
	}
	
	/**
	 * Strips the "shopgate_redirect" GET parameter off the URL and
	 * then reloads the page to get the server side redirect done
	 */
	function redirect() {
		var webappUrl = '',
			backLinkRegex = new RegExp('(\\?*||\\&*)(' + scriptParams.backReferenceParam + '=)(1|0)'),
			documentUrl = '',
			queryString = '?',
			ignoredParams = ['webapp_c_name', 'shop_alias', 'host', 'debug', 'debug_platform']; 
		
		documentUrl = document.location.href.replace(backLinkRegex, '');
		
		window.location.replace(documentUrl);
	}
	
	/**
	 * Defines a new onDomReady event as a workaround for problems with IE
	 */		
	function addDomReadyEvent() {
		// create onDomReady Event
		window.onDomReady = initReady;
	
		// Initialize event depending on browser
		function initReady(fn) {
			// W3C-compliant browser
			if (document.addEventListener) {
				document.addEventListener("DOMContentLoaded", fn, false);
			} else { // IE
				document.onreadystatechange = function(){readyState(fn)}
			}
		}
	
		// IE execute function
		function readyState(func) {
			// DOM is ready
			if (document.readyState == "interactive" || document.readyState == "complete") {
				func();
			}
		}
	}
}

try {
	var _shopgate_mobile_header = new ShopgateMobileHeaderLt();
	
	_shopgate_mobile_header.create();
} catch(err){}