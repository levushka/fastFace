///////////////////////////////////////////////
//               LANGUAGE
///////////////////////////////////////////////

fastFace.lang = {

	rtl: true,
	ltr: false,
	dir: 'rtl',
	align: 'right',
	alignM: 'left',
	ico: 'secondary',
	old: 'heb',
	cur: 'he',
	id: 0,
	def: 'he',

	data: {
		he: {
			old: 'heb',
			rtl: true
		},
		ru: {
			old: 'rus',
			rtl: false
		}
	},

	changeLang: function(lang) {
		lang = $.inArray(lang, $.keys(this.data)) >= 0 ? lang : this.def;
		
		if(this.cur !== lang) {
			this.cur = lang;

			$.cookie('l', lang, { expires: 30, path: '/' });
			
			this.id  = $.inArray(lang, $.keys(this.data));
			this.old = this.data[lang].old;
			this.rtl = this.data[lang].rtl;
			this.ltr = !this.rtl;
			this.dir = this.rtl ? 'rtl' : 'ltr';
			this.ico = this.rtl ? 'secondary' : 'primary';
			this.align  = this.rtl ? 'right' : 'left';
			this.alignM = this.rtl ? 'left' : 'right';
			
			$('BODY').removeClass("ltr rtl").addClass(fastFace.lang.dir);

			fastFace.sync.start();
			fastFace.dict.reInit();
			fastFace.arr.reInit();
			fastFace.sync.end();
		}
	}
};