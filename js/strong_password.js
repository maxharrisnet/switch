/**
 * @author Walter Wimberly
 * @version 1.0 RC
 * @classDescription Strong Password JS tool to measure how strong
 * a password is.  It allows you to configure various settings.     
 * This tool calculates how strong a password is while it is being  
 * entered by the user.
 */

/**
 * 
 * @constructor
 * @param {Array}	settings	Associative array of all of the settings 
 * that you can configure.
 * @param {Function}	fn		a function that can be called when the 
 * password is being checked if it is being called at real time - this
 * allows for other sytles to be used if desired.
 */
function StrongPassword(settings, fn) {
	/**
	 * Stores the current strength of the password in a numeric fashion.
	 * @type int
	 */
	this.strength = 0;
	/**
	 * Show the strength of the password (as a number) be shown while is it calculated.
	 * @type boolean
	 */
	this.showStrength = false;
	/**
	 * Should numbers be allowed within the password.
	 * @type boolean
	 */
	this.allowNumbers = settings && settings.allowNumbers ? settings.allowNumbers : true;
	/**
	 * Should special characters (!@#$%^&*()-+_<>?/;[]{}) be allowed 
	 * within the password.
	 * @type boolean
	 */
	this.allowSpecialChars = settings && settings.allowSpecialChars ? settings.allowSpecialChars : false;
	/**
	 * The minimum "strength" score that is allowed for processing.
	 * @type int
	 */
	this.minAllowScore = settings && settings.minAllowScore ? settings.minAllowScore : 5;
	/**
	 * The minimum "strength" score that is allowed for a "Weak" setting.
	 * Very similar to the minAllowScore
	 * @type int
	 */
	this.minWeakScore = settings && settings.minWeakScore ? settings.minWeakScore : 10;
	/**
	 * The minimum "strength" score that is allowed for an "Intermediate"  
	 * level strength password.
	 * @type int
	 */
	this.minIntermediateScore = settings && settings.minIntermediateScore ? settings.minIntermediateScore : 15;
	/**
	 * The minimum "strength" score that is allowed for a "Strong" level 
	 * strength password.
	 * @type int
	 */
	this.minStrongScore = settings && settings.scoreTextNotAcceptable ? settings.scoreTextNotAcceptable : 22;
	/**
	 * What text is displayed if the password is too weak to be allowed.
	 * This can be any valid HTML string.
	 * @type string
	 */
	this.scoreTextNotAcceptable = settings && settings.minAllowScore ? settings.minAllowScore : 'Unacceptable!';
	/**
	 * What text is displayed if the password is of weak strength.
	 * This can be any valid HTML string.
	 * @type string
	 */
	this.scoreTextWeak = settings && settings.scoreTextWeak ? settings.scoreTextWeak : '<em>Weak Password</em>';
	/**
	 * What text is displayed if the password is of intermediate strength.
	 * This can be any valid HTML string.
	 * @type string
	 */
	this.scoreTextIntermediate = settings && settings.scoreTextIntermediate ? settings.scoreTextIntermediate : '<em>Just</em> Strong Enough Password';
	/**
	 * What text is displayed if the password is of strong strength.
	 * This can be any valid HTML string.
	 * @type string
	 */
	this.scoreTextStrong = settings && settings.scoreTextStrong ? settings.scoreTextStrong : '<strong>Strong</strong> Passsword';
	/**
	 * What is the URL for the file to look up against standard passwords.
	 * Default value is blank.  If the field is blank, then it will skip
	 * this step.  
	 * @type string
	 */
	this.dictionaryLookupFile = settings && settings.dictionary ? settings.dictionary : '';
	/**
	 * The id value of the password field to check.  If it is not passed, 
	 * a default value of password will be used.
	 * @type string
	 */
	this.src = settings && settings.source ? settings.source : 'password';
	/**
	 * This private variable is used to store the created DOM element which
	 * shows the value of the strength of the password.
	 * @private	
	 * @type string
	 */
	this.target = this.src + '_target';
	/**
	 * If true (default) then it will check the password as it is being typed.
	 * @type boolean
	 */
	this.realTimeCheck = settings && settings.realTimeCheck ? settings.realTimeCheck : true;
	
	
	// avoide confusion over this within an event handler
	var wdSPSelf = this;
	
	// initialize the strength counter
	//$('#' + this.src).after('<span id="' + this.target + '" class="wdStrengthHint"></span>');
	if( this.realTimeCheck ) {
		$('#' + this.src).keyup( function() { 
			// reset strength
			wdSPSelf.strength = 0;
			var password = $('#' + wdSPSelf.src).val();
			wdSPSelf.strength += password.length;
			
			// define the regular expression necessary
			var rmatch = '!@#$%^&*()-+_<>?/;[]{}';
	
			for(i = 0, max = password.length - 1; i < max; i++) {
				// take one away if the two characters next to each other are equal
				if(password.charAt(i) == password.charAt(i + 1)) {
					wdSPSelf.strength-=2;
				}
				
				// give points for using capital letters
				if(password.charAt(i) == password.charAt(i).toUpperCase()) {
					wdSPSelf.strength++;
				}
				
				// give points for using numbers
				if(wdSPSelf.allowNumbers) {
					if(password.charAt(i) * 1 == password.charAt(i)) {
						wdSPSelf.strength++;
						
						// check for numbers in a sequence
						tmpNum = password[i] * 1;
						if(tmpNum + 1 == password.charAt(i + 1) || 
							tmpNum - 1 == password.charAt(i + 1)) {
								wdSPSelf.strength-=3;
							}
					}
				}
				
				// give points for using special characters
				if(wdSPSelf.allowSpecialChars) {
					for(j = 0; j < rmatch.length; j++) {
						if(rmatch.charAt(j) == password.charAt(i)  ) {
							wdSPSelf.strength+=3;
						}
					}
				}
			}
			
			// final password strength checks
			
			// revent 'cheating' with only upper case letters
			if(password == password.toUpperCase()) {
				wdSPSelf.strength -= password.length;
			}
			
			// demonstrates mixed case?
			if(password != password.toLowerCase()) {
				wdSPSelf.strength += 5; 
			}
			
			// final error? checks
			// link to server side code and check to see if it is a dictionary 
			// word or a partial dictionary word - fancy checks will even 
			// convert numbers into letters to see if its a simple/conversion
			// check, but still a word...
			if(self.dictionaryLookupFile !== '' && self.dictionaryLookupFile !== undefined) {
				$.getJSON(self.dictionaryLookupFile, {value: password}, 
					function(json) {
						wdSPSelf.strength += json.returnValue;
					});
			}
			
			// remove existing classes
			$('#' + wdSPSelf.target).removeClass($('#' + wdSPSelf.target).attr("class"));
			$('#' + wdSPSelf.target).addClass('wdStrengthHint');

			
			// set the strength values...
			/*cssClass = 'wdTooWeakPassword';
			targetText = wdSPSelf.scoreTextNotAcceptable;
			if(wdSPSelf.strength > wdSPSelf.minStrongScore) {
				cssClass = 'wdStrongPassword';
				targetText = wdSPSelf.scoreTextStrong;
			} else if(wdSPSelf.strength >= wdSPSelf.minIntermediateScore) {
				cssClass = 'wdIntermediatePassword';
				targetText = wdSPSelf.scoreTextIntermediate;
			} else if(wdSPSelf.strength >= wdSPSelf.minWeakScore) {
				cssClass = 'wdWeakPassword';
				targetText = wdSPSelf.scoreTextWeak;
			}
			
			if(wdSPSelf.showStrength) {
				$('#' + wdSPSelf.target).html(wdSPSelf.strength + ' : ' + targetText);
			} else {
				$('#' + wdSPSelf.target).html(targetText);
			}
				
			$('#' + wdSPSelf.target).addClass(cssClass);
			*/
			try {
				fn();
			} catch( e ) {
				// do nothing...user generated error, and we don't need to worry about their mistakes.
			}
		});
	}
}

StrongPassword.prototype = {
	getPasswordStrength: function() {
		return this.strength;
	}
}