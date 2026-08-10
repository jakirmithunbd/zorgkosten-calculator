/* Zorgkosten Calculator – frontend stepper (v2 design)
 * Renders the redesigned calculator from the JSON config produced by the
 * Elementor widget. No dependencies.
 *
 * Screen flow: intro → insurer → policy → deductible → used → info →
 * reimbursement → invoices → [machtiging] → coulance → result.
 * The machtiging screen only exists for insurers that may need one, so the
 * step counter shows 8 or 9 steps depending on the chosen insurer.
 */
(function () {
	'use strict';

	var STEPS = [
		'insurer',
		'policy',
		'deductible',
		'used',
		'info',
		'reimbursement',
		'invoices',
		'machtiging',
		'coulance'
	];

	/* Which sidebar illustration belongs to which screen. */
	var SCREEN_IMAGES = {
		policy: 'verzekering',
		deductible: 'eigenrisico',
		used: 'eigenrisico',
		info: 'gesprek',
		reimbursement: 'gesprek',
		invoices: 'factuur',
		machtiging: 'factuur',
		coulance: 'factuur'
	};

	function fmt(amount) {
		try {
			return new Intl.NumberFormat('nl-NL', {
				style: 'currency',
				currency: 'EUR',
				minimumFractionDigits: 0,
				maximumFractionDigits: 0
			}).format(Math.max(0, Math.round(amount)));
		} catch (e) {
			return '€ ' + Math.max(0, Math.round(amount));
		}
	}

	function el(tag, cls, html) {
		var node = document.createElement(tag);
		if (cls) node.className = cls;
		if (html !== undefined && html !== null) node.innerHTML = html;
		return node;
	}

	function esc(str) {
		return String(str == null ? '' : str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function tpl(str, map) {
		return String(str == null ? '' : str).replace(/\{(\w+)\}/g, function (m, key) {
			return Object.prototype.hasOwnProperty.call(map, key) ? map[key] : m;
		});
	}

	function Calculator(root) {
		var raw = root.getAttribute('data-zkc-config');
		if (!raw) return;
		try {
			this.cfg = JSON.parse(raw);
		} catch (e) {
			return;
		}
		this.root = root;
		this.inner = root.querySelector('.zkc-inner') || root;
		if (this.cfg.general.appFrame) {
			this.root.classList.add('zkc-frame');
		}
		this.state = this.blankState();
		this.render();
	}

	Calculator.prototype.blankState = function () {
		return {
			screen: 'intro',      // 'intro' | a step key | 'result'
			insurer: null,        // insurer object or null
			policy: null,         // policy object, 'unknown', or null
			deductible: null,     // number, 'unknown', or null
			usedDeductible: 0,
			usedUnknown: false
		};
	};

	/* ------------------------------------------------------------------ flow */

	Calculator.prototype.steps = function () {
		var needsMachtiging = !!(this.state.insurer && this.state.insurer.machtiging);
		return STEPS.filter(function (key) {
			return key !== 'machtiging' || needsMachtiging;
		});
	};

	Calculator.prototype.stepIndex = function () {
		return this.steps().indexOf(this.state.screen);
	};

	Calculator.prototype.goTo = function (screen) {
		this.state.screen = screen;
		this.render();
		var main = this.inner.querySelector('.zkc-main');
		if (main) main.scrollTop = 0;
		var rect = this.root.getBoundingClientRect();
		if (rect.top < 0) {
			this.root.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
	};

	Calculator.prototype.goNext = function () {
		var steps = this.steps();
		var i = steps.indexOf(this.state.screen);
		this.goTo(i === -1 || i >= steps.length - 1 ? 'result' : steps[i + 1]);
	};

	Calculator.prototype.goBack = function () {
		var steps = this.steps();
		if (this.state.screen === 'result') {
			return this.goTo(steps[steps.length - 1]);
		}
		var i = steps.indexOf(this.state.screen);
		this.goTo(i <= 0 ? 'intro' : steps[i - 1]);
	};

	Calculator.prototype.restart = function () {
		this.state = this.blankState();
		this.render();
	};

	/* ------------------------------------------------------------------ data */

	Calculator.prototype.basisInfo = function (key) {
		var bases = this.cfg.bases || [];
		for (var i = 0; i < bases.length; i++) {
			if (bases[i].key === key) return bases[i];
		}
		return { key: key, label: key, title: key, text: '' };
	};

	Calculator.prototype.policiesFor = function (insurerName) {
		return (this.cfg.policies || []).filter(function (p) {
			return p.insurer === insurerName;
		});
	};

	Calculator.prototype.insurerName = function () {
		return this.state.insurer ? this.state.insurer.name : this.cfg.general.fallbackName;
	};

	Calculator.prototype.machtigingUrl = function () {
		var ins = this.state.insurer;
		if (!ins) return '';
		return ins.machtigingUrl || ins.declareUrl || '';
	};

	function pctLabel(min, max) {
		return max === null || max === undefined || max === min
			? Math.round(min) + '%'
			: Math.round(min) + '% tot ' + Math.round(max) + '%';
	}

	/* Resolves the current reimbursement: percentage label ("70%" or
	 * "60% tot 100%"), the money (single expected amount plus a range when
	 * applicable), the basis and which explanatory message to use.
	 *
	 * Mirrors the original app:
	 * - policy known        → that policy's percentage or range;
	 * - policy unknown but  → the lowest and highest percentage across ALL
	 *   insurer known         policies of that insurer (a range);
	 * - otherwise           → the configured default percentage.
	 */
	Calculator.prototype.reimbursement = function () {
		var c = this.cfg.calc;
		var s6 = this.cfg.steps.s6;
		var pol = this.state.policy;
		var ins = this.state.insurer;
		var invoice = c.avgInvoice;

		var min, max = null, basis, message;

		if (pol && pol !== 'unknown') {
			min = pol.percentage;
			max = (pol.percentageMax === null || pol.percentageMax === undefined) ? null : pol.percentageMax;
			basis = pol.basis;
			message = s6.message;
		} else if (pol === 'unknown' && ins && this.policiesFor(ins.name).length) {
			var all = [];
			this.policiesFor(ins.name).forEach(function (p) {
				all.push(p.percentage);
				if (p.percentageMax !== null && p.percentageMax !== undefined) {
					all.push(p.percentageMax);
				}
			});
			min = Math.min.apply(null, all);
			max = Math.max.apply(null, all);
			if (max === min) max = null;
			basis = this.policiesFor(ins.name)[0].basis || c.defaultBasis;
			message = max === null ? s6.message : s6.messageInsurer;
		} else {
			min = c.defaultPercentage;
			basis = c.defaultBasis;
			message = s6.messageUnknown;
		}

		var basisLabel = this.basisInfo(basis).label;
		var label = pctLabel(min, max);
		var mid = max === null ? min : (min + max) / 2;
		var expected = Math.round(invoice * (mid / 100));
		var range = max === null ? null : [
			Math.round(invoice * (min / 100)),
			Math.round(invoice * (max / 100))
		];

		return {
			policyUnknown: pol === 'unknown' || pol === null,
			min: min,
			max: max,
			label: label,
			basis: basis,
			basisLabel: basisLabel,
			note: (pol && pol !== 'unknown' && pol.note) || '',
			expected: expected,
			range: range,
			amountLabel: range ? fmt(range[0]) + ' tot ' + fmt(range[1]) : '± ' + fmt(expected),
			message: tpl(message, {
				insurer: this.insurerName(),
				percentage: label,
				basis: basisLabel
			})
		};
	};

	/* The own-costs figures. When either the total deductible or the amount
	 * already used is unknown we can only show a range. */
	Calculator.prototype.ownCosts = function () {
		var c = this.cfg.calc;
		var totalKnown = this.state.deductible !== null && this.state.deductible !== 'unknown';
		var known = totalKnown && !this.state.usedUnknown;

		if (known) {
			var remaining = Math.max(0, this.state.deductible - (this.state.usedDeductible || 0));
			return {
				known: true,
				deductibleLabel: fmt(remaining),
				totalLabel: fmt(c.contribution + remaining)
			};
		}

		var ceiling = totalKnown ? this.state.deductible : c.maxDeductible;
		return {
			known: false,
			deductibleLabel: fmt(0) + ' tot ' + fmt(ceiling),
			totalLabel: fmt(c.contribution) + ' tot ' + fmt(c.contribution + ceiling)
		};
	};

	/* ---------------------------------------------------------------- render */

	Calculator.prototype.render = function () {
		var self = this;
		var g = this.cfg.general;
		var screen = this.state.screen;
		this.inner.innerHTML = '';

		this.root.classList.toggle('zkc-on-result', screen === 'result');

		// Top bar: "Berekening aanpassen" (result only) + "Opnieuw beginnen".
		var top = el('div', 'zkc-topbar');
		if (screen === 'result') {
			var adjust = el('button', 'zkc-link', esc(g.adjust));
			adjust.type = 'button';
			adjust.addEventListener('click', function () { self.goBack(); });
			top.appendChild(adjust);
		} else {
			top.appendChild(el('span', ''));
		}
		if (screen !== 'intro') {
			var restart = el('button', 'zkc-link', esc(g.restart));
			restart.type = 'button';
			restart.addEventListener('click', function () { self.restart(); });
			top.appendChild(restart);
		}
		this.inner.appendChild(top);

		if (screen === 'intro') {
			this.renderIntro(this.inner);
			return;
		}
		if (screen === 'result') {
			var main = el('div', 'zkc-result');
			this.inner.appendChild(main);
			this.renderResult(main);
			return;
		}

		// Steps: single column for the insurer grid, two columns afterwards.
		var withAside = screen !== 'insurer';
		var layout = el('div', 'zkc-layout' + (withAside ? ' zkc-has-aside' : ''));
		this.inner.appendChild(layout);

		var col = el('div', 'zkc-col');
		layout.appendChild(col);
		var main2 = el('div', 'zkc-main');
		col.appendChild(main2);

		switch (screen) {
			case 'insurer':       this.renderInsurers(main2); break;
			case 'policy':        this.renderPolicies(main2); break;
			case 'deductible':    this.renderDeductible(main2); break;
			case 'used':          this.renderUsedDeductible(main2); break;
			case 'info':          this.renderInfo(main2); break;
			case 'reimbursement': this.renderReimbursement(main2); break;
			case 'invoices':      this.renderInvoices(main2); break;
			case 'machtiging':    this.renderMachtiging(main2); break;
			case 'coulance':      this.renderCoulance(main2); break;
		}

		col.appendChild(this.navBar());

		if (withAside) {
			layout.appendChild(this.aside());
		}
	};

	/* Decorative blob composition around an illustration. */
	Calculator.prototype.illustration = function (src, alt, cls) {
		var wrap = el('div', 'zkc-illu ' + (cls || ''));
		wrap.innerHTML =
			'<span class="zkc-blob zkc-blob-1" aria-hidden="true"></span>' +
			'<span class="zkc-blob zkc-blob-2" aria-hidden="true"></span>' +
			'<span class="zkc-blob zkc-blob-3" aria-hidden="true"></span>' +
			'<span class="zkc-dot zkc-dot-1" aria-hidden="true"></span>' +
			'<span class="zkc-dot zkc-dot-2" aria-hidden="true"></span>' +
			'<span class="zkc-dot zkc-dot-3" aria-hidden="true"></span>' +
			'<span class="zkc-dot zkc-dot-4" aria-hidden="true"></span>' +
			'<img src="' + esc(src) + '" alt="' + esc(alt) + '" loading="lazy" width="1024" height="1024">';
		return wrap;
	};

	/* Right sidebar: "Uw gegevens" chips + step illustration. */
	Calculator.prototype.aside = function () {
		var self = this;
		var g = this.cfg.general;
		var aside = el('aside', 'zkc-aside');

		var head = el('div', 'zkc-aside-head');
		head.appendChild(el('div', 'zkc-aside-title', esc(g.sidebarTitle)));
		var chips = el('div', 'zkc-chips');

		var items = [];
		if (this.state.insurer) {
			items.push({ label: this.state.insurer.name, screen: 'insurer' });
		}
		if (this.state.policy && this.state.policy !== 'unknown') {
			items.push({ label: this.state.policy.name, screen: 'policy' });
		}
		if (this.state.deductible !== null && this.state.deductible !== 'unknown') {
			items.push({
				label: tpl(g.chipDeductible, { amount: fmt(this.state.deductible) }),
				screen: 'deductible'
			});
		}
		if (!this.state.usedUnknown && this.stepIndex() > this.steps().indexOf('used')) {
			items.push({
				label: tpl(g.chipUsed, { amount: fmt(this.state.usedDeductible || 0) }),
				screen: 'used'
			});
		}

		if (!items.length) {
			chips.appendChild(el('span', 'zkc-chips-empty', esc(g.sidebarEmpty)));
		}
		items.forEach(function (item) {
			var chip = el('button', 'zkc-chip', '<span>' + esc(item.label) + '</span>');
			chip.type = 'button';
			chip.title = g.chipTitle;
			chip.addEventListener('click', function () { self.goTo(item.screen); });
			chips.appendChild(chip);
		});
		head.appendChild(chips);
		aside.appendChild(head);

		var imgKey = SCREEN_IMAGES[this.state.screen] || 'verzekering';
		var img = (this.cfg.images || {})[imgKey] || { src: '', alt: '' };
		aside.appendChild(this.illustration(img.src, img.alt, 'zkc-illu-aside'));

		return aside;
	};

	/* Bottom navigation: back link, progress segments, next button. */
	Calculator.prototype.navBar = function () {
		var self = this;
		var g = this.cfg.general;
		var steps = this.steps();
		var index = this.stepIndex();
		var current = index + 1;
		var total = steps.length;

		var bar = el('div', 'zkc-navbar');

		var back = el('button', 'zkc-nav-back', '<span aria-hidden="true">&larr;</span> ' + esc(g.back));
		back.type = 'button';
		back.addEventListener('click', function () { self.goBack(); });
		bar.appendChild(back);

		var mid = el('div', 'zkc-progress');
		var segs = el('div', 'zkc-segments');
		for (var i = 0; i < total; i++) {
			segs.appendChild(el('span', 'zkc-seg' + (i < current ? ' zkc-seg-on' : '')));
		}
		mid.appendChild(segs);
		mid.appendChild(el('span', 'zkc-counter', esc(tpl(g.stepCounter, { current: current, total: total }))));
		bar.appendChild(mid);

		if (this._next) {
			var next = el('button', 'zkc-btn zkc-nav-next');
			next.innerHTML = '<span>' + esc(this._nextLabel || g.next) + '</span><span aria-hidden="true">&rarr;</span>';
			next.type = 'button';
			next.addEventListener('click', this._next);
			bar.appendChild(next);
		} else {
			bar.appendChild(el('span', 'zkc-nav-spacer'));
		}

		return bar;
	};

	/* Each renderer sets what the nav "next" button does (null = no button). */
	Calculator.prototype.setNav = function (next, nextLabel) {
		this._next = next || null;
		this._nextLabel = nextLabel || null;
	};

	Calculator.prototype.stepHead = function (card, title, subtitle, help) {
		var g = this.cfg.general;
		var head = el('div', 'zkc-step-head');
		head.appendChild(el('h2', 'zkc-title', esc(title)));
		if (subtitle) head.appendChild(el('p', 'zkc-subtitle', esc(subtitle)));
		if (help) {
			var box = el('div', 'zkc-help');
			box.appendChild(el('div', 'zkc-help-label', esc(g.helpLabel)));
			box.appendChild(el('div', '', esc(help)));
			head.appendChild(box);
		}
		card.appendChild(head);
	};

	/* Warning box ("Let op"). */
	Calculator.prototype.warnBox = function (label, html) {
		var box = el('div', 'zkc-warn');
		box.appendChild(el('div', 'zkc-warn-label', esc(label || this.cfg.general.warnLabel)));
		box.appendChild(el('div', 'zkc-warn-text', html));
		return box;
	};

	/* Intro */
	Calculator.prototype.renderIntro = function (parent) {
		var self = this;
		var i = this.cfg.intro;
		var wrap = el('div', 'zkc-intro');

		var copy = el('div', 'zkc-intro-copy');
		copy.appendChild(el('div', 'zkc-eyebrow', esc(i.kicker)));
		copy.appendChild(el('h1', 'zkc-intro-title', esc(i.title)));
		copy.appendChild(el('p', 'zkc-intro-text', esc(i.text)));

		if (i.bullets && i.bullets.length) {
			var ul = el('ul', 'zkc-bullets');
			i.bullets.forEach(function (b) {
				ul.appendChild(el('li', '', '<span class="zkc-bullet-dot" aria-hidden="true"></span><span>' + esc(b) + '</span>'));
			});
			copy.appendChild(ul);
		}

		var btn = el('button', 'zkc-btn zkc-intro-btn', esc(i.button));
		btn.type = 'button';
		btn.addEventListener('click', function () { self.goTo('insurer'); });
		copy.appendChild(btn);
		wrap.appendChild(copy);

		var media = el('div', 'zkc-intro-media');
		media.appendChild(this.illustration(i.image, i.imageAlt, 'zkc-illu-intro'));
		wrap.appendChild(media);

		parent.appendChild(wrap);
	};

	/* Step 1 — insurer */
	Calculator.prototype.renderInsurers = function (card) {
		var self = this;
		var s = this.cfg.steps.s1;
		this.stepHead(card, s.title, null, s.help);

		// Group insurers preserving order.
		var groups = [];
		var byName = {};
		(this.cfg.insurers || []).forEach(function (ins) {
			var key = ins.group || '';
			if (!byName[key]) {
				byName[key] = { name: key, insurers: [] };
				groups.push(byName[key]);
			}
			byName[key].insurers.push(ins);
		});

		var wrap = el('div', 'zkc-groups');
		groups.forEach(function (grp) {
			var section = el('section', 'zkc-group');
			section.appendChild(el('div', 'zkc-group-title', '<span class="zkc-eyebrow">' + esc(grp.name) + '</span><span class="zkc-group-line"></span>'));
			var grid = el('div', 'zkc-tiles');
			grp.insurers.forEach(function (ins) {
				var selected = self.state.insurer && self.state.insurer.name === ins.name;
				var tile = el('button', 'zkc-tile' + (selected ? ' zkc-selected' : ''));
				tile.type = 'button';
				tile.title = ins.name;
				var logoHtml = ins.logo
					? '<img class="zkc-tile-logo" src="' + esc(ins.logo) + '" alt="' + esc(ins.name) + '" loading="lazy">'
					: '<span class="zkc-tile-initial">' + esc((ins.name || '?').slice(0, 3)) + '</span>';
				tile.innerHTML = '<span class="zkc-tile-media">' + logoHtml + '</span><span class="zkc-tile-name">' + esc(ins.name) + '</span>';
				tile.addEventListener('click', function () {
					self.state.insurer = ins;
					self.state.policy = null;
					self.goTo('policy');
				});
				grid.appendChild(tile);
			});
			section.appendChild(grid);
			wrap.appendChild(section);
		});
		card.appendChild(wrap);

		this.setNav(null);
	};

	/* Step 2 — policy */
	Calculator.prototype.renderPolicies = function (card) {
		var self = this;
		var s = this.cfg.steps.s2;
		var insurer = this.state.insurer || { name: '' };
		this.stepHead(card, s.title, tpl(s.subtitle, { insurer: insurer.name }), s.help);

		var list = el('div', 'zkc-options');
		this.policiesFor(insurer.name).forEach(function (p) {
			var selected = self.state.policy && self.state.policy !== 'unknown' && self.state.policy.name === p.name;
			var btn = el('button', 'zkc-option' + (selected ? ' zkc-selected' : ''), esc(p.name));
			btn.type = 'button';
			btn.addEventListener('click', function () {
				self.state.policy = p;
				self.goTo('deductible');
			});
			list.appendChild(btn);
		});
		var unknown = el('button', 'zkc-option' + (this.state.policy === 'unknown' ? ' zkc-selected' : ''), esc(this.cfg.general.unknown));
		unknown.type = 'button';
		unknown.addEventListener('click', function () {
			self.state.policy = 'unknown';
			self.goTo('deductible');
		});
		list.appendChild(unknown);
		card.appendChild(list);

		this.setNav(null);
	};

	/* Step 3 — total deductible */
	Calculator.prototype.renderDeductible = function (card) {
		var self = this;
		var s = this.cfg.steps.s3;
		this.stepHead(card, s.title, s.text, s.help);

		var grid = el('div', 'zkc-amount-grid');
		(this.cfg.calc.deductibles || []).forEach(function (amount) {
			var selected = self.state.deductible === amount;
			var btn = el('button', 'zkc-option zkc-option-amount' + (selected ? ' zkc-selected' : ''), '<span class="zkc-display">' + esc(fmt(amount)) + '</span>');
			btn.type = 'button';
			btn.addEventListener('click', function () {
				self.state.deductible = amount;
				self.goTo('used');
			});
			grid.appendChild(btn);
		});
		card.appendChild(grid);

		var unknown = el('button', 'zkc-option zkc-option-wide' + (this.state.deductible === 'unknown' ? ' zkc-selected' : ''), esc(this.cfg.general.unknown));
		unknown.type = 'button';
		unknown.addEventListener('click', function () {
			self.state.deductible = 'unknown';
			self.goTo('used');
		});
		card.appendChild(unknown);

		this.setNav(null);
	};

	/* Step 4 — used deductible (validated) */
	Calculator.prototype.renderUsedDeductible = function (card) {
		var self = this;
		var s = this.cfg.steps.s4;
		this.stepHead(card, s.title, null, s.help);

		card.appendChild(el('label', 'zkc-label', esc(s.fieldLabel)));

		var fieldWrap = el('div', 'zkc-input-wrap');
		fieldWrap.appendChild(el('span', 'zkc-input-prefix', '€'));
		var input = el('input', 'zkc-input');
		input.type = 'number';
		input.min = '0';
		input.inputMode = 'decimal';
		input.placeholder = s.placeholder || '';
		if (!this.state.usedUnknown && this.state.usedDeductible) {
			input.value = this.state.usedDeductible;
		}
		fieldWrap.appendChild(input);
		card.appendChild(fieldWrap);

		var error = el('p', 'zkc-error');
		error.hidden = true;
		card.appendChild(error);

		var checkWrap = el('label', 'zkc-check' + (this.state.usedUnknown ? ' zkc-selected' : ''));
		var check = el('input', '');
		check.type = 'checkbox';
		check.checked = !!this.state.usedUnknown;
		checkWrap.appendChild(check);
		checkWrap.appendChild(el('span', '', esc(this.cfg.general.unknown)));
		card.appendChild(checkWrap);

		function clearError() {
			error.hidden = true;
			error.textContent = '';
		}

		check.addEventListener('change', function () {
			input.disabled = check.checked;
			if (check.checked) input.value = '';
			checkWrap.classList.toggle('zkc-selected', check.checked);
			clearError();
		});
		input.addEventListener('input', function () {
			if (check.checked) check.checked = false;
			input.disabled = false;
			checkWrap.classList.remove('zkc-selected');
			clearError();
		});
		input.disabled = check.checked;

		// The amount already used can never exceed the chosen total.
		var ceiling = (this.state.deductible === null || this.state.deductible === 'unknown')
			? Infinity
			: this.state.deductible;

		this.setNav(function () {
			if (check.checked) {
				self.state.usedUnknown = true;
				self.state.usedDeductible = 0;
				return self.goNext();
			}
			var v = parseFloat(String(input.value).replace(',', '.'));
			if (isNaN(v) || v < 0) {
				error.textContent = s.errorInvalid;
				error.hidden = false;
				return;
			}
			if (v > ceiling) {
				error.textContent = s.errorMax;
				error.hidden = false;
				return;
			}
			self.state.usedUnknown = false;
			self.state.usedDeductible = Math.round(v);
			self.goNext();
		});
	};

	/* Step 5 — how costs are determined */
	Calculator.prototype.renderInfo = function (card) {
		var self = this;
		var s = this.cfg.steps.s5;
		this.stepHead(card, s.title);
		card.appendChild(el('div', 'zkc-rich', s.content));
		if (s.panel) card.appendChild(el('div', 'zkc-panel zkc-rich', s.panel));
		this.setNav(function () { self.goNext(); });
	};

	/* Step 6 — reimbursement */
	Calculator.prototype.renderReimbursement = function (card) {
		var self = this;
		var s = this.cfg.steps.s6;
		var r = this.reimbursement();
		var basis = this.basisInfo(r.basis);

		var hero = el('div', 'zkc-hero');
		hero.appendChild(el('div', 'zkc-hero-kicker', esc(
			this.state.insurer
				? tpl(s.heroTitle, { insurer: this.state.insurer.name })
				: s.heroTitleFallback
		)));
		hero.appendChild(el('div', 'zkc-hero-pct zkc-display', esc(r.label)));
		hero.appendChild(el('p', 'zkc-hero-basis', esc(tpl(s.heroBasis, { basis: r.basisLabel }))));

		var estimate = el('div', 'zkc-hero-estimate');
		estimate.appendChild(el('div', 'zkc-hero-estimate-kicker', esc(s.estimateKicker)));
		estimate.appendChild(el('div', 'zkc-hero-amount zkc-display', esc(r.amountLabel)));
		estimate.appendChild(el('p', 'zkc-hero-note', esc(tpl(s.estimateNote, {
			invoice: fmt(this.cfg.calc.avgInvoice)
		}))));
		hero.appendChild(estimate);

		if (r.note) hero.appendChild(el('p', 'zkc-hero-policy-note', esc(r.note)));
		card.appendChild(hero);

		var body = el('div', 'zkc-step6-body');
		body.appendChild(el('div', 'zkc-panel', '<p>' + esc(r.message) + '</p>'));

		var box = el('div', 'zkc-panel');
		box.appendChild(el('div', 'zkc-panel-kicker', esc(s.basisKicker)));
		box.appendChild(el('h3', 'zkc-h3', esc(basis.title)));
		box.appendChild(el('p', 'zkc-text-sm', esc(basis.text)));
		body.appendChild(box);

		var det = el('details', 'zkc-accordion zkc-panel');
		det.appendChild(el('summary', '', esc(s.accordionLabel)));
		var detBody = el('div', 'zkc-accordion-body');
		(this.cfg.bases || []).forEach(function (b) {
			detBody.appendChild(el('h4', 'zkc-h4', esc(b.title)));
			detBody.appendChild(el('p', 'zkc-text-sm', esc(b.text)));
		});
		det.appendChild(detBody);
		body.appendChild(det);

		if (s.footnote) body.appendChild(el('p', 'zkc-text-sm', esc(s.footnote)));
		card.appendChild(body);

		this.setNav(function () { self.goNext(); });
	};

	/* Step 7 — invoices */
	Calculator.prototype.renderInvoices = function (card) {
		var self = this;
		var s = this.cfg.steps.s7;
		var insurer = this.state.insurer || { name: this.cfg.general.fallbackName, agreement: false };
		this.stepHead(card, s.title);

		var yes = !!insurer.agreement;
		var badge = el('div', 'zkc-badge ' + (yes ? 'zkc-badge-ok' : 'zkc-badge-no'));
		badge.appendChild(el('div', 'zkc-badge-kicker', esc(yes ? s.yesBadge : s.noBadge)));
		badge.appendChild(el('p', '', esc(tpl(yes ? s.yesIntro : s.noIntro, { insurer: insurer.name }))));
		card.appendChild(badge);

		var content = el('div', 'zkc-panel zkc-rich', yes ? s.yesContent : s.noContent);
		var note = yes ? s.yesNote : s.noNote;
		if (note) content.appendChild(this.warnBox(yes ? s.yesNoteLabel : s.noNoteLabel, esc(note)));
		card.appendChild(content);

		this.setNav(function () { self.goNext(); });
	};

	/* Step 8 — authorization (machtiging), only for insurers that may need one */
	Calculator.prototype.renderMachtiging = function (card) {
		var self = this;
		var s = this.cfg.steps.s8;
		var name = this.insurerName();
		var url = this.machtigingUrl();
		this.stepHead(card, s.title);

		var body = el('div', 'zkc-step8-body');
		body.appendChild(el('div', 'zkc-rich', s.content));

		if (s.asks && s.asks.length) {
			var box = el('div', 'zkc-panel');
			box.appendChild(el('h3', 'zkc-h3', esc(s.asksTitle)));
			var ul = el('ul', 'zkc-list');
			s.asks.forEach(function (line) { ul.appendChild(el('li', '', esc(line))); });
			box.appendChild(ul);
			body.appendChild(box);
		}

		if (url) {
			var check = el('div', 'zkc-panel');
			check.appendChild(el('h3', 'zkc-h3', esc(tpl(s.checkTitle, { insurer: name }))));
			check.appendChild(el('p', '', esc(tpl(s.checkText, { insurer: name }))));
			var link = el('a', 'zkc-btn-outline', esc(tpl(s.checkButton, { insurer: name })));
			link.href = url;
			link.target = '_blank';
			link.rel = 'noopener noreferrer';
			check.appendChild(link);
			body.appendChild(check);
		}

		if (s.footnote) body.appendChild(this.warnBox(null, esc(s.footnote)));
		card.appendChild(body);

		this.setNav(function () { self.goNext(); });
	};

	/* Step 9 — coulance */
	Calculator.prototype.renderCoulance = function (card) {
		var self = this;
		var s = this.cfg.steps.s9;
		this.stepHead(card, s.title);

		var body = el('div', 'zkc-step9-body');
		body.appendChild(el('div', 'zkc-rich', s.intro));

		if (s.conditions && s.conditions.length) {
			var box = el('div', 'zkc-panel');
			box.appendChild(el('h3', 'zkc-h3', esc(s.conditionsTitle)));
			var ul = el('ul', 'zkc-list');
			s.conditions.forEach(function (line) { ul.appendChild(el('li', '', esc(line))); });
			box.appendChild(ul);
			body.appendChild(box);
		}

		if (s.excluded && s.excluded.length) {
			var box2 = el('div', 'zkc-panel');
			box2.appendChild(el('h3', 'zkc-h3', esc(s.excludedTitle)));
			var ul2 = el('ul', 'zkc-list');
			s.excluded.forEach(function (line) { ul2.appendChild(el('li', '', esc(line))); });
			box2.appendChild(ul2);
			body.appendChild(box2);
		}
		card.appendChild(body);

		this.setNav(function () { self.goNext(); }, s.button);
	};

	/* Result */
	Calculator.prototype.renderResult = function (card) {
		var self = this;
		var r = this.cfg.result;
		var c = this.cfg.calc;
		var reimb = this.reimbursement();
		var own = this.ownCosts();
		var name = this.insurerName();
		var ins = this.state.insurer;
		var invoice = c.avgInvoice;

		// Header row: copy + illustration.
		var head = el('div', 'zkc-r-head');
		var copy = el('div', '');
		copy.appendChild(el('div', 'zkc-eyebrow', esc(r.kicker)));
		copy.appendChild(el('h1', 'zkc-r-title', esc(r.title)));
		copy.appendChild(el('p', 'zkc-r-intro', esc(tpl(r.intro, { invoice: fmt(invoice), insurer: name }))));
		head.appendChild(copy);
		var img = (this.cfg.images || {}).factuur || { src: '', alt: '' };
		var media = el('div', 'zkc-r-media');
		media.appendChild(this.illustration(img.src, img.alt, 'zkc-illu-result'));
		head.appendChild(media);
		card.appendChild(head);

		// Invoice panel with the split bar.
		var panel = el('div', 'zkc-r-invoice');
		panel.appendChild(el('h2', 'zkc-r-invoice-title', esc(tpl(r.invoiceTitle, { invoice: fmt(invoice) }))));
		panel.appendChild(el('p', 'zkc-r-invoice-text', esc(r.invoiceText)));

		var pctMin = reimb.range ? Math.round((reimb.range[0] / invoice) * 100) : Math.round((reimb.expected / invoice) * 100);
		var pctMax = reimb.range ? Math.round((reimb.range[1] / invoice) * 100) : pctMin;

		var bar = el('div', 'zkc-splitbar');
		var left = el('div', 'zkc-split-reimb', '<span>' + esc(reimb.range ? pctMin + '% tot ' + pctMax + '%' : pctMin + '%') + '</span>');
		left.style.width = pctMin + '%';
		bar.appendChild(left);
		if (reimb.range && pctMax > pctMin) {
			var midSeg = el('div', 'zkc-split-uncertain');
			midSeg.style.width = (pctMax - pctMin) + '%';
			midSeg.title = r.uncertainTooltip;
			bar.appendChild(midSeg);
		}
		var right = el('div', 'zkc-split-waived', '<span>' + esc(reimb.range ? (100 - pctMax) + '% tot ' + (100 - pctMin) + '%' : (100 - pctMin) + '%') + '</span>');
		right.style.width = (100 - pctMax) + '%';
		bar.appendChild(right);
		panel.appendChild(bar);

		var waivedLabel = reimb.range
			? fmt(invoice - reimb.range[1]) + ' tot ' + fmt(invoice - reimb.range[0])
			: '± ' + fmt(Math.max(0, invoice - reimb.expected));

		var legend = el('div', 'zkc-r-legend');
		var l1 = el('div', 'zkc-r-legend-item');
		l1.appendChild(el('div', 'zkc-r-legend-title', '<span class="zkc-dot-reimb" aria-hidden="true"></span>' + esc(tpl(r.reimbursedLabel, { insurer: name, amount: reimb.amountLabel }))));
		l1.appendChild(el('p', 'zkc-text-sm', esc(tpl(r.reimbursedText, { percentage: reimb.label, basis: reimb.basisLabel }))));
		var l2 = el('div', 'zkc-r-legend-item');
		l2.appendChild(el('div', 'zkc-r-legend-title', '<span class="zkc-dot-waived" aria-hidden="true"></span>' + esc(tpl(r.waivedLabel, { amount: waivedLabel }))));
		l2.appendChild(el('p', 'zkc-text-sm', esc(r.waivedText)));
		legend.appendChild(l1);
		legend.appendChild(l2);
		panel.appendChild(legend);

		if (reimb.policyUnknown && reimb.range) {
			panel.appendChild(el('div', 'zkc-r-note', esc(tpl(r.rangeNotePolicy, { insurer: name, percentage: reimb.label }))));
		}
		card.appendChild(panel);

		// Two cards: own costs + what you arrange yourself.
		var grid = el('div', 'zkc-r-grid');

		var ownCard = el('div', 'zkc-r-own');
		ownCard.appendChild(el('div', 'zkc-r-card-kicker', esc(r.ownKicker)));
		ownCard.appendChild(el('div', 'zkc-r-own-total zkc-display', esc(own.totalLabel)));
		ownCard.appendChild(el('p', 'zkc-text-sm', esc(r.ownText)));

		var dl = el('dl', 'zkc-r-rows');
		var row1 = el('div', 'zkc-r-row');
		row1.appendChild(el('dt', '', '<span class="zkc-r-row-label">' + esc(r.contributionLabel) + '</span><span class="zkc-r-row-sub">' + esc(r.contributionText) + '</span>'));
		row1.appendChild(el('dd', '', esc(fmt(c.contribution))));
		dl.appendChild(row1);
		var row2 = el('div', 'zkc-r-row');
		row2.appendChild(el('dt', '', '<span class="zkc-r-row-label">' + esc(r.deductibleLabel) + '</span><span class="zkc-r-row-sub">' + esc(tpl(r.deductibleText, { insurer: name })) + '</span>'));
		row2.appendChild(el('dd', '', esc(own.deductibleLabel)));
		dl.appendChild(row2);
		ownCard.appendChild(dl);

		if (!own.known) {
			ownCard.appendChild(el('div', 'zkc-r-note', esc(r.usedUnknownNote)));
		}
		grid.appendChild(ownCard);

		var selfCard = el('div', 'zkc-r-self');
		selfCard.appendChild(el('div', 'zkc-r-card-kicker', esc(r.selfKicker)));
		var yes = !!(ins && ins.agreement);
		selfCard.appendChild(el('h3', 'zkc-r-self-title', esc(tpl(yes ? r.selfTitleYes : r.selfTitleNo, { insurer: name }))));
		if (yes) {
			selfCard.appendChild(el('p', 'zkc-text-sm', esc(tpl(r.selfTextYes, { insurer: name }))));
		} else {
			var ol = el('ol', 'zkc-r-steps');
			(r.selfSteps || []).forEach(function (line) {
				ol.appendChild(el('li', '', esc(tpl(line, { insurer: name, amount: fmt(reimb.expected) }))));
			});
			selfCard.appendChild(ol);
			if (ins && ins.declareUrl) {
				var declare = el('a', 'zkc-btn-outline', esc(tpl(r.declareButton, { insurer: name })));
				declare.href = ins.declareUrl;
				declare.target = '_blank';
				declare.rel = 'noopener noreferrer';
				selfCard.appendChild(declare);
			}
		}
		if (ins && ins.machtiging) {
			var mUrl = this.machtigingUrl();
			var html = esc(tpl(r.machtigingText, { insurer: name }));
			if (mUrl) {
				html += ' <a href="' + esc(mUrl) + '" target="_blank" rel="noopener noreferrer">' + esc(tpl(r.machtigingLink, { insurer: name })) + '</a>.';
			}
			selfCard.appendChild(this.warnBox(r.machtigingLabel, html));
		}
		grid.appendChild(selfCard);
		card.appendChild(grid);

		// Notes + disclaimer.
		var notes = el('div', 'zkc-r-footnotes');
		if (reimb.policyUnknown && !reimb.range && r.notePolicyUnknown) {
			notes.appendChild(this.warnBox(null, esc(r.notePolicyUnknown)));
		}
		notes.appendChild(el('p', 'zkc-r-disclaimer', esc(r.disclaimer)));
		card.appendChild(notes);

		// CTA panel.
		var cta = el('div', 'zkc-r-cta');
		var ctaCopy = el('div', '');
		ctaCopy.appendChild(el('h2', 'zkc-r-cta-title', esc(r.ctaTitle)));
		ctaCopy.appendChild(el('p', 'zkc-text-sm', esc(r.ctaText)));
		cta.appendChild(ctaCopy);

		var ctaActions = el('div', 'zkc-r-cta-actions');
		if (r.signupLabel) {
			var signup = el('a', 'zkc-btn', esc(r.signupLabel));
			signup.href = r.signupUrl || '#';
			if (r.signupTarget) {
				signup.target = r.signupTarget;
				signup.rel = 'noopener';
			}
			ctaActions.appendChild(signup);
		}
		var links = el('div', 'zkc-r-cta-links');
		var adjust = el('button', 'zkc-link', esc(this.cfg.general.adjust));
		adjust.type = 'button';
		adjust.addEventListener('click', function () { self.goBack(); });
		links.appendChild(adjust);
		var restart = el('button', 'zkc-link', esc(this.cfg.general.restart));
		restart.type = 'button';
		restart.addEventListener('click', function () { self.restart(); });
		links.appendChild(restart);
		ctaActions.appendChild(links);
		cta.appendChild(ctaActions);
		card.appendChild(cta);
	};

	/* ---------------------------------------------------------------- boot */

	function init(scope) {
		(scope || document).querySelectorAll('.zkc[data-zkc-config]').forEach(function (node) {
			if (node.__zkc) return;
			node.__zkc = new Calculator(node);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { init(document); });
	} else {
		init(document);
	}

	// Elementor editor / preview support.
	window.addEventListener('elementor/frontend/init', function () {
		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			window.elementorFrontend.hooks.addAction(
				'frontend/element_ready/zkc_cost_calculator.default',
				function ($scope) {
					var elScope = $scope && $scope[0] ? $scope[0] : $scope;
					if (elScope && elScope.querySelectorAll) {
						elScope.querySelectorAll('.zkc[data-zkc-config]').forEach(function (node) {
							node.__zkc = new Calculator(node);
						});
					}
				}
			);
		}
	});
})();
