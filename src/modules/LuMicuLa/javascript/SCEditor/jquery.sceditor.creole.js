/**
 * SCEditor Creole Plugin
 * http://www.samclarke.com/2011/07/sceditor/ 
 *
 * Copyright (C) 2011-2012, Sam Clarke (samclarke.com)
 *
 * SCEditor is dual licensed under the MIT and GPL licenses:
 *	http://www.opensource.org/licenses/mit-license.php
 *	http://www.gnu.org/licenses/gpl.html
 * 
 * @author Sam Clarke
 * @version 1.3.5
 * @requires jQuery 
 */

// ==ClosureCompiler==
// @output_file_name jquery.sceditor.min.js
// @compilation_level SIMPLE_OPTIMIZATIONS
// ==/ClosureCompiler==

/*jshint smarttabs: true, jquery: true, eqnull:true, curly: false */

(function($) {
	'use strict';
	
	/**
	 * Creole plugin for SCEditor
	 *
	 * @param {Element} el The textarea to be converted
	 * @return {Object} options
	 * @class sceditorCreolePlugin
	 * @name jQuery.sceditorCreolePlugin
	 */
	$.sceditorCreolePlugin = function(element, options) {
		var base = this;

		/**
		 * Private methods
		 * @private
		 */
		var	init,
			buildCreoleCache,
			handleStyles,
			handleTags,
			formatString,
			getStyle,
			wrapInDivs,
			mergeTextModeCommands;

		base.Creoles = $.sceditorCreolePlugin.Creoles;


		/**
		 * cache of all the tags pointing to their Creoles to enable
		 * faster lookup of which Creole a tag should have
		 * @private
		 */
		var tagsToCreoles = {};

		/**
		 * Same as tagsToCreoles but instead of HTML tags it's styles
		 * @private
		 */
		var stylesToCreoles = {};
		
		/**
		 * Allowed children of specific HTML tags. Empty array if no
		 * children other than text nodes are allowed
		 * @private
		 */
		var validChildren = {
			ul: ['li'],
			ol: ['li'],
			table: ['tr'],
			tr: ['td', 'th'],
			code: ['br', 'p', 'div'],
			youtube: []
		};


		/**
		 * Initializer
		 * @private
		 * @name sceditorCreolePlugin.init
		 */
		init = function() {
			$.data(element, "sceditorCreole", base);
			
			base.options = $.extend({}, $.sceditor.defaultOptions, options);

			// build the Creole cache
			buildCreoleCache();

			(new $.sceditor(element,
				$.extend({}, base.options, {
					getHtmlHandler: base.getHtmlHandler,
					getTextHandler: base.getTextHandler,
					commands: mergeTextModeCommands()
				})
			));
		};
		
		mergeTextModeCommands = function() {
			var merge = {
				bold: { txtExec: ["[b]", "[/b]"] },
				italic: { txtExec: ["[i]", "[/i]"] },
				underline: { txtExec: ["[u]", "[/u]"] },
				strike: { txtExec: ["[s]", "[/s]"] },
				subscript: { txtExec: ["[sub]", "[/sub]"] },
				superscript: { txtExec: ["[sup]", "[/sup]"] },
				left: { txtExec: ["[left]", "[/left]"] },
				center: { txtExec: ["[center]", "[/center]"] },
				right: { txtExec: ["[right]", "[/right]"] },
				justify: { txtExec: ["[justify]", "[/justify]"] },
				font: { txtExec: function(caller) {
					var editor = this;
					
					$.sceditor.command.get('font')._createDropDown(
						editor,
						caller,
						function(fontName) {
							editor.insertText("[font="+fontName+"]", "[/font]");
						}
					);
				} },
				size: { txtExec: function(caller) {
					var editor = this;
					
					$.sceditor.command.get('size')._createDropDown(
						editor,
						caller,
						function(fontSize) {
							editor.insertText("[size="+fontSize+"]", "[/size]");
						}
					);
				} },
				color: { txtExec: function(caller) {
					var editor = this;
					
					$.sceditor.command.get('color')._createDropDown(
						editor,
						caller,
						function(color) {
							editor.insertText("[color="+color+"]", "[/color]");
						}
					);
				} },
				bulletlist: { txtExec: ["[ul][li]", "[/li][/ul]"] },
				orderedlist: { txtExec: ["[ol][li]", "[/li][/ol]"] },
				table: { txtExec: ["[table][tr][td]", "[/td][/tr][/table]"] },
				horizontalrule: { txtExec: ["[hr]"] },
				code: { txtExec: ["[code]", "[/code]"] },
				image: { txtExec: function(caller, selected) {
					var url = prompt(this._("Enter the image URL:"), selected);
					
					if(url)
						this.insertText("[img]" + url + "[/img]");
				} },
				email: { txtExec: function(caller, selected) {
					var	email	= prompt(this._("Enter the e-mail address:"), selected || "@"),
						text	= prompt(this._("Enter the displayed text:"), email) || email;
					
					if(email)
						this.insertText("[email=" + email + "]" + text + "[/email]");
				} },
				link: { txtExec: function(caller, selected) {
					var	url	= prompt(this._("Enter URL:"), selected || "http://"),
						text	= prompt(this._("Enter the displayed text:"), url) || url;
					
					if(url)
						this.insertText("[url=" + url + "]" + text + "[/url]");
				} },
				quote: { txtExec: ["[quote]", "[/quote]"] },
				youtube: { txtExec: function(caller, selected) {
					var url = prompt(this._("Enter the YouTube video URL or ID:"), selected);
					
					if(url)
					{
						if(url.indexOf("://") > -1)
							url = url.replace(/^[^v]+v.(.{11}).*/,"$1");
						
						this.insertText("[youtube]" + url + "[/youtube]");
					}
				} },
				rtl: { txtExec: ["[rtl]", "[/rtl]"] },
				ltr: { txtExec: ["[ltr]", "[/ltr]"] }
			};

			return $.extend(true, {}, merge, $.sceditor.commands);
		};
		
		/**
		 * Populates tagsToCreoles and stylesToCreoles to enable faster lookups
		 * 
		 * @private
		 */
		buildCreoleCache = function() {
			$.each(base.Creoles, function(Creole, info) {
				if(typeof base.Creoles[Creole].tags !== "undefined")
					$.each(base.Creoles[Creole].tags, function(tag, values) {
						var isBlock = !!base.Creoles[Creole].isBlock;
						tagsToCreoles[tag] = (tagsToCreoles[tag] || {});
						tagsToCreoles[tag][isBlock] = (tagsToCreoles[tag][isBlock] || {});
						tagsToCreoles[tag][isBlock][Creole] = values;
					});

				if(typeof base.Creoles[Creole].styles !== "undefined")
					$.each(base.Creoles[Creole].styles, function(style, values) {
						var isBlock = !!base.Creoles[Creole].isBlock;
						stylesToCreoles[isBlock] = (stylesToCreoles[isBlock] || {});
						stylesToCreoles[isBlock][style] = (stylesToCreoles[isBlock][style] || {});
						stylesToCreoles[isBlock][style][Creole] = values;
					});
			});
		};
		
		getStyle = function(element, property) {
			var	name = $.camelCase(property),
				$elm, ret, dir;

			// add exception for align
			if("text-align" === property)
			{
				$elm = $(element);
				
				if($elm.parent().css(property) !== $elm.css(property) &&
					$elm.css('display') === "block" && !$elm.is('hr') && !$elm.is('th'))
					ret = $elm.css(property);
				
				// IE changes text-align to the same as direction so skip unless overried by user
				dir = element.style['direction'];
				if(dir && ((/right/i.test(ret) && dir === 'rtl') || (/left/i.test(ret) && dir === 'ltr')))
					return null;
				
				return ret;
			}
			
			if(element.style)
				return element.style[name];
			
			return null;
		};

		/**
		 * Checks if any Creole styles match the elements styles
		 * 
		 * @private
		 * @return string Content with any matching Creole tags wrapped around it.
		 * @Private
		 */
		handleStyles = function(element, content, blockLevel) {
			var	elementPropVal,
				tag = element[0].nodeName.toLowerCase();

			// convert blockLevel to boolean
			blockLevel = !!blockLevel;
			
			if(!stylesToCreoles[blockLevel])
				return content;
			
			$.each(stylesToCreoles[blockLevel], function(property, Creoles) {
				elementPropVal = getStyle(element[0], property);
				if(elementPropVal == null || elementPropVal === "")
					return;

				// if the parent has the same style use that instead of this one
				// so you dont end up with [i]parent[i]child[/i][/i]
				if(getStyle(element.parent()[0], property) === elementPropVal)
					return;

				$.each(Creoles, function(Creole, values) {
					if((element[0].childNodes.length === 0 || element[0].childNodes[0].nodeName.toLowerCase() === "br") &&
						!base.Creoles[Creole].allowsEmpty)
						return;
					
					if(values === null || $.inArray(elementPropVal.toString(), values) > -1) {
						if($.isFunction(base.Creoles[Creole].format))
							content = base.Creoles[Creole].format.call(base, element, content);
						else
							content = formatString(base.Creoles[Creole].format, content);
					}
				});
			});

			return content;
		};

		/**
		 * Handles a HTML tag and finds any matching Creoles
		 * 
		 * @private
		 * @param	jQuery element	element		The element to convert
		 * @param	string			content		The Tags text content
		 * @param	bool			blockLevel	If to convert block level tags
		 * @return	string	Content with any matching Creole tags wrapped around it.
		 * @Private
		 */
		handleTags = function(element, content, blockLevel) {
			var tag = element[0].nodeName.toLowerCase();
			
			// convert blockLevel to boolean
			blockLevel = !!blockLevel;

			if(tagsToCreoles[tag] && tagsToCreoles[tag][blockLevel]) {
				// loop all Creoles for this tag
				$.each(tagsToCreoles[tag][blockLevel], function(Creole, CreoleAttribs) {
					if(!base.Creoles[Creole].allowsEmpty &&
						(element[0].childNodes.length === 0 || (element[0].childNodes[0].nodeName.toLowerCase() === "br" && element[0].childNodes.length === 1))						)
						return;
					
					// if the Creole requires any attributes then check this has
					// all needed
					if(CreoleAttribs !== null) {
						var runCreole = false;

						// loop all the Creole attribs
						$.each(CreoleAttribs, function(attrib, values)
						{
							// check if has the Creoles attrib
							if(element.attr(attrib) == null)
								return;

							// if the element has the Creoles attribute and the Creole attribute
							// has values check one of the values matches
							if(values !== null && $.inArray(element.attr(attrib), values) < 0)
								return;

							// break this loop as we have matched this Creole
							runCreole = true;
							return false;
						});

						if(!runCreole)
							return;
					}

					if($.isFunction(base.Creoles[Creole].format))
						content = base.Creoles[Creole].format.call(base, element, content);
					else
						content = formatString(base.Creoles[Creole].format, content);
				});
			}
			
			// add newline after paragraph elements p and div (WebKit uses divs) and br tags
			if(blockLevel && /^(br|div|p)$/.test(tag))
			{
				var parentChildren = element[0].parentNode.childNodes;

				// if it's a <p><br /></p> the paragraph will put the newline so skip the br
				if(!("br" === tag && parentChildren.length === 1) &&
					!("br" === tag && parentChildren[parentChildren.length-1] === element[0])) {
					content += "\n";
				}

				// needed for browsers that enter textnode then when return is pressed put the rest in a div, i.e.:
				// text<div>line 2</div>
				if("br" !== tag && !$.sceditor.dom.isInline(element.parent()[0]) && element[0].previousSibling &&
					element[0].previousSibling.nodeType === 3) {
					content = "\n" + content;
				}
			}

			return content;
		};

		/**
		 * Formats a string in the format
		 * {0}, {1}, {2}, ect. with the params provided
		 * @private
		 * @return string
		 * @Private
		 */
		formatString = function() {
			var args = arguments;
			return args[0].replace(/\{(\d+)\}/g, function(str, p1) {
				return typeof args[p1-0+1] !== "undefined"? 
						args[p1-0+1] :
						'{' + p1 + '}';
			});
		};

		/**
		 * Removes any leading or trailing quotes ('")
		 *
		 * @return string
		 * @memberOf jQuery.sceditorCreolePlugin.prototype
		 */
		base.stripQuotes = function(str) {
			return str.replace(/^["']+/, "").replace(/["']+$/, "");
		};

		/**
		 * Converts HTML to Creole
		 * @param string	html	Html string, this function ignores this, it works off domBody
		 * @param HtmlElement	domBody	Editors dom body object to convert
		 * @return string Creole which has been converted from HTML 
		 * @memberOf jQuery.sceditorCreolePlugin.prototype
		 */
		base.getHtmlHandler = function(html, domBody) {
			$.sceditor.dom.removeWhiteSpace(domBody[0]);

			return $.trim(base.elementToCreole(domBody));
		};

		/**
		 * Converts a HTML dom element to Creole starting from
		 * the innermost element and working backwards
		 * 
		 * @private
		 * @param HtmlElement	element		The element to convert to Creole
		 * @param array			vChildren	Valid child tags allowed
		 * @return string Creole
		 * @memberOf jQuery.sceditorCreolePlugin.prototype
		 */
		base.elementToCreole = function($element) {
			return (function toCreole(node, vChildren) {
				var ret = '';

				$.sceditor.dom.traverse(node, function(node) {
					var	$node		= $(node),
						curTag		= '',
						tag		= node.nodeName.toLowerCase(),
						vChild		= validChildren[tag],
						isValidChild	= true;
					
					if(typeof vChildren === 'object')
					{
						isValidChild = $.inArray(tag, vChildren) > -1;

						// if this tag is one of the parents allowed children
						// then set this tags allowed children to whatever it allows,
						// otherwise set to what the parent allows
						if(!isValidChild)
							vChild = vChildren;
					}
					
					// 3 is text element
					if(node.nodeType !== 3)
					{
						// skip ignored elments
						if($node.hasClass("sceditor-ignore"))
							return;

						// don't loop inside iframes
						if(tag !== 'iframe')
							curTag = toCreole(node, vChild);
						
						if(isValidChild)
						{
							// code tags should skip most styles
							if(!$node.is('code'))
							{
								// handle inline Creoles
								curTag = handleStyles($node, curTag);
								curTag = handleTags($node, curTag);
								
								// handle blocklevel Creoles
								curTag = handleStyles($node, curTag, true);
							}
							
							ret += handleTags($node, curTag, true);
						}
						else
							ret += curTag;
					}
					else if(node.wholeText && (!node.previousSibling || node.previousSibling.nodeType !== 3))
					{
						if($(node).parents('code').length === 0)
							ret += node.wholeText.replace(/ +/g, " ");
						else
							ret += node.wholeText;
					}
					else if(!node.wholeText)
						ret += node.nodeValue;
				}, false, true);
				
				return ret;
			}($element.get(0)));
		};

		/**
		 * Converts Creole to HTML
		 * 
		 * @param {String} text
		 * @param {Bool} isFragment
		 * @return {String} HTML
		 * @memberOf jQuery.sceditorCreolePlugin.prototype
		 */
		base.getTextHandler = function(text, isFragment) {

			var	oldText, replaceCreoleFunc,
				CreoleRegex = /\[([^\[\s=]+)(?:([^\[]+))?\]((?:[\s\S](?!\[\1))*?)\[\/(\1)\]/g,
				atribsRegex = /(\S+)=((?:(?:(["'])(?:\\\3|[^\3])*?\3))|(?:[^'"\s]+))/g;

			replaceCreoleFunc = function(str, Creole, attrs, content)
			{
				var	attrsMap = {},
					matches;
				
				Creole = Creole.toLowerCase();
				
				if(attrs)
				{
					attrs = $.trim(attrs);
					
					// if only one attribute then remove the = from the start and strip any quotes
					if((attrs.charAt(0) === "=" && (attrs.split("=").length - 1) <= 1) || Creole === 'url')
						attrsMap.defaultattr = base.stripQuotes(attrs.substr(1));
					else
					{
						if(attrs.charAt(0) === "=")
							attrs = "defaultattr" + attrs;

						while((matches = atribsRegex.exec(attrs)))
							attrsMap[matches[1].toLowerCase()] = base.stripQuotes(matches[2]);
					}
				}

				if(!base.Creoles[Creole])
					return str;

				if($.isFunction(base.Creoles[Creole].html))
					return base.Creoles[Creole].html.call(base, Creole, attrsMap, content);
				else
					return formatString(base.Creoles[Creole].html, content);
			};

			text = text.replace(/&/g, "&amp;")
					.replace(/</g, "&lt;")
					.replace(/>/g, "&gt;")
					.replace(/\r/g, "")
					.replace(/(\[\/?(?:left|center|right|justify|align|rtl|ltr)\])\n/g, "$1")
					.replace(/\n/g, "<br />");

			while(text !== oldText)
			{
				oldText = text;
				text    = text.replace(CreoleRegex, replaceCreoleFunc);
			}

			// As hr is the only Creole not to have a start and end tag it's
			// just being replace here instead of adding support for it above.
			text = text.replace(/\[hr\]/gi, "<hr>")
					.replace(/\[\*\]/gi, "<li>");

			// replace multi-spaces which are not inside tags with a non-breaking space
			// to preserve them. Otherwise they will just be converted to 1!
			text = text.replace(/ {2}(?=([^<\>]*?<|[^<\>]*?$))/g, " &nbsp;");
			
			return wrapInDivs(text, isFragment);
		};
		
		/**
		 * Wraps divs around inline HTML. Needed for IE
		 * 
		 * @param string html
		 * @return string HTML
		 * @private
		 */
		wrapInDivs = function(html, excludeFirstLast)
		{
			var	d		= document,
				inlineFrag	= d.createDocumentFragment(),
				outputDiv	= d.createElement('div'),
				tmpDiv		= d.createElement('div'),
				div, node, next, nodeName;
			
			$(tmpDiv).hide().appendTo(d.body);
			tmpDiv.innerHTML = html;
			
			node = tmpDiv.firstChild;
			while(node)
			{
				next = node.nextSibling;
				nodeName = node.nodeName.toLowerCase();

				if((node.nodeType === 1 && !$.sceditor.dom.isInline(node)) || nodeName === "br")
				{
					if(inlineFrag.childNodes.length > 0 || nodeName === "br")
					{
						div = d.createElement('div');
						div.appendChild(inlineFrag);
						
						// Putting BR in a div in IE9 causes it to do a double line break,
						// as much as I hate browser UA sniffing, to do feature detection would
						// be more code than it's worth for this specific bug.
						if(nodeName === "br" && (!$.sceditor.ie || $.sceditor.ie < 9))
							div.appendChild(d.createElement('br'));
						
						outputDiv.appendChild(div);
						inlineFrag = d.createDocumentFragment();
					}
					
					if(nodeName !== "br")
						outputDiv.appendChild(node);
				}
				else
					inlineFrag.appendChild(node);
					
				node = next;
			}
			
			if(inlineFrag.childNodes.length > 0)
			{
				div = d.createElement('div');
				div.appendChild(inlineFrag);
				outputDiv.appendChild(div);
			}
			
			// needed for paste, the first shouldn't be wrapped in a div
			if(excludeFirstLast)
			{
				node = outputDiv.firstChild;
				if(node && node.nodeName.toLowerCase() === "div")
				{
					while((next = node.firstChild))
						outputDiv.insertBefore(next, node);
					
					if($.sceditor.ie >= 9)
						outputDiv.insertBefore(d.createElement('br'), node);
					
					outputDiv.removeChild(node);
				}
				
				node = outputDiv.lastChild;
				if(node && node.nodeName.toLowerCase() === "div")
				{
					while((next = node.firstChild))
						outputDiv.insertBefore(next, node);

					if($.sceditor.ie >= 9)
						outputDiv.insertBefore(d.createElement('br'), node);
					
					outputDiv.removeChild(node);
				}
			}

			$(tmpDiv).remove();
			return outputDiv.innerHTML;
		};

		init();
	};
	
	$.sceditorCreolePlugin.Creoles = {
		// START_COMMAND: Bold
		b: {
			tags: {
				b: null,
				strong: null
			},
			styles: {
				// 401 is for FF 3.5
				"font-weight": ["bold", "bolder", "401", "700", "800", "900"]
			},
			format: "[b]{0}[/b]",
			html: '<strong>{0}</strong>'
		},
		// END_COMMAND

		// START_COMMAND: Italic
		i: {
			tags: {
				i: null,
				em: null
			},
			styles: {
				"font-style": ["italic", "oblique"]
			},
			format: "[i]{0}[/i]",
			html: '<em>{0}</em>'
		},
		// END_COMMAND

		// START_COMMAND: Underline
		u: {
			tags: {
				u: null
			},
			styles: {
				"text-decoration": ["underline"]
			},
			format: "[u]{0}[/u]",
			html: '<u>{0}</u>'
		},
		// END_COMMAND

		// START_COMMAND: Strikethrough
		s: {
			tags: {
				s: null,
				strike: null
			},
			styles: {
				"text-decoration": ["line-through"]
			},
			format: "[s]{0}[/s]",
			html: '<s>{0}</s>'
		},
		// END_COMMAND

		// START_COMMAND: Subscript
		sub: {
			tags: {
				sub: null
			},
			format: "[sub]{0}[/sub]",
			html: '<sub>{0}</sub>'
		},
		// END_COMMAND

		// START_COMMAND: Superscript
		sup: {
			tags: {
				sup: null
			},
			format: "[sup]{0}[/sup]",
			html: '<sup>{0}</sup>'
		},
		// END_COMMAND

		// START_COMMAND: Font
		font: {
			tags: {
				font: {
					face: null
				}
			},
			styles: {
				"font-family": null
			},
			format: function(element, content) {
				if(element[0].nodeName.toLowerCase() === "font" && element.attr('face'))
					return '[font=' + this.stripQuotes(element.attr('face')) + ']' + content + '[/font]';

				return '[font=' + this.stripQuotes(element.css('font-family')) + ']' + content + '[/font]';
			},
			html: function(element, attrs, content) {
				return '<font face="' + attrs.defaultattr + '">' + content + '</font>';
			}
		},
		// END_COMMAND

		// START_COMMAND: Size
		size: {
			tags: {
				font: {
					size: null
				}
			},
			styles: {
				"font-size": null
			},
			format: function(element, content) {
				var	fontSize = element.css('fontSize'),
					size     = 1;

				// Most browsers return px value but IE returns 1-7
				if(fontSize.indexOf("px") > -1) {
					// convert size to an int
					fontSize = fontSize.replace("px", "") - 0;

					if(fontSize > 12)
						size = 2;
					if(fontSize > 15)
						size = 3;
					if(fontSize > 17)
						size = 4;
					if(fontSize > 23)
						size = 5;
					if(fontSize > 31)
						size = 6;
					if(fontSize > 47)
						size = 7;
				}
				else
					size = fontSize;

				return '[size=' + size + ']' + content + '[/size]';
			},
			html: function(element, attrs, content) {
				return '<font size="' + attrs.defaultattr + '">' + content + '</font>';
			}
		},
		// END_COMMAND

		// START_COMMAND: Color
		color: {
			tags: {
				font: {
					color: null
				}
			},
			styles: {
				color: null
			},
			format: function(element, content) {
				/**
				 * Converts CSS rgb value into hex
				 * @private
				 * @return string Hex color
				 */
				var rgbToHex = function(rgbStr) {
					var m;
		
					function toHex(n) {
						n = parseInt(n,10);
						if(isNaN(n))
							return "00";
						n = Math.max(0,Math.min(n,255)).toString(16);
		
						return n.length<2 ? '0'+n : n;
					}
		
					// rgb(n,n,n);
					if((m = rgbStr.match(/rgb\((\d+),\s*?(\d+),\s*?(\d+)\)/i)))
						return '#' + toHex(m[1]) + toHex(m[2]-0) + toHex(m[3]-0);
		
					// expand shorthand
					if((m = rgbStr.match(/#([0-f])([0-f])([0-f])\s*?$/i)))
						return '#' + m[1] + m[1] + m[2] + m[2] + m[3] + m[3];
					
					return rgbStr;
				};
		
				var color = element.css('color');

				if(element[0].nodeName.toLowerCase() === "font" && element.attr('color'))
					color = element.attr('color');
				
				color = rgbToHex(color);

				return '[color=' + color + ']' + content + '[/color]';
			},
			html: function(element, attrs, content) {
				return '<font color="' + attrs.defaultattr + '">' + content + '</font>';
			}
		},
		// END_COMMAND

		// START_COMMAND: Lists
		ul: {
			tags: {
				ul: null
			},
			isBlock: true,
			format: "[ul]{0}[/ul]",
			html: '<ul>{0}</ul>'
		},
		list: {
			html: '<ul>{0}</ul>'
		},
		ol: {
			tags: {
				ol: null
			},
			isBlock: true,
			format: "[ol]{0}[/ol]",
			html: '<ol>{0}</ol>'
		},
		li: {
			tags: {
				li: null
			},
			format: "[li]{0}[/li]",
			html: '<li>{0}</li>'
		},
		"*": {
			html: '<li>{0}</li>'
		},
		// END_COMMAND

		// START_COMMAND: Table
		table: {
			tags: {
				table: null
			},
			format: "[table]{0}[/table]",
			html: '<table>{0}</table>'
		},
		tr: {
			tags: {
				tr: null
			},
			format: "[tr]{0}[/tr]",
			html: '<tr>{0}</tr>'
		},
		th: {
			tags: {
				th: null
			},
			isBlock: true,
			format: "[th]{0}[/th]",
			html: '<th>{0}</th>'
		},
		td: {
			tags: {
				td: null
			},
			isBlock: true,
			format: "[td]{0}[/td]",
			html: '<td>{0}<br class="sceditor-ignore" /></td>'
		},
		// END_COMMAND

		// START_COMMAND: Emoticons
		emoticon: {
			allowsEmpty: true,
			tags: {
				img: {
					src: null,
					"data-sceditor-emoticon": null
				}
			},
			format: function(element, content) {
				return element.attr('data-sceditor-emoticon') + content;
			},
			html: '{0}'
		},
		// END_COMMAND

		// START_COMMAND: Horizontal Rule
		horizontalrule: {
			allowsEmpty: true,
			tags: {
				hr: null
			},
			format: "[hr]{0}",
			html: "<hr />"
		},
		// END_COMMAND

		// START_COMMAND: Image
		img: {
			allowsEmpty: true,
			tags: {
				img: {
					src: null
				}
			},
			format: function(element, content) {
				// check if this is an emoticon image
				if(typeof element.attr('data-sceditor-emoticon') !== "undefined")
					return content;

				var attribs = "=" + $(element).width() + "x" + $(element).height();

				return '[img' + attribs + ']' + element.attr('src') + '[/img]';
			},
			html: function(element, attrs, content) {
				var attribs = "", parts;

				// handle [img width=340 height=240]url[/img]
				if(typeof attrs.width !== "undefined")
					attribs += ' width="' + attrs.width + '"';
				if(typeof attrs.height !== "undefined")
					attribs += ' height="' + attrs.height + '"';

				// handle [img=340x240]url[/img]
				if(typeof attrs.defaultattr !== "undefined") {
					parts = attrs.defaultattr.split(/x/i);

					attribs = ' width="' + parts[0] + '"' +
						' height="' + (parts.length === 2 ? parts[1] : parts[0]) + '"';
				}

				return '<img ' + attribs + ' src="' + content + '" />';
			}
		},
		// END_COMMAND

		// START_COMMAND: URL
		url: {
			allowsEmpty: true,
			tags: {
				a: {
					href: null
				}
			},
			format: function(element, content) {
				// make sure this link is not an e-mail, if it is return e-mail Creole
				if(element.attr('href').substr(0, 7) === 'mailto:')
					return '[email=' + element.attr('href').substr(7) + ']' + content + '[/email]';

				return '[url=' + decodeURI(element.attr('href')) + ']' + content + '[/url]';
			},
			html: function(element, attrs, content) {
				if(typeof attrs.defaultattr === "undefined" || attrs.defaultattr.length === 0)
					attrs.defaultattr = content;

				return '<a href="' + encodeURI(attrs.defaultattr) + '">' + content + '</a>';
			}
		},
		// END_COMMAND

		// START_COMMAND: E-mail
		email: {
			html: function(element, attrs, content) {
				if(typeof attrs.defaultattr === "undefined")
					attrs.defaultattr = content;

				return '<a href="mailto:' + attrs.defaultattr + '">' + content + '</a>';
			}
		},
		// END_COMMAND

		// START_COMMAND: Quote
		quote: {
			tags: {
				blockquote: null
			},
			isBlock: true,
			format: function(element, content) {
				var	author,
					attr = '',
					$elm = $(element);
		
				if($elm.children("cite:first").length === 1 || $elm.data("author")) {
					author = $(element).children("cite:first").text() || $elm.data("author");
		
					
					$elm.data("author", author)
					$(element).children("cite:first").remove();
		
					content	= '';
					content	= this.elementToCreole($(element));
					attr	= '=' + author;
				}

				return '[quote' + attr + ']' + content + '[/quote]';
			},
			html: function(element, attrs, content) {
				if(typeof attrs.defaultattr !== "undefined")
					content = '<cite>' + attrs.defaultattr + '</cite>' + content;

				return '<blockquote>' + content + '</blockquote>';
			}
		},
		// END_COMMAND

		// START_COMMAND: Code
		code: {
			tags: {
				code: null
			},
			isBlock: true,
			format: "[code]{0}[/code]",
			html: '<code>{0}</code>'
		},
		// END_COMMAND


		// START_COMMAND: Left
		left: {
			styles: {
				"text-align": ["left", "-webkit-left", "-moz-left", "-khtml-left"]
			},
			isBlock: true,
			format: "[left]{0}[/left]",
			html: '<div align="left">{0}</div>'
		},
		// END_COMMAND

		// START_COMMAND: Centre
		center: {
			styles: {
				"text-align": ["center", "-webkit-center", "-moz-center", "-khtml-center"]
			},
			isBlock: true,
			format: "[center]{0}[/center]",
			html: '<div align="center">{0}</div>'
		},
		// END_COMMAND

		// START_COMMAND: Right
		right: {
			styles: {
				"text-align": ["right", "-webkit-right", "-moz-right", "-khtml-right"]
			},
			isBlock: true,
			format: "[right]{0}[/right]",
			html: '<div align="right">{0}</div>'
		},
		// END_COMMAND

		// START_COMMAND: Justify
		justify: {
			styles: {
				"text-align": ["justify", "-webkit-justify", "-moz-justify", "-khtml-justify"]
			},
			isBlock: true,
			format: "[justify]{0}[/justify]",
			html: '<div align="justify">{0}</div>'
		},
		// END_COMMAND

		// START_COMMAND: YouTube
		youtube: {
			allowsEmpty: true,
			tags: {
				iframe: {
					'data-youtube-id': null
				}
			},
			format: function(element, content) {
				if(!element.attr('data-youtube-id'))
					return content;

				return '[youtube]' + element.attr('data-youtube-id') + '[/youtube]';
			},
			html: '<iframe width="560" height="315" src="http://www.youtube.com/embed/{0}?wmode=opaque' +
				'" data-youtube-id="{0}" frameborder="0" allowfullscreen></iframe>'
		},
		// END_COMMAND
		
		
		// START_COMMAND: Rtl
		rtl: {
			styles: {
				"direction": ["rtl"]
			},
			format: "[rtl]{0}[/rtl]",
			html: '<div style="direction: rtl">{0}</div>'
		},
		// END_COMMAND
		
		// START_COMMAND: Ltr
		ltr: {
			styles: {
				"direction": ["ltr"]
			},
			format: "[ltr]{0}[/ltr]",
			html: '<div style="direction: ltr">{0}</div>'
		},
		// END_COMMAND
		
		
		// this is here so that commands above can be removed
		// without having to remove the , after the last one.
		// Needed for IE.
		ignore: {}
	};
	
	/**
	 * Static Creole helper class
	 * @class command
	 * @name jQuery.sceditorCreolePlugin.Creole
	 */
	$.sceditorCreolePlugin.Creole = 
	/** @lends jQuery.sceditorCreolePlugin.Creole */
	{
		/**
		 * Gets a Creole
		 * 
		 * @param {String} name
		 * @return {Object|null}
		 * @since v1.3.5
		 */
		get: function(name) {
			return $.sceditorCreolePlugin.Creoles[name] || null;
		},
		
		/**
		 * <p>Adds a Creole to the parser or updates an exisiting
		 * Creole if a Creole with the specified name already exists.</p>
		 * 
		 * @param {String} name
		 * @param {Object} Creole
		 * @return {this|false} Returns false if name or Creole is false
		 * @since v1.3.5
		 */
		set: function(name, Creole) {
			if(!name || !Creole)
				return false;
			
			// merge any existing command properties
			Creole = $.extend($.sceditorCreolePlugin.Creoles[name] || {}, Creole);
		
			Creole.remove = function() { $.sceditorCreolePlugin.Creole.remove(name); };
			
			$.sceditorCreolePlugin.Creoles[name] = Creole;
			return this;
		},
		
		/**
		 * Removes a Creole
		 * 
		 * @param {String} name
		 * @return {this}
		 * @since v1.3.5
		 */
		remove: function(name) {
			if($.sceditorCreolePlugin.Creoles[name])
				delete $.sceditorCreolePlugin.Creoles[name];
			
			return this;
		}
	};
	
	/**
	 * Checks if a command with the specified name exists
	 * 
	 * @param string name
	 * @return bool
	 * @deprecated Since v1.3.5
	 * @memberOf jQuery.sceditorCreolePlugin
	 */
	$.sceditorCreolePlugin.commandExists = function(name) {
		return !!$.sceditorCreolePlugin.Creole.get(name);
	};
	
	/**
	 * Adds/updates a Creole.
	 * 
	 * @param String		name		The Creole name
	 * @param Object		tags		Any html tags this Creole applies to, i.e. strong for [b]
	 * @param Object		styles		Any style properties this applies to, i.e. font-weight for [b]
	 * @param String|Function	format		Function or string to convert the element into Creole
	 * @param String|Function	html		String or function to format the Creole back into HTML.
	 * @param bool			allowsEmpty	If this Creoles is allowed to be empty, e.g. [b][/b]
	 * @return Bool
	 * @deprecated Since v1.3.5
	 * @memberOf jQuery.sceditorCreolePlugin
	 */
	$.sceditorCreolePlugin.setCommand = function(name, tags, styles, format, html, allowsEmpty, isBlock) {
		return $.sceditorCreolePlugin.Creole.set(name,
		{
			tags: tags || {},
			styles: styles || {},
			allowsEmpty: allowsEmpty,
			isBlock: isBlock,
			format: format,
			html: html
		});
	};

	$.fn.sceditorCreolePlugin = function(options) {
		if((!options || !options.runWithoutWysiwygSupport) && !$.sceditor.isWysiwygSupported())
			return;
		
		return this.each(function() {
			(new $.sceditorCreolePlugin(this, options));
		});
	};
})(jQuery);
