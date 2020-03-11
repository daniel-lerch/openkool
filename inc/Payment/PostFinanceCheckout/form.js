(function() {
	// a very simple deferred to cope with ie's lacking Promise support
	function deferred() {
		var awaiters = [];
		var result = null;
		var resolved = false;
		return {
			then:function(cb) {
				if(resolved) {
					if(result === null) {
						cb();
					} else {
						cb(result);
					}
				} else {
					awaiters.push(cb);
				}
			},
			resolve: function(r = null) {
				result = r;
				resolved = true;
				awaiters.forEach(function(cb) {
					if(result === null) {
						cb();
					} else {
						cb(result);
					}
				});
				awaiters = [];
			}
		};
	}

	if(!window.hasOwnProperty('koInitPaymentForm')) {
		window.koInitPaymentForm = {};
	}
	window.koInitPaymentForm.<?= $this->provider->getName() ?> = function(container) {
		var init = deferred();
		var ready = null;
		var sc = document.createElement('script');
		sc.src = '<?= $url ?>';
		sc.addEventListener('load',function() {
			init.resolve();
		});
		container.appendChild(sc);
		var wrap = document.createElement('div');
		wrap.id = 'koPaymentPFCheckoutWrap';
		container.appendChild(wrap);
		var handler = null;
		var validationCallback = null;
		var confirmCallback = null;
		var replaceButtonLabelCallback = null;
		return {
			setPaymentMethod: function(id,then) {
				ready = deferred();
				init.then(function() {
					window.IframeCheckoutHandler.configure('replacePrimaryAction',replaceButtonLabelCallback !== null);
					handler = window.IframeCheckoutHandler(id);
					handler.setValidationCallback(function(result) {
						if(result.success) {
							confirmCallback(function() {
								handler.submit();
							});
						} else if(validationCallback) {
							validationCallback(result.errors);
						}
					});
					handler.setHeightChangeCallback(function(height) {
						console.log(height);
						wrap.querySelector('iframe').height = height;
					});
					handler.setInitializeCallback(function() {
						ready.resolve();
						then();
					});
					wrap.innerHTML = '';
					handler.create('koPaymentPFCheckoutWrap');
				});
			},
			setValidationCallback: function(cb) {
				validationCallback = cb;
				if(handler) {
					handler.setValidationCallback(validationCallback);
				}
			},
			setConfirmCallback: function(cb) {
				confirmCallback = cb;
			},
			setReplaceButtonLabelCallback: function(cb) {
				if(handler) {
					throw 'a replaceButtonLabelCallback must be registered before the call to setPaymentMethod';
				}
				replaceButtonLabelCallback = cb;
			},
			complete: function() {
				if(ready === null) {
					throw 'call setPaymentMethod first';
				}
				if(!confirmCallback) {
					throw 'a confirm callback must be set first';
				}
				ready.then(function() {
					handler.validate();
				});
			},
		};
	}
}());
