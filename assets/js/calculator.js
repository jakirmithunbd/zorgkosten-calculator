/* Zorgkosten Calculator – frontend stepper (v3)
 * Renders the calculator from the JSON config produced by the Elementor
 * widget. No dependencies. Mirrors the reference app's flow:
 *
 *   intro → insurer → policy → deductible → used → info → reimbursement →
 *   coulance → contribution → [machtiging] → payment → result
 *
 * The machtiging screen only exists for insurers that may need one, so the
 * counter shows 10 or 9 steps depending on the chosen insurer.
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
		'coulance',
		'contribution',
		'machtiging',
		'payment'
	];

	/* Which sidebar illustration belongs to which screen. */
	var SCREEN_IMAGES = {
		policy: 'verzekering',
		deductible: 'eigenrisico',
		used: 'eigenrisico',
		info: 'gesprek',
		reimbursement: 'gesprek',
		coulance: 'gesprek',
		contribution: 'eigenrisico',
		machtiging: 'factuur',
		payment: 'factuur'
	};

	/* Lucide icon paths, matching the reference app's icon set. */
	var ICONS = {
		landmark: '<path d="M10 18v-7"/><path d="M11.12 2.198a2 2 0 0 1 1.76.006l7.866 3.847c.476.233.31.949-.22.949H3.474c-.53 0-.695-.716-.22-.949z"/><path d="M14 18v-7"/><path d="M18 18v-7"/><path d="M3 22h18"/><path d="M6 18v-7"/>',
		clock: '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		users: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/>',
		coins: '<path d="M13.744 17.736a6 6 0 1 1-7.48-7.48"/><path d="M15 6h1v4"/><path d="m6.134 14.768.866-.5 2 3.464"/><circle cx="16" cy="8" r="6"/>',
		'shield-check': '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>'
	};

	function icon(name) {
		var paths = ICONS[name];
		if (!paths) return '';
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" ' +
			'stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ' +
			'aria-hidden="true">' + paths + '</svg>';
	}

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

	/* "± € 1.700" for a single value, "€ 1.200 tot € 2.000" for a range. */
	function amountLabel(lo, hi) {
		return lo === hi ? '± ' + fmt(lo) : fmt(lo) + ' tot ' + fmt(hi);
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

	function link(href, text) {
		return '<a class="zkc-inline-link" href="' + esc(href) + '" target="_blank" rel="noopener noreferrer">' + esc(text) + '</a>';
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
			maxIndex: 0,          // furthest step reached, for the clickable progress
			insurer: null,
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
		var i = this.steps().indexOf(screen);
		if (i + 1 > this.state.maxIndex) {
			this.state.maxIndex = i + 1;
		}
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

	Calculator.prototype.basisLabel = function (key) {
		var bases = this.cfg.bases || [];
		for (var i = 0; i < bases.length; i++) {
			if (bases[i].key === key) return bases[i].label;
		}
		return key;
	};

	Calculator.prototype.policiesFor = function (insurerName) {
		return (this.cfg.policies || []).filter(function (p) {
			return p.insurer === insurerName;
		});
	};

	Calculator.prototype.insurerName = function () {
		return this.state.insurer ? this.state.insurer.name : this.cfg.general.fallbackName;
	};

	Calculator.prototype.hasAgreement = function () {
		return !!(this.state.insurer && this.state.insurer.agreement);
	};

	Calculator.prototype.declareUrl = function () {
		return (this.state.insurer && this.state.insurer.declareUrl) || '';
	};

	Calculator.prototype.machtigingUrl = function () {
		var ins = this.state.insurer;
		if (!ins) return '';
		return ins.machtigingUrl || ins.declareUrl || '';
	};

	/* Resolves the current reimbursement percentage, label and basis.
	 * - policy known         → that policy's percentage or range;
	 * - policy unknown, but  → the lowest and highest percentage across ALL
	 *   insurer known           policies of that insurer;
	 * - otherwise            → the configured default percentage.
	 */
	Calculator.prototype.reimbursement = function () {
		var c = this.cfg.calc;
		var pol = this.state.policy;
		var ins = this.state.insurer;
		var min, max = null, basis;

		if (pol && pol !== 'unknown') {
			min = pol.percentage;
			max = (pol.percentageMax === null || pol.percentageMax === undefined) ? null : pol.percentageMax;
			basis = pol.basis;
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
		} else {
			min = c.defaultPercentage;
			basis = c.defaultBasis;
		}

		return {
			policyUnknown: pol === 'unknown' || pol === null,
			min: min,
			max: max,
			hasRange: max !== null,
			label: max === null
				? Math.round(min) + '%'
				: Math.round(min) + '% tot ' + Math.round(max) + '%',
			basis: basis,
			basisLabel: this.basisLabel(basis)
		};
	};

	/* Reimbursed / not-reimbursed split of one amount. */
	Calculator.prototype.part = function (r, amount) {
		var lo = Math.round((r.hasRange ? r.min : r.min) / 100 * amount);
		var hi = Math.round((r.hasRange ? r.max : r.min) / 100 * amount);
		return {
			amount: amount,
			lo: lo,
			hi: hi,
			notLo: amount - hi,
			notHi: amount - lo
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
			var res = el('div', 'zkc-result');
			this.inner.appendChild(res);
			this.renderResult(res);
			return;
		}

		var withAside = screen !== 'insurer';
		var layout = el('div', 'zkc-layout' + (withAside ? ' zkc-has-aside' : ''));
		this.inner.appendChild(layout);

		var col = el('div', 'zkc-col');
		layout.appendChild(col);
		var main = el('div', 'zkc-main');
		col.appendChild(main);

		switch (screen) {
			case 'insurer':       this.renderInsurers(main); break;
			case 'policy':        this.renderPolicies(main); break;
			case 'deductible':    this.renderDeductible(main); break;
			case 'used':          this.renderUsedDeductible(main); break;
			case 'info':          this.renderInfo(main); break;
			case 'reimbursement': this.renderReimbursement(main); break;
			case 'coulance':      this.renderCoulance(main); break;
			case 'contribution':  this.renderContribution(main); break;
			case 'machtiging':    this.renderMachtiging(main); break;
			case 'payment':       this.renderPayment(main); break;
		}

		col.appendChild(this.navBar());

		if (withAside) {
			layout.appendChild(this.aside());
		}
	};

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

	/* Bottom bar: back link, clickable progress segments, next button. */
	Calculator.prototype.navBar = function () {
		var self = this;
		var g = this.cfg.general;
		var steps = this.steps();
		var index = this.stepIndex();
		var current = index + 1;
		var total = steps.length;
		var maxReached = Math.min(this.state.maxIndex, total);

		var bar = el('div', 'zkc-navbar');

		var mid = el('div', 'zkc-progress');
		var segs = el('div', 'zkc-segments');
		for (var i = 1; i <= total; i++) {
			(function (n) {
				var reachable = n <= maxReached;
				var seg = el('button', 'zkc-seg' + (n <= current ? ' zkc-seg-on' : '') + (reachable ? ' zkc-seg-jump' : ''));
				seg.type = 'button';
				seg.disabled = !reachable;
				seg.setAttribute('aria-label', tpl(g.segJump, { step: n }));
				seg.title = reachable ? tpl(g.segJump, { step: n }) : tpl(g.segLater, { step: n });
				if (reachable) {
					seg.addEventListener('click', function () { self.goTo(steps[n - 1]); });
				}
				segs.appendChild(seg);
			})(i);
		}
		mid.appendChild(segs);
		mid.appendChild(el('span', 'zkc-counter', esc(tpl(g.stepCounter, { current: current, total: total }))));
		bar.appendChild(mid);

		var actions = el('div', 'zkc-nav-actions');

		var back = el('button', 'zkc-nav-back', '<span aria-hidden="true">&larr;</span> ' + esc(g.back));
		back.type = 'button';
		if (this.state.screen === 'insurer') {
			back.disabled = true;
		} else {
			back.addEventListener('click', function () { self.goBack(); });
		}
		actions.appendChild(back);

		if (this._next) {
			var next = el('button', 'zkc-btn zkc-nav-next');
			next.innerHTML = '<span>' + esc(this._nextLabel || g.next) + '</span><span aria-hidden="true">&rarr;</span>';
			next.type = 'button';
			next.addEventListener('click', this._next);
			actions.appendChild(next);
		} else {
			actions.appendChild(el('span', 'zkc-nav-spacer'));
		}
		bar.appendChild(actions);

		return bar;
	};

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

	Calculator.prototype.warnBox = function (label, html) {
		var box = el('div', 'zkc-warn');
		box.appendChild(el('div', 'zkc-warn-label', esc(label || this.cfg.general.warnLabel)));
		box.appendChild(el('div', 'zkc-warn-text', html));
		return box;
	};

	/* Card with a circled icon, a title and one or two lines of text. */
	function infoCard(item) {
		var card = el('div', 'zkc-item');
		card.appendChild(el('span', 'zkc-item-icon', icon(item.icon)));
		var body = el('div', 'zkc-item-body');
		body.appendChild(el('div', 'zkc-item-title', esc(item.title)));
		body.appendChild(el('p', 'zkc-item-text', esc(item.text)));
		card.appendChild(body);
		return card;
	}

	/* Numbered block for a step-by-step plan. */
	function numberedStep(n, title, html) {
		var row = el('div', 'zkc-num-item');
		row.appendChild(el('span', 'zkc-num', String(n)));
		var body = el('div', 'zkc-item-body');
		body.appendChild(el('div', 'zkc-item-title', esc(title)));
		if (html) body.appendChild(el('div', 'zkc-item-text', html));
		row.appendChild(body);
		return row;
	}

	function dotList(items) {
		var ul = el('ul', 'zkc-dotlist');
		items.forEach(function (t) {
			ul.appendChild(el('li', '', '<span>' + esc(t) + '</span>'));
		});
		return ul;
	}

	function bulletPanel(title, items, note) {
		var box = el('div', 'zkc-panel');
		box.appendChild(el('h3', 'zkc-h3', esc(title)));
		if (note) box.appendChild(el('p', 'zkc-note-sm', esc(note)));
		var ul = el('ul', 'zkc-list' + (note ? ' zkc-list-spaced' : ''));
		items.forEach(function (t) { ul.appendChild(el('li', '', esc(t))); });
		box.appendChild(ul);
		return box;
	}

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
		this.stepHead(card, s.title, s.subtitle, s.help);

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

	/* Step 5 — how the costs are determined */
	Calculator.prototype.renderInfo = function (card) {
		var self = this;
		var s = this.cfg.steps.s5;
		this.stepHead(card, s.title);

		var stack = el('div', 'zkc-stack');
		(s.cards || []).forEach(function (item) {
			stack.appendChild(infoCard(item));
		});
		card.appendChild(stack);

		if (s.linkLabel && s.linkUrl) {
			card.appendChild(el('p', 'zkc-text-sm zkc-stack-link', link(s.linkUrl, s.linkLabel)));
		}

		this.setNav(function () { self.goNext(); });
	};

	/* Step 6 — reimbursement, with the per-part worked example */
	Calculator.prototype.renderReimbursement = function (card) {
		var self = this;
		var s = this.cfg.steps.s6;
		var c = this.cfg.calc;
		var r = this.reimbursement();

		var hero = el('div', 'zkc-hero');
		hero.appendChild(el('div', 'zkc-hero-kicker', esc(
			this.state.insurer
				? tpl(s.heroTitle, { insurer: this.state.insurer.name })
				: s.heroTitleFallback
		)));
		hero.appendChild(el('div', 'zkc-hero-pct zkc-display', esc(r.label)));
		hero.appendChild(el('p', 'zkc-hero-note', esc(tpl(s.heroBasis, { basis: r.basisLabel }))));

		var box = el('div', 'zkc-hero-card');
		box.appendChild(el('div', 'zkc-hero-card-title', esc(s.exampleTitle)));

		var rows = [
			{ label: s.labelDiagnostiek, part: this.part(r, c.diagnostiek) },
			{ label: s.labelBehandeling, part: this.part(r, c.behandeling) },
			{ label: s.labelTotal, part: this.part(r, c.diagnostiek + c.behandeling), total: true }
		];

		var rowsWrap = el('div', 'zkc-calc-rows');
		rows.forEach(function (row) {
			var line = el('div', 'zkc-calc-row' + (row.total ? ' zkc-calc-row-total' : ''));
			line.appendChild(el('div', 'zkc-calc-label',
				'<span>' + esc(row.label) + '</span>' +
				'<span class="zkc-calc-invoice">' + esc(tpl(s.invoiceLabel, { amount: fmt(row.part.amount) })) + '</span>'));
			line.appendChild(el('div', 'zkc-calc-amounts',
				'<span class="zkc-calc-in">' + esc(amountLabel(row.part.lo, row.part.hi) + ' ' + s.reimbursedSuffix) + '</span>' +
				'<span class="zkc-calc-out">' + esc(amountLabel(row.part.notLo, row.part.notHi) + ' ' + s.notReimbursedSuffix) + '</span>'));
			rowsWrap.appendChild(line);
		});
		box.appendChild(rowsWrap);
		box.appendChild(el('p', 'zkc-text-sm', esc(s.exampleNote)));
		hero.appendChild(box);
		card.appendChild(hero);

		var tot = this.part(r, c.diagnostiek + c.behandeling);
		var panel = el('div', 'zkc-uncovered-panel');
		panel.appendChild(el('h3', 'zkc-h3', esc(s.uncoveredTitle)));
		panel.appendChild(el('p', 'zkc-text-sm', tpl(esc(s.uncoveredText), {
			amount: '<span class="zkc-uncovered-amount">' + esc(amountLabel(tot.notLo, tot.notHi)) + '</span>'
		})));
		card.appendChild(panel);

		this.setNav(function () { self.goNext(); });
	};

	/* Step 7 — coulance scheme */
	Calculator.prototype.renderCoulance = function (card) {
		var self = this;
		var s = this.cfg.steps.s7;
		var c = this.cfg.calc;
		var r = this.reimbursement();
		var tot = this.part(r, c.diagnostiek + c.behandeling);
		var direct = this.hasAgreement();
		var name = this.insurerName();

		this.stepHead(card, s.title, s.subtitle);

		var hero = el('div', 'zkc-hero');
		hero.appendChild(el('div', 'zkc-hero-kicker', esc(s.heroKicker)));
		hero.appendChild(el('div', 'zkc-hero-amount zkc-hero-amount-uncovered zkc-display',
			esc(amountLabel(tot.notLo, tot.notHi))));
		hero.appendChild(el('p', 'zkc-hero-note', esc(tpl(s.heroNote, {
			total: fmt(c.diagnostiek + c.behandeling)
		}))));

		var box = el('div', 'zkc-hero-card');
		box.appendChild(el('div', 'zkc-hero-card-title', esc(s.meansTitle)));
		box.appendChild(dotList(s.means || []));
		hero.appendChild(box);
		card.appendChild(hero);

		var body = el('div', 'zkc-body');
		var asks = (direct ? s.asksDirect : s.asksSelf) || [];
		body.appendChild(bulletPanel(s.asksTitle, asks.map(function (t) {
			return tpl(t, { insurer: name, contribution: fmt(c.contribution) });
		})));

		var excl = (direct ? s.exclDirect : s.exclSelf) || [];
		body.appendChild(bulletPanel(s.exclTitle, excl.map(function (t) {
			return tpl(t, { insurer: name, contribution: fmt(c.contribution) });
		}), s.exclNote));
		card.appendChild(body);

		this.setNav(function () { self.goNext(); });
	};

	/* Step 8 — the one-off personal contribution */
	Calculator.prototype.renderContribution = function (card) {
		var self = this;
		var s = this.cfg.steps.s8;
		var c = this.cfg.calc;

		this.stepHead(card, s.title, tpl(s.subtitle, { contribution: fmt(c.contribution) }));

		var hero = el('div', 'zkc-hero');
		hero.appendChild(el('div', 'zkc-hero-kicker', esc(s.heroKicker)));
		hero.appendChild(el('div', 'zkc-hero-amount zkc-display', esc(fmt(c.contribution))));
		hero.appendChild(el('p', 'zkc-hero-note', esc(s.heroNote)));

		var box = el('div', 'zkc-hero-card');
		box.appendChild(el('div', 'zkc-hero-card-title', esc(s.meansTitle)));
		box.appendChild(dotList(s.means || []));
		hero.appendChild(box);
		card.appendChild(hero);

		var body = el('div', 'zkc-body');
		(s.cards || []).forEach(function (item) { body.appendChild(infoCard(item)); });
		body.appendChild(bulletPanel(s.knowTitle, s.know || []));
		card.appendChild(body);

		this.setNav(function () { self.goNext(); });
	};

	/* Step 9 — authorization, only for insurers that may need one */
	Calculator.prototype.renderMachtiging = function (card) {
		var self = this;
		var s = this.cfg.steps.s9;
		var name = this.insurerName();
		var url = this.machtigingUrl();

		this.stepHead(card, s.title, tpl(s.subtitle, { insurer: name }));

		var hero = el('div', 'zkc-hero');
		var box = el('div', 'zkc-hero-card zkc-hero-card-first');
		box.appendChild(el('div', 'zkc-hero-card-title', esc(s.meansTitle)));
		box.appendChild(el('p', 'zkc-text-sm', esc(s.meansNote)));
		box.appendChild(dotList((s.means || []).map(function (t) {
			return tpl(t, { insurer: name });
		})));
		hero.appendChild(box);
		card.appendChild(hero);

		var body = el('div', 'zkc-body');
		body.appendChild(bulletPanel(s.asksTitle, s.asks || []));

		if (url) {
			var check = el('div', 'zkc-panel');
			check.appendChild(el('h3', 'zkc-h3', esc(tpl(s.checkTitle, { insurer: name }))));
			check.appendChild(el('p', 'zkc-text-sm', esc(tpl(s.checkText, { insurer: name }))));
			var a = el('a', 'zkc-btn-outline', esc(tpl(s.checkButton, { insurer: name })));
			a.href = url;
			a.target = '_blank';
			a.rel = 'noopener noreferrer';
			check.appendChild(a);
			body.appendChild(check);
		}

		if (s.warning) body.appendChild(this.warnBox(null, esc(s.warning)));
		card.appendChild(body);

		this.setNav(function () { self.goNext(); });
	};

	/* Step 10 — how the care is paid for */
	Calculator.prototype.renderPayment = function (card) {
		var self = this;
		var s = this.cfg.steps.s10;
		var direct = this.hasAgreement();
		var name = this.insurerName();
		var declUrl = this.declareUrl();

		this.stepHead(card, s.title, tpl(direct ? s.subtitleDirect : s.subtitleSelf, { insurer: name }));

		var declareLink = declUrl
			? ' ' + link(declUrl, tpl(s.declareLinkLabel, { insurer: name })) + '.'
			: '';

		var body = el('div', 'zkc-body-flat');
		var stack = el('div', 'zkc-stack');
		((direct ? s.stepsDirect : s.stepsSelf) || []).forEach(function (item, i) {
			stack.appendChild(numberedStep(
				i + 1,
				tpl(item.title, { insurer: name }),
				tpl(item.text, { insurer: name, declareLink: declareLink })
			));
		});
		body.appendChild(stack);

		body.appendChild(this.warnBox(s.riskLabel, esc(tpl(direct ? s.riskDirect : s.riskSelf, { insurer: name }))));

		if (!direct && s.knowTitle) {
			var know = el('div', 'zkc-panel');
			know.appendChild(el('h3', 'zkc-h3', esc(s.knowTitle)));
			know.appendChild(el('p', 'zkc-text-sm', esc(s.knowText)));
			body.appendChild(know);
		}
		card.appendChild(body);

		this.setNav(function () { self.goNext(); }, s.nextLabel);
	};

	/* Result */
	Calculator.prototype.renderResult = function (card) {
		var self = this;
		var r = this.cfg.result;
		var c = this.cfg.calc;
		var reimb = this.reimbursement();
		var own = this.ownCosts();
		var name = this.insurerName();
		var direct = this.hasAgreement();
		var ins = this.state.insurer;
		var total = c.diagnostiek + c.behandeling;

		var diag = this.part(reimb, c.diagnostiek);
		var beh = this.part(reimb, c.behandeling);
		var tot = this.part(reimb, total);

		// Header.
		var head = el('div', 'zkc-r-head');
		var copy = el('div', '');
		copy.appendChild(el('div', 'zkc-eyebrow', esc(r.kicker)));
		copy.appendChild(el('h1', 'zkc-r-title', esc(r.title)));
		copy.appendChild(el('p', 'zkc-r-intro', esc(tpl(r.intro, { total: fmt(total), insurer: name }))));
		head.appendChild(copy);
		var img = (this.cfg.images || {}).factuur || { src: '', alt: '' };
		var media = el('div', 'zkc-r-media');
		media.appendChild(this.illustration(img.src, img.alt, 'zkc-illu-result'));
		head.appendChild(media);
		card.appendChild(head);

		// Invoice panel.
		var panel = el('div', 'zkc-r-invoice');
		panel.appendChild(el('h2', 'zkc-r-invoice-title', esc(tpl(r.invoiceTitle, { total: fmt(total) }))));
		panel.appendChild(el('p', 'zkc-r-invoice-text', esc(r.invoiceText)));

		var pctMin = Math.round((tot.lo / total) * 100);
		var pctMax = Math.round((tot.hi / total) * 100);

		var bar = el('div', 'zkc-splitbar');
		var left = el('div', 'zkc-split-reimb', '<span>' + esc(reimb.hasRange ? pctMin + '% tot ' + pctMax + '%' : pctMin + '%') + '</span>');
		left.style.width = pctMin + '%';
		bar.appendChild(left);
		if (reimb.hasRange && pctMax > pctMin) {
			var midSeg = el('div', 'zkc-split-uncertain');
			midSeg.style.width = (pctMax - pctMin) + '%';
			midSeg.title = r.uncertainTooltip;
			bar.appendChild(midSeg);
		}
		var right = el('div', 'zkc-split-waived', '<span>' + esc(reimb.hasRange ? (100 - pctMax) + '% tot ' + (100 - pctMin) + '%' : (100 - pctMin) + '%') + '</span>');
		right.style.width = (100 - pctMax) + '%';
		bar.appendChild(right);
		panel.appendChild(bar);

		var legend = el('div', 'zkc-r-legend');
		var l1 = el('div', 'zkc-r-legend-item');
		l1.appendChild(el('span', 'zkc-legend-dot zkc-legend-dot-reimb'));
		var l1b = el('div', '');
		l1b.appendChild(el('div', 'zkc-r-legend-title', esc(tpl(r.reimbursedLabel, { insurer: name, amount: amountLabel(tot.lo, tot.hi) }))));
		l1b.appendChild(el('p', 'zkc-text-sm', esc(tpl(r.reimbursedText, { percentage: reimb.label, basis: reimb.basisLabel }))));
		l1.appendChild(l1b);
		var l2 = el('div', 'zkc-r-legend-item');
		l2.appendChild(el('span', 'zkc-legend-dot zkc-legend-dot-waived'));
		var l2b = el('div', '');
		l2b.appendChild(el('div', 'zkc-r-legend-title', esc(tpl(r.waivedLabel, { amount: amountLabel(tot.notLo, tot.notHi) }))));
		l2b.appendChild(el('p', 'zkc-text-sm', esc(r.waivedText)));
		l2.appendChild(l2b);
		legend.appendChild(l1);
		legend.appendChild(l2);
		panel.appendChild(legend);

		// Breakdown table.
		var tableWrap = el('div', 'zkc-r-table-wrap');
		var table = el('table', 'zkc-r-table');
		// Fixed column proportions, as in the reference.
		table.appendChild(el('colgroup', '',
			'<col style="width:26%"><col style="width:22%"><col style="width:26%"><col style="width:26%">'));
		var thead = el('thead', '', '<tr>' +
			'<th>' + esc(r.colPart) + '</th>' +
			'<th>' + esc(r.colCost) + '</th>' +
			'<th>' + esc(r.colReimbursed) + '</th>' +
			'<th>' + esc(r.colWaived) + '</th>' +
			'</tr>');
		table.appendChild(thead);
		var tbody = el('tbody', '');
		[
			{ label: r.rowDiagnostiek, part: diag },
			{ label: r.rowBehandeling, part: beh },
			{ label: r.rowTotal, part: tot }
		].forEach(function (row) {
			tbody.appendChild(el('tr', '',
				'<td>' + esc(row.label) + '</td>' +
				'<td class="zkc-cell-cost">± ' + esc(fmt(row.part.amount)) + '</td>' +
				'<td class="zkc-cell-in">' + esc(amountLabel(row.part.lo, row.part.hi)) + '</td>' +
				'<td class="zkc-cell-out">' + esc(amountLabel(row.part.notLo, row.part.notHi)) + '</td>'));
		});
		table.appendChild(tbody);
		tableWrap.appendChild(table);
		panel.appendChild(tableWrap);

		if (reimb.policyUnknown && reimb.hasRange) {
			panel.appendChild(el('div', 'zkc-r-note', esc(tpl(r.rangeNotePolicy, { insurer: name, percentage: reimb.label }))));
		}
		card.appendChild(panel);

		// Own costs + what you arrange yourself.
		var grid = el('div', 'zkc-r-grid');

		var ownCard = el('div', 'zkc-r-own');
		ownCard.appendChild(el('div', 'zkc-r-card-kicker', esc(r.ownKicker)));
		ownCard.appendChild(el('div', 'zkc-r-own-total zkc-display', esc(own.totalLabel)));
		ownCard.appendChild(el('p', 'zkc-text-sm', esc(tpl(r.ownText, { total: fmt(total) }))));

		var dl = el('dl', 'zkc-r-rows');
		var row1 = el('div', 'zkc-r-row');
		row1.appendChild(el('dt', '', '<span class="zkc-r-row-label">' + esc(r.contributionLabel) + '</span><span class="zkc-r-row-sub">' + esc(r.contributionText) + '</span>'));
		row1.appendChild(el('dd', '', esc(fmt(c.contribution))));
		dl.appendChild(row1);
		var row2 = el('div', 'zkc-r-row');
		row2.appendChild(el('dt', '', '<span class="zkc-r-row-label">' + esc(r.deductibleLabel) + '</span><span class="zkc-r-row-sub">' + esc(tpl(direct ? r.deductibleTextDirect : r.deductibleTextSelf, { insurer: name })) + '</span>'));
		row2.appendChild(el('dd', '', esc(own.deductibleLabel)));
		dl.appendChild(row2);
		ownCard.appendChild(dl);

		if (!own.known) {
			ownCard.appendChild(el('div', 'zkc-r-note', esc(r.usedUnknownNote)));
		}
		grid.appendChild(ownCard);

		var selfCard = el('div', 'zkc-r-self');
		selfCard.appendChild(el('div', 'zkc-r-card-kicker', esc(r.selfKicker)));
		selfCard.appendChild(el('h3', 'zkc-r-self-title', esc(tpl(direct ? r.selfTitleDirect : r.selfTitleSelf, { insurer: name }))));
		if (direct) {
			selfCard.appendChild(el('p', 'zkc-text-sm', esc(tpl(r.selfTextDirect, { insurer: name }))));
		} else {
			var ol = el('ol', 'zkc-r-steps');
			(r.selfSteps || []).forEach(function (line) {
				ol.appendChild(el('li', '', esc(tpl(line, { insurer: name }))));
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
				html += ' ' + link(mUrl, tpl(r.machtigingLink, { insurer: name })) + '.';
			}
			selfCard.appendChild(this.warnBox(r.machtigingLabel, html));
		}
		grid.appendChild(selfCard);
		card.appendChild(grid);

		// Notes + disclaimer.
		var notes = el('div', 'zkc-r-footnotes');
		if (reimb.policyUnknown && !reimb.hasRange && r.notePolicyUnknown) {
			notes.appendChild(this.warnBox(null, esc(r.notePolicyUnknown)));
		}
		notes.appendChild(el('p', 'zkc-r-disclaimer', esc(r.disclaimer)));
		card.appendChild(notes);

		// CTA.
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
