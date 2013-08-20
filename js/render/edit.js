$.extend(fastFace.render, {

	_getEdt: function(edtFn, edt, edtType) {
		if(typeof edtFn === 'string') {
			return eval('(function() { return function(args) ' + edtFn + '; }())');
		}
		
		return this[edt] || this[edt+(edtType || 'Edt')] || this[edt+'Edt'] || this.charEdt;
	},

	setEdt: function(args) {
		if(fastFace.tbl.get(args.column.fk.cls).js_def.mod) {
			return new fastFace.edt.setCheckboxEdt(args);
		} else {
			return new fastFace.edt.enumEdt(args);
		}
	},

	enumGrd: function(args) {
		return new fastFace.edt.enumSelectEdt(args);
	},
	
	enumEdt: function(args) {
		if(fastFace.tbl.get(args.column.fk.cls).js_def.mod) {
			return new fastFace.edt.enumRadioEdt(args);
		}
		return new fastFace.edt.enumSelectEdt(args);
	},

	enumSelectEdt: function(args) {
		var $input;
		var defaultValue;
		var scope = this;
		var optHtml = null;
		var isEnum = true;
		var type = args.column.type;
		
		this.init = function() {
			isEnum = args.column.type !== 'set';
			var fk = args.column.fk;
			if(fk.edtFn) {
				optHtml = eval(fk.edtFn);
			} else if(fk.cls) {
				optHtml = fastFace.tbl.get(fk.cls).getOpt(false);
			}
			$input = $('<SELECT id='+fastFace.render.uid('enum_')+( isEnum ? '' : ' multiple="multiple" size='+(($.isArray(args.column.len) && args.column.len[0] < 18) ? args.column.len[0] : 18))+'>'+optHtml+'</SELECT>');
			$input.appendTo(args.container).on('change', function (event) {
				if(isEnum) {
					args.commitChanges();
				}
				event.stopImmediatePropagation();
			}).on('keydown', function (event) {
				if (event.which === $.ui.keyCode.ENTER) {
					args.commitChanges();
				} else if (event.which === $.ui.keyCode.ESCAPE) {
					event.preventDefault();
					args.cancelChanges();
				}
				event.stopImmediatePropagation();
			});
		};

		this.destroy = function() {
			$input.remove();
		};

		this.focus = function() {
			$input.focus();
		};

		this.loadValue = function(item) {
			defaultValue = item[args.column.field];
			$input.val(isEnum ? defaultValue : (defaultValue || '').split(',')).focus();
			defaultValue = type === 'int' ? ~~defaultValue : (type === 'bool' ? $.boolV(defaultValue) : (type === 'decimal' ? parseFloat(defaultValue || 0, 10) || 0 : defaultValue || ''));
		};

		this.serializeValue = function() {
			var val = $input.val();
			return type === 'int' ? ~~val : (type === 'bool' ? $.boolV(val) : (type === 'decimal' ? parseFloat(val || 0, 10) || 0 : (isEnum ? val || '' : (val || []).join(','))));
		};

		this.applyValue = function(item, state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return this.serializeValue() !== defaultValue;
		};

		this.validate = function() {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},
	
	enumRadioEdt: function(args) {
		var $input;
		var defaultValue;
		var selectedValue;
		var inputName = fastFace.render.uid('input_radio_');
		var scope = this;
		var type = args.column.type;

		this.init = function() {
			var fk = args.column.fk, res = '', clsDef = fastFace.tbl.get(fk.cls), js_def = clsDef.js_def || {}, mod = js_def.mod || 7;
			var data = clsDef.data;
			
			for(var i=0; i<data.length; i++) {
				var val = data[i][0];
				res += (i % mod === 0 ? (res ? '</TR><TR>' : '') : '')+'<TD style="width:20px;" id="td_'+val+'" class="ui-state-disabled"><input id="input_'+val+'" name="'+inputName+'" value="'+val+'" type=radio checked=false></TD><TD id="td_'+val+'" for="'+val+'" class="for ui-state-disabled">' + clsDef.getLblByID(i) + '</TD><TD>&nbsp;</TD>';
			}
			res = '<TABLE style="border:0;"><TR>'+res+'</TR></TABLE>';
			
			$input = $('<DIV style="display:inline-block; border:0;">'+res+'</DIV>');
			$input.appendTo(args.container).on('keydown', function (event) {
				if (event.which === $.ui.keyCode.ENTER) {
					args.commitChanges();
				} else if (event.which === $.ui.keyCode.ESCAPE) {
					event.preventDefault();
					args.cancelChanges();
				}
				event.stopImmediatePropagation();
			}).on('click', '.for', function (event) {
				selectedValue = $(event.currentTarget).attr('for');
				selectedValue = type === 'int' ? ~~selectedValue : (type === 'bool' ? $.boolV(selectedValue) : (type === 'decimal' ? parseFloat(selectedValue || 0, 10) || 0 : selectedValue));
				var $idObj = $input.find('#input_'+selectedValue);
				$idObj.prop('checked', true);
				scope.markChecked(selectedValue, true);
				args.commitChanges();
				event.stopImmediatePropagation();
			}).on('click', 'INPUT', function (event) {
				var $idObj = $(event.currentTarget);
				selectedValue = $idObj.attr('id').replace('input_', '');
				selectedValue = type === 'int' ? ~~selectedValue : (type === 'bool' ? $.boolV(selectedValue) : (type === 'decimal' ? parseFloat(selectedValue || 0, 10) || 0 : selectedValue));
				scope.markChecked(selectedValue, true);
				args.commitChanges();
				event.stopImmediatePropagation();
			}).on('click', function (event) {
				event.stopImmediatePropagation();
			});
			
		};

		this.destroy = function() {
			$input.remove();
		};

		this.focus = function() {
			$input.find('INPUT:checked').focus();
		};

		this.markChecked = function(val, isChecked) {
			if(isChecked) {
				$input.find('TD#td_'+val).removeClass('ui-state-disabled');
			} else {
				$input.find('TD#td_'+val).addClass('ui-state-disabled');
			}
		};

		this.loadValue = function(item) {
			defaultValue = item[args.column.field];
			selectedValue = defaultValue = type === 'int' ? ~~defaultValue : (type === 'bool' ? $.boolV(defaultValue) : (type === 'decimal' ? parseFloat(defaultValue || 0, 10) || 0 : defaultValue || ''));
			$input.find('INPUT#input_'+selectedValue).prop('checked', true).focus();
			scope.markChecked(selectedValue, true);
			scope.focus();
		};

		this.serializeValue = function() {
			return selectedValue;
		};

		this.applyValue = function(item, state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return selectedValue !== defaultValue;
		};

		this.validate = function() {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	setCheckboxEdt: function(args) {
		var $input;
		var defaultValue;
		var defaultValueArr;
		var scope = this;
		var data = null;

		this.init = function() {
			var fk = args.column.fk, res = '', clsDef = fastFace.tbl.get(fk.cls), js_def = clsDef.js_def || {}, mod = js_def.mod || 7;
			data = clsDef.data;
			
			for(var i=0; i<data.length; i++) {
				var val = data[i][0];
				res += (i % mod === 0 ? (res ? '</TR><TR>' : '') : '')+'<TD style="width:20px;" id="td_'+val+'" class="ui-state-disabled"><input id="input_'+val+'" type=checkbox checked=false></TD><TD id="td_'+val+'" for="'+val+'" class="for ui-state-disabled">' + clsDef.getLblByID(i) + '</TD><TD>&nbsp;</TD>';
			}
			res = '<TABLE style="border:0;"><TR>' + res + '<TD colspan='+(mod*3)+'>' + '<BUTTON style="margin: 0; padding: 0 5px 0 5px;">'+fastFace.dict.val('save')+'</BUTTON>&nbsp;&nbsp;<BUTTON style="margin: 0; padding: 0 5px 0 5px;">'+fastFace.dict.val('cancel')+'</BUTTON></TD></TR></TABLE>';
			
			$input = $('<DIV style="display:inline-block; border:0;">'+res+'</DIV>');
			$input.appendTo(args.container).on('keydown', function (event) {
				if (event.which === $.ui.keyCode.ENTER) {
					args.commitChanges();
				} else if (event.which === $.ui.keyCode.ESCAPE) {
					event.preventDefault();
					args.cancelChanges();
				}
				event.stopImmediatePropagation();
			}).on('click', '.for', function (event) {
				var val = $(event.currentTarget).attr('for'), $idObj = $input.find('#input_'+val), isChecked = !$idObj.prop('checked');
				$idObj.prop('checked', isChecked);
				scope.markChecked(val, isChecked);
			}).on('click', 'INPUT', function (event) {
				var $idObj = $(event.currentTarget);
				scope.markChecked($idObj.attr('id').replace('input_', ''), $idObj.prop('checked'));
			}).on('click', function (event) {
				event.stopImmediatePropagation();
			});
			
			$input.find("BUTTON:first").on("click", function(event) {
				args.commitChanges();
			});
			$input.find("BUTTON:last").on("click", function(event) {
				args.cancelChanges();
			});
			
		};

		this.destroy = function() {
			$input.remove();
		};

		this.focus = function() {
			$input.find('INPUT:first').focus();
		};

		this.markChecked = function(val, isChecked) {
			if(isChecked) {
				$input.find('TD#td_'+val).removeClass('ui-state-disabled');
			} else {
				$input.find('TD#td_'+val).addClass('ui-state-disabled');
			}
		};

		this.loadValue = function(item) {
			defaultValueArr = (defaultValue = item[args.column.field] || '').split(',');
			for(var i=0; i<data.length; i++) {
				var val = data[i][0], isChecked = $.inArray(val, defaultValueArr) >= 0;
				$input.find('INPUT#input_'+val).prop('checked', isChecked);
				scope.markChecked(val, isChecked);
			}
			scope.focus();
		};

		this.serializeValue = function() {
			var res = [];
			for(var i=0; i<data.length; i++) {
				var val = data[i][0];
				if($input.find('INPUT#input_'+val).prop('checked')) {
					res.push(val);
				}
			}
			return res.join(',');
		};

		this.applyValue = function(item, state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return scope.serializeValue() !== defaultValue;
		};

		this.validate = function() {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	charEdt: function(args) {
		var $input;
		var defaultValue;
		var scope = this;

		this.init = function() {
			$input = $("<INPUT type=text />")
			.appendTo(args.container)
			.on("keydown", scope.handleKeyDown)
			.on("keydown.nav", function(event) {
				if (event.keyCode === $.ui.keyCode.LEFT || event.keyCode === $.ui.keyCode.RIGHT) {
					event.stopImmediatePropagation();
				} else if (event.keyCode === $.ui.keyCode.LEFT || event.keyCode === $.ui.keyCode.RIGHT) {
					event.stopImmediatePropagation();
				}
			})
			.width($(args.container).width() - 10).addClass(args.column.dir)
			.autoGrowInput();
		};

		this.handleKeyDown = function(event) {
			if (event.which === $.ui.keyCode.ENTER) {
				args.commitChanges();
			} else if (event.which === $.ui.keyCode.ESCAPE) {
				event.preventDefault();
				args.cancelChanges();
			}
			event.stopImmediatePropagation();
		};

		this.save = function() {
			args.commitChanges();
		};

		this.cancel = function() {
			args.cancelChanges();
		};

		this.destroy = function() {
			$input.remove();
		};

		this.focus = function() {
			$input.focus();
		};

		this.loadValue = function(item) {
			defaultValue = $.trim(item[args.column.field] || '').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&amp;/g, '&').replace(/&#0*39;|&apos;|&#x0*27;/g, "'");
			$input.width(args.column.currentWidth - 10)
				.addClass(args.column.dir)
				.val(defaultValue)
				.focus();
		};

		this.serializeValue = function() {
			return $.trim($input.val());
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return $.trim($input.val()) !== defaultValue;
		};

		this.validate = function() {
			if (args.column.validator) {
				var validationResults = args.column.validator($input.val());
				if (!validationResults.valid) { return validationResults; }
			}

			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	intEdt: function(args) {
		var $input;
		var defaultValue;
		var scope = this;

		this.init = function() {
			$input = $("<INPUT type=text />");

			$input
			.on("keydown", scope.handleKeyDown)
			.on("keydown.nav", function(event) {
				if (event.keyCode === $.ui.keyCode.LEFT || event.keyCode === $.ui.keyCode.RIGHT) {
					event.preventDefault();
					event.stopImmediatePropagation();
				}
			})
			.width($(args.container).width());

			$input.appendTo(args.container);
			$input.autoNumeric({mDec: 0, vMin: -999999999});
		};

		this.handleKeyDown = function(event) {
			if (event.which === $.ui.keyCode.ENTER) {
				event.preventDefault();
				args.commitChanges();
			} else if (event.which === $.ui.keyCode.ESCAPE) {
				args.cancelChanges();
			}
			event.stopImmediatePropagation();
		};

		this.save = function() {
			args.commitChanges();
		};

		this.cancel = function() {
			args.cancelChanges();
		};

		this.destroy = function() {
			$input.remove();
		};

		this.focus = function() {
			$input.focus();
		};

		this.loadValue = function(item) {
			$input.autoNumericSet(defaultValue = ~~$.trim(item[args.column.field] || 0)).focus();
		};

		this.serializeValue = function() {
			return ~~$.trim($input.autoNumericGet());
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return ~~$.trim($input.autoNumericGet()) !== defaultValue;
		};

		this.validate = function() {
			if(isNaN($input.autoNumericGet())) { return {
				valid: false,
				msg: "Please enter a valid integer"
			}; }


			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	decimalEdt: function(args) {
		var $input;
		var defaultValue;
		var scope = this;

		this.init = function() {
			$input = $("<INPUT type=text />");

			$input
			.on("keydown", scope.handleKeyDown)
			.on("keydown.nav", function(event) {
				if (event.keyCode === $.ui.keyCode.LEFT || event.keyCode === $.ui.keyCode.RIGHT) {
					event.stopImmediatePropagation();
				}
			});

			$input.appendTo(args.container);
			$input.autoNumeric();
		};

		this.handleKeyDown = function(event) {
			if (event.which === $.ui.keyCode.ENTER) {
				args.commitChanges();
			}
			else if (event.which === $.ui.keyCode.ESCAPE) {
				event.preventDefault();
				args.cancelChanges();
			}
			event.stopImmediatePropagation();
		};

		this.save = function() {
			args.commitChanges();
		};

		this.cancel = function() {
			args.cancelChanges();
		};

		this.destroy = function() {
			$input.remove();
		};

		this.focus = function() {
			$input.focus();
		};

		this.loadValue = function(item) {
			$input.autoNumericSet(defaultValue = parseFloat($.trim(item[args.column.field] || 0), 10) || 0).focus();
		};

		this.serializeValue = function() {
			return parseFloat($.trim($input.autoNumericGet()), 10) || 0;
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return (parseFloat($.trim($input.autoNumericGet()), 10) || 0) !== defaultValue;
		};

		this.validate = function() {
			if(isNaN($.trim($input.autoNumericGet()))) { return {
				valid: false,
				msg: "Please enter a valid number"
			}; }


			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	dateEdt: function(args) {
		var $input;
		var defaultValue;
		var scope = this;
		var isSaved = false;

		this.init = function() {
			$input = $("<INPUT type=text />");
			$input.appendTo(args.container);
			$input.datepicker({
				onSelect: function(dateText, inst) { if(!isSaved) {isSaved = true; args.commitChanges();} },
				onClose: function(dateText, inst) { if(!isSaved) {isSaved = true; args.commitChanges();} }
			});
		};

		this.destroy = function() {
			$input.datepicker("hide");
			$input.datepicker("destroy");
			$input.remove();
		};

		this.show = function() {
			$input.datepicker("show");
		};

		this.hide = function() {
			$input.datepicker("hide");
		};

		this.position = function(position) {
		};

		this.focus = function() {
			$input.focus();
		};

		this.loadValue = function(item) {
			$input.val(defaultValue = item[args.column.field] || '').focus();
		};

		this.serializeValue = function() {
			return $input.val();
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return $input.val() !== defaultValue;
		};

		this.validate = function() {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	datetimeEdt: function(args) {
		var $input;
		var defaultValue;
		var scope = this;
		var isSaved = false;

		this.init = function() {
			$input = $("<INPUT type=text />");
			$input.appendTo(args.container);
			$input.datetimepicker({
				autoSize: false,
				onClose: function(dateText, inst) { if(!isSaved) {isSaved = true; args.commitChanges();} }
			});
		};

		this.destroy = function() {
			$input.datetimepicker("hide");
			$input.datetimepicker("destroy");
			$input.remove();
		};

		this.show = function() {
			$input.datetimepicker("show");
		};

		this.hide = function() {
			$input.datetimepicker("hide");
		};

		this.position = function(position) {
		};

		this.focus = function() {
			$input.focus();
		};

		this.loadValue = function(item) {
			$input.val(defaultValue = item[args.column.field]).focus();
		};

		this.serializeValue = function() {
			return $input.val();
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return (!($input.val() === '' && defaultValue === null)) && ($input.val() !== defaultValue);
		};

		this.validate = function() {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	timeEdt: function(args) {
		var $input;
		var defaultValue;
		var scope = this;
		var isSaved = false;

		this.init = function() {
			$input = $("<INPUT type=text />");
			$input.appendTo(args.container);
			$input.timepicker({
				autoSize: true,
				onClose: function(dateText, inst) { if(!isSaved) {isSaved = true; args.commitChanges();} }
			});
		};

		this.destroy = function() {
			$input.datetimepicker("hide");
			$input.datetimepicker("destroy");
			$input.remove();
		};

		this.show = function() {
			$input.datetimepicker("show");
		};

		this.hide = function() {
			$input.datetimepicker("hide");
		};

		this.position = function(position) {
		};

		this.focus = function() {
			$input.focus();
		};

		this.loadValue = function(item) {
			$input.val(defaultValue = item[args.column.field]).focus();
		};

		this.serializeValue = function() {
			return $input.val();
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return (!($input.val() === '' && defaultValue === null)) && ($input.val() !== defaultValue);
		};

		this.validate = function() {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	unixtimeEdt: function(args) {
		var $input;
		var defaultValue;
		var scope = this;
		var isSaved = false;

		this.init = function() {
			$input = $("<INPUT type=text />");
			$input.appendTo(args.container);
			$input.datetimepicker({
				autoSize: false,
				onClose: function(dateText, inst) { if(!isSaved) {isSaved = true; args.commitChanges();} }
			});
		};

		this.destroy = function() {
			$input.datetimepicker("destroy").remove();
		};

		this.show = function() {
			$input.datetimepicker("show");
		};

		this.hide = function() {
			$input.datetimepicker("hide");
		};

		this.position = function(position) {
		};

		this.focus = function() {
			$input.focus();
		};

		this.loadValue = function(item) {
			defaultValue = item[args.column.field];
			$input.val(date('Y-m-d H:i:s',defaultValue)).focus();
		};

		this.serializeValue = function() {
			return strtotime($input.val());
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return (!($input.val() === '' && defaultValue === null)) && (strtotime($input.val()) !== defaultValue);
		};

		this.validate = function() {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	boolEdt: function(args) {
		var $input;
		var defaultValue;
		var scope = this;

		this.init = function() {
			$input = $('<input type=checkbox checked=false>');
			$input.appendTo(args.container);
			$input.on('keydown', function (event) {
				event.stopImmediatePropagation();
				if (event.which === $.ui.keyCode.ENTER) {
					args.commitChanges();
				} else if (event.which === $.ui.keyCode.ESCAPE) {
					event.preventDefault();
					args.cancelChanges();
				}
			}).on('click', function (event) {
				event.stopImmediatePropagation();
				args.commitChanges();
			});
		};

		this.destroy = function() {
			$input.remove();
		};

		this.loadValue = function(item) {
			$input.prop('checked', defaultValue = $.boolV(item[args.column.field])).focus();
		};

		this.serializeValue = function() {
			return $input.prop('checked');
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return ($input.prop('checked') !== defaultValue);
		};

		this.validate = function() {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	colorEdt: function(args) {
		var $input;
		var defaultValue;
		var newValue = '';
		var scope = this;
		var isLoad = false;

		this.init = function() {
			var uid = fastFace.render.uid('wColorPicker_');
			$input = $('<div id="'+uid+'_holder" style="display:inline-block;"></div>');
			$input.appendTo(args.container);
		};

		this.destroy = function() {
			$input.remove();
		};

		this.show = function() {
		};

		this.hide = function() {
		};

		this.focus = function() {
		};

		this.position = function(position) {
		};

		this.loadValue = function(item) {
			isLoad = true;
			$input.wColorPicker({
				'initColor': newValue = defaultValue = item[args.column.field],
				'onSelect': function(color){
					newValue = color;
					if(!isLoad) {
						args.commitChanges();
					}
				}
			});
			isLoad = false;
		};

		this.serializeValue = function() {
			return newValue;
		};

		this.applyValue = function(item, state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return newValue !== defaultValue;
		};

		this.validate = function() {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	preEdt: function(args) {
		return new fastFace.edt.textEdt(args);
	},

	textEdt: function(args) {
		var $input;
		var defaultValue;
		var scope = this;
		var dir = args.column.dir || 'ltr';

		this.init = function() {
			$input = $("<TEXTAREA rows=5 style='min-width:300px; max-width:100%; height:100px; border: 1px dashed #666666;'>")
			.addClass(dir)
			.appendTo(args.container)
			.on("keydown", scope.handleKeyDown);
		};

		this.handleKeyDown = function(event) {
			if (event.which === $.ui.keyCode.ENTER && event.ctrlKey) {
				args.commitChanges();
			} else if (event.which === $.ui.keyCode.ESCAPE) {
				event.preventDefault();
				args.cancelChanges();
			}
			event.stopImmediatePropagation();
		};

		this.save = function() {
			args.commitChanges();
		};

		this.cancel = function() {
			args.cancelChanges();
		};

		this.destroy = function() {
			$input.remove();
		};

		this.focus = function() {
			$input.focus();
		};

		this.loadValue = function(item) {
			defaultValue = $.trim(item[args.column.field] || '').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#0*39;|&apos;|&#x0*27;/g, "'");
			$input.val(defaultValue).removeClass("rtl ltr").addClass((dir === 'rtl' || $.isRTL(defaultValue)) ? 'rtl' : 'ltr').focus();
		};

		this.serializeValue = function() {
			return $.trim($input.val());
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return $.trim($input.val()) !== defaultValue;
		};

		this.validate = function() {
			if (args.column.validator) {
				var validationResults = args.column.validator($input.val());
				if (!validationResults.valid) { return validationResults; }
			}

			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	preGrd: function(args) {
		return new fastFace.edt.textGrd(args);
	},

	textGrd: function (args) {
		var $input, $wrapper;
		var defaultValue;
		var scope = this;
		var body_width = 0;
		var body_height = 0;
		var editor_width = 0;
		var editor_height = 0;

		this.init = function() {
			var $container = $("body");
			body_width = $container.width();
			body_height = $container.height();

			$wrapper = $("<DIV style='z-index:10000;position:absolute;background:white;padding:5px;border:3px solid gray; -moz-border-radius:10px; border-radius:10px;' />")
			.appendTo($container);

			$input = $("<TEXTAREA hidefocus rows=5 style='backround:white;width:400px;height:200px;border:0;outline:0'>")
			.addClass(args.column.dir).appendTo($wrapper);

			$('<DIV style="text-align:right;"><BUTTON>'+fastFace.dict.val('save')+'</BUTTON><BUTTON>'+fastFace.dict.val('cancel')+'</BUTTON></DIV>')
			.appendTo($wrapper);

			$wrapper.find("button:first").on("click", scope.save);
			$wrapper.find("button:last").on("click", scope.cancel);
			$input.on("keydown", scope.handleKeyDown);

			editor_width = $wrapper.outerWidth();
			editor_height = $wrapper.outerHeight();
			scope.position(args.position);
		};

		this.handleKeyDown = function(event) {
			if (event.which === $.ui.keyCode.ENTER && event.ctrlKey) {
				args.commitChanges();
			} else if (event.which === $.ui.keyCode.ESCAPE) {
				event.preventDefault();
				args.cancelChanges();
			}
			event.stopImmediatePropagation();
		};

		this.save = function() {
			args.commitChanges();
		};

		this.cancel = function() {
			$input.val(defaultValue);
			args.cancelChanges();
		};

		this.hide = function() {
			$wrapper.hide();
		};

		this.show = function() {
			$wrapper.show();
		};

		this.position = function(position) {
			$wrapper
			.css("top", ((position.top + editor_height > body_height)?body_height - editor_height:position.top) - 5)
			.css("left", ((position.left + editor_width > body_width)?body_width - editor_width:position.left) - 5);
		};

		this.destroy = function() {
			$wrapper.remove();
		};

		this.focus = function() {
			$input.focus();
		};

		this.loadValue = function(item) {
			defaultValue = $.trim(item[args.column.field] || '').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#0*39;|&apos;|&#x0*27;/g, "'");
			$input.val(defaultValue).focus();
		};

		this.serializeValue = function() {
			return $.trim($input.val());
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return $.trim($input.val()) !== defaultValue;
		};

		this.validate = function() {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	htmlEdt: function(args) {
		var $input;
		var defaultValue;
		var scope = this;
		var htmlContId = 0;
		var editorInstance = null;

		this.init = function() {
			htmlContId = fastFace.render.uid('htmlCont_');
			$input = $("<textarea id='"+htmlContId+"' name='"+htmlContId+"' dir='"+args.column.dir+"' style='min-width:600px; min-height:200px;'>Loading...</textarea>")
				.addClass(args.column.dir).appendTo(args.container);

			fastFace.htmlEditor.panelInstance(htmlContId,{hasPanel : true});
			editorInstance = fastFace.htmlEditor.instanceById(htmlContId);
			
		};

		this.handleKeyDown = function(event) {
			if (event.which === $.ui.keyCode.ENTER && event.ctrlKey) {
				args.commitChanges();
			} else if (event.which === $.ui.keyCode.ESCAPE) {
				event.preventDefault();
				args.cancelChanges();
			}
			event.stopImmediatePropagation();
		};

		this.save = function() {
			args.commitChanges();
		};

		this.cancel = function() {
			args.cancelChanges();
		};

		this.destroy = function() {
			editorInstance = null;
			fastFace.htmlEditor.removeInstance(htmlContId);
			$input.remove();
		};

		this.focus = function() {
			//$input.focus();
		};

		this.loadValue = function(item) {
			editorInstance.setContent(defaultValue = $.trim(item[args.column.field] || ''));
		};

		this.serializeValue = function() {
			return $.trim(editorInstance.getContent());
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return $.trim(editorInstance.getContent()) !== defaultValue;
		};

		this.validate = function() {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	},

	htmlGrd: function (args) {
		var $input, $wrapper;
		var defaultValue;
		var scope = this;
		var htmlContId = '';
		var editorInstance = null;

		this.init = function() {
			var $container = $("body");

			$wrapper = $('<DIV title="HTML Editor"/>')
			.addClass(args.column.dir)
			.appendTo($container);

			$wrapper.on("keydown", scope.handleKeyDown);

			htmlContId = fastFace.render.uid('htmlCont_');
			$input = $("<textarea id='"+htmlContId+"' dir='"+args.column.dir+"' name='"+htmlContId+"'>Loading...</textarea>")
			.addClass(args.column.dir).appendTo($wrapper);

			$wrapper.dialog({
				fullText: 'Full screen',
				width: '90%', height: 500,
				minWidth: 500, minHeight: 300, maxHeight: ($(window).height()-50),
				draggable:true, resizable:true, modal:false, autoOpen:false, closeOnEscape:true, close: scope.cancel});

			fastFace.htmlEditor.panelInstance(htmlContId,{hasPanel : true});
			editorInstance = fastFace.htmlEditor.instanceById(htmlContId);
			
		};

		this.handleKeyDown = function(event) {
			if (event.which === $.ui.keyCode.ENTER && event.ctrlKey) {
				args.commitChanges();
			} else if (event.which === $.ui.keyCode.ESCAPE) {
				event.preventDefault();
				args.cancelChanges();
			}
			event.stopImmediatePropagation();
		};

		this.save = function() {
			args.commitChanges();
		};

		this.cancel = function() {
			args.cancelChanges();
		};

		this.hide = function() {
		};

		this.show = function() {
			$wrapper.dialog('open');
		};

		this.position = function(position) {
		};

		this.destroy = function() {
			editorInstance = null;
			fastFace.htmlEditor.removeInstance(htmlContId);
			$input.remove();
			if($wrapper.dialog( "isOpen" )) { $wrapper.dialog('destroy').remove(); }
		};

		this.focus = function() {
			//$input.focus();
		};

		this.loadValue = function(item) {
			$wrapper.dialog( "option", "title", 'HTML editor: '+args.column.name+' #'+item[0]); //$.inArray(args.column.edtArg.col, args.column.col)] );
			editorInstance.setContent(defaultValue = $.trim(item[args.column.field] || ''));
		};

		this.serializeValue = function() {
			return $.trim(editorInstance.getContent());
		};

		this.applyValue = function(item,state) {
			item[args.column.field] = state;
		};

		this.isValueChanged = function() {
			return $.trim(editorInstance.getContent()) !== defaultValue;
		};

		this.validate = function() {
			return {
				valid: true,
				msg: null
			};
		};

		this.init();
	}

});
